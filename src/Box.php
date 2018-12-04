<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Objects\{Object, Item, Folder, File, User};

class Box {
  const baseUrl = "https://api.box.com/2.0/";
  private $AccessToken;
  private $config;
  private $lastResponseCode;
  private $lastResponse;

  public function __construct(Config $config, $token = "") {
    $this->config = $config;
    if($token != "") {
      $this->AccessToken = new Token($this, $token);
    } else {
      $this->requestAccessToken();
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

  private function guzzle($method, $endpoint, $params, $responseHandler = NULL) {
    $client = new \GuzzleHttp\Client(["base_uri" => self::baseUrl, "http_errors" => false]);
    try {
      $response = $client->request($method, $endpoint, $params);
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

  private function guzzleObject($url, $fields = [], $headers = []) {
    $headers = array_merge($this->getDefaultHeaders(), $headers);
    $query = [];
    if(!empty($fields)) {
      $query['fields'] = implode(",", $fields);
    }
    return $this->guzzle('GET', $url, [
      'headers' => $headers,
      'query' => $query
    ]);
  }

  public function requestFolder($id = "0", $fields = []) {
    if($id instanceof Object && $id->isFolder()) $id = $id->getId();
    if($ret = $this->guzzleObject("folders/$id", $fields)) $ret = new Folder($this, $ret);
    return $ret;
  }

  public function requestFile($id, $fields = []) {
    if($id instanceof Object && $id->isFile()) $id = $id->getId();
    if($ret = $this->guzzleObject("files/$id", $fields)) $ret = new File($this, $ret);
    return $ret;
  }

  public function requestUser($id = "me", $fields = []) {
    if($id instanceof Object && $id->isUser()) $id = $id->getId();
    if($ret = $this->guzzleObject("users/$id", $fields)) $ret = new User($this, $ret);
    return $ret;
  }

  public function createAppUser(string $name, $params = [], $fields = []) {
    $query = [];
    if(!empty($fields)) {
      $query['fields'] = implode(",", $fields);
    }
    $params['name'] = $name;
    $params['is_platform_access_only'] = true;
    $response = $this->guzzle('POST', "users/", [
      'headers' => $this->getDefaultHeaders(),
      'query' => $query,
      'json' => $params
    ]);
    if($response) {
      return new User($this, $response);
    }
    return false;
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
