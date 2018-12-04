<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Objects\{Object, Item, Folder, File, User};
use PhpBox\Exception\BoxException;

class Box {
  const baseUrl = "https://api.box.com/2.0/";
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

  public function requestAccessToken() {
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
      $this->AccessToken = new Token($this, json_decode($data));
    }
    return false;
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
        $folder = (string)($folder->getId());
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
              return json_decode($response->getBody()->getContents());
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

  public static function fieldsQuery($fields, $query = []) {
    if(!empty($fields)) {
      $query['fields'] = implode(',', $fields);
    }
    return $query;
  }

  public function requestGroupMembership($id, $fields = []) {
    if($id instanceof Object && $id->isGroupMembership()) $id = $id->getId();
    if($ret = $this->guzzleObject("group_memberships/$id", $fields)) $ret = new GroupMembership($this, $ret);
    return $ret;
  }

  public function createAppUser(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    $params['is_platform_access_only'] = true;
    return $this->guzzleCreate("User", $params, $fields);
  }

  public function createGroup(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    if(!($ret = $this->guzzleCreate("Group", $params, $fields))) {
      $error = $this->getResponseCode();
      if($error == 409) {
        throw new BoxException("A group with this name already exists", 409);
      }
    }
    return $ret;
  }

  public function createGroupMembership(User $user, Group $group, $params = [], $fields = []) {
    $params["user"] = ["id" => $user->getId()];
    $params["group"] = ["id" => $group->getId()];
    $this->guzzleCreate("GroupMembership", $params, $fields);
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
