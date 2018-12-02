<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Items\{Item, Folder};

class Box {
  const baseUrl = "https://api.box.com/2.0/";
  private $AccessToken;
  private $config;

  public function __construct(Config $config, $token = "") {
    $this->config = $config;
    if($token != "") {
      $this->AccessToken = new Token($token);
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
    if($this->handleResponse($response)) {
      $data = $response->getBody()->getContents();
      $this->AccessToken = new Token(json_decode($data));
    }
    return false;
  }

  public function requestExchangeToken($scopes = ["base_preview", "item_download"], $folder = NULL, $token = NULL) {
    if($token == NULL) {
      $token = $this->getValidAccessToken();
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
    return new Token(json_decode($response->getBody()->getContents()));
  }

  public function getAccessToken() {
    return $this->AccessToken;
  }

  public function getValidAccessToken() {
    if(!Token::isValid($this->AccessToken)) {
      $this->requestAccessToken();
    }
    return $this->AccessToken;
  }

  public function requestFolder($id = "0", $fields = []) {
    if($id instanceof Item && $id->isFolder()) $id = $id->getId();
    $client = new \GuzzleHttp\Client();
    $headers = $this->getDefaultHeaders();
    $query = [];
    if(!empty($fields)) {
      $query['fields'] = implode(",", $fields);
    }
    $response = $client->request('GET', self::baseUrl."folders/$id", [
      'headers' => $headers,
      'query' => $query
    ]);
    if($this->handleResponse($response)){
      return new Folder(json_decode($response->getBody()->getContents()));
    }
    return false;
  }

  public function getDefaultHeaders() {
    $token = $this->getValidAccessToken();
    return [
        'Authorization' => "Bearer $token",
        'Accept'        => 'application/json',
    ];
  }

  public function handleResponse(\GuzzleHttp\Psr7\Response $response) {
    $statusCode = $response->getStatusCode();
    switch($statusCode) {
      case 200:
        return true;
      default:
        throw new \Exception("Uncaught response code $statusCode");
    }
    return true;
  }
}

?>
