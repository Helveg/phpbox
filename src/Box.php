<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Objects\{Object, Item, Folder, File, User};
use PhpBox\Exception\BoxException;

class Box {
  const baseUrl = "https://api.box.com/2.0/";
  public static $guzzleDebug = false;
  private $AccessToken;
  private $config;
  private $lastResponseCode;
  private $lastResponse;

  public function __construct(Config $config, $token = "") {
    $this->config = $config;
    // Request access token
    if($token != "") {
      $this->AccessToken = new Token($this, $token);
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

  public function guzzle($method, $endpoint, $params, $responseHandler = NULL) {
    $params['debug'] = self::$guzzleDebug;
    // Default headers
    if(!isset($params['headers'])) $params['headers'] = $this->getDefaultHeaders();
    else $params['headers'] = array_merge($this->getDefaultHeaders(), $params['headers']);
    // Connect
    $client = new \GuzzleHttp\Client(["base_uri" => self::baseUrl, "http_errors" => false]);
    try {
      $response = $client->request($method, $endpoint."?name=RRR", $params);
    }
    finally {
      $this->lastResponse = $response;
      if($response instanceof \GuzzleHttp\Psr7\Response) {
        $this->lastResponseCode = $response->getStatusCode();
        if($responseHandler != NULL) {
          return $responseHandler($this, $response);
        }
        else {
          switch($response->getStatusCode()) {
            case 200:
            case 201:
              return $this->lastResponseJSON = json_decode($response->getBody()->getContents());
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
