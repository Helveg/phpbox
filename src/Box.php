<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Objects\{BoxObject, Item, Folder, File, User};
use PhpBox\Exception\BoxException;

class Box {
  const baseUrl = "https://api.box.com/2.0/";
  public static $guzzleDebug = false;
  private $AccessToken;
  private $config;
  private $lastResponseCode;
  private $lastResponseRaw;
  private $lastResponse;

  public function __construct(Config $config, $token = "") {
    $this->config = $config;
    // Request access token
    if($token != "") {
      if($token instanceof Token) {
        $this->AccessToken = $token;
      } else {
        $this->AccessToken = new Token($this, $token);
      }
    } else {
      $this->requestAccessToken();
    }
    // Initialize managers
    foreach (["Collaboration", "File", "Folder", "Group", "GroupMembership", "User"] as $manager) {
      $className = "\\PhpBox\\Managers\\{$manager}Manager";
      $this->$manager = new $className($this);
    }
  }

  public function requestToken($sub_type, $sub) {
    $this->config->setConnectionDetails($sub_type, $sub);
    $appDetails = $this->config->getAppDetails();
    $assertion = $this->config->writeAssertion();
    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', $this->config->getAuthenticationUrl(), [
      'form_params' => [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $assertion,
        'client_id' => $appDetails->ID,
        'client_secret' => $appDetails->Secret
      ]
    ]);
    if($response->getStatusCode() == 200) {
      $data = $response->getBody()->getContents();
      return new Token($this, json_decode($data));
    }
    return false;
  }

  public function requestAccessToken() {
    return $this->AccessToken = $this->requestToken('enterprise', $this->config->getEnterpriseId());
  }

  public function requestUserToken($userId) {
    return $this->requestToken('user', $userId);
  }

  public function requestExchangeToken($scopes = ["base_preview", "item_download"], $folder = NULL, $token = NULL) {
    if($token == NULL) {
      $token = $this->getAccessToken();
    }
    $client = new \GuzzleHttp\Client();
    $params = [
      'subject_token' => (string)$token,
      'subject_token_type' => 'urn:ietf:params:oauth:token-type:access_token',
      'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
      'scope' => implode(" ", $scopes)
    ];
    if ($folder != NULL) {
      if ($folder instanceof Item && $folder->isFolder()) {
        $folder = (string)($folder->id);
      }
      $params['resource'] = Folder::endpointUrl.$folder;
    }
    $response = $client->request('POST', $this->config->getAuthenticationUrl(), [
      'form_params' => $params
    ]);
    return new Token($this, json_decode($response->getBody()->getContents()));
  }

  public function getAccessToken() {
    if(!Token::isValid($this->AccessToken)) {
      $this->requestAccessToken();
    }
    return $this->AccessToken;
  }

  public function useToken($token) {
    return new self($this->config, $token);
  }

  public function asUser($userId) {
    throw new \Exception("Not implemented yet.");
    $nBox = new self($this->config, $this->getAccessToken());
    $nBox->setAsUser($userId);
    return $nBox;
  }

  public function setAsUser($userId) {
    throw new \Exception("Not implemented yet.");
  }

  public function clearAsUser() {
    throw new \Exception("Not implemented yet.");
  }

  public function guzzle($method, $endpoint, $params, $responseHandler = NULL) {
    $params['debug'] = self::$guzzleDebug;
    // Default headers
    if(!isset($params['headers'])) $params['headers'] = $this->getDefaultHeaders();
    else $params['headers'] = array_merge($this->getDefaultHeaders(), $params['headers']);
    // Connect
    $client = new \GuzzleHttp\Client(["base_uri" => self::baseUrl, "http_errors" => false]);
    try {
      $response = $client->request($method, $endpoint, $params);
    } catch(\Exception $e) {
      $response = false;
    }
    finally {
      $this->lastResponse = $response;
      $this->lastResponseRaw = $contents = $response->getBody()->getContents();
      if($response instanceof \GuzzleHttp\Psr7\Response) {
        $this->lastResponseCode = $response->getStatusCode();
        if($responseHandler != NULL) {
          return $responseHandler($this, $response);
        }
        else {
          switch($response->getStatusCode()) {
            case 200:
            case 201:
              return $this->lastResponseJSON = json_decode($contents);
            case 400:
              $this->lastResponseJSON = json_decode($contents);
              if($this->lastResponseJSON === NULL) return false; // Empty 400 response. See https://community.box.com/t5/Platform-and-Development-Forum/Empty-response-with-code-400-during-file-upload/m-p/65124#M5738
              if($this->lastResponseJSON->code == 'user_already_collaborator')
                throw new \Exception("User is already a collaborator on this item.");
          }
        }
      } else {
        $this->lastResponseCode = 0;
      }
      return false;
    }
  }

  public function getResponseCode() {
    return $this->lastResponseCode;
  }

  public function getResponse() {
    return $this->lastResponse;
  }

  public function getResponseJSON() {
    return $this->lastResponseJSON;
  }

  public function getResponseRaw() {
    return $this->lastResponseRaw;
  }

  public static function fieldsQuery($fields, $query = []) {
    if(!empty($fields)) {
      $query['fields'] = implode(',', $fields);
    }
    return $query;
  }

  public function getDefaultHeaders() {
    $token = $this->getAccessToken();
    return [
        'Authorization' => "Bearer $token",
        'Accept'        => 'application/json',
    ];
  }
}

?>
