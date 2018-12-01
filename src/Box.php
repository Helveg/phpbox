<?php

namespace PhpBox;
use PhpBox\Config\Config;
use PhpBox\Items\{Folder};

class Box {
  private $baseUrl = "https://api.box.com/2.0/";
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
    $client = new \GuzzleHttp\Client();
    $headers = $this->getDefaultHeaders();
    $query = [];
    if(!empty($fields)) {
      $query['fields'] = implode(",", $fields);
    }
    $response = $client->request('GET', $this->baseUrl."folders/$id", [
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
