<?php

namespace PhpBox;
use PhpBox\Config\Config;

class Box {
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
    $data = $response->getBody()->getContents();
    $this->AccessToken = new Token(json_decode($data));
  }

  public function getAccessToken() {
    return $this->AccessToken;
  }

  public function requestFolder($id, $findpath = false) {
    
  }
}

?>
