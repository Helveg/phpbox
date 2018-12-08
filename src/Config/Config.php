<?php

namespace PhpBox\Config;
use PhpBox\Config\ConfigInterface;

class Config implements ConfigInterface {
  protected $authUrl = 'https://api.box.com/oauth2/token';
  protected $pk_key;
  protected $sk_key;
  protected $sk_pass;
  protected $clientId;
  protected $clientSecret;
  protected $assertType, $assertId;

  public function __construct() {

  }

  public function getAppDetails() {
    return (object)["ID" => $this->clientId, "Secret" => $this->clientSecret];
  }

  public function getAuthenticationUrl() {
    return $this->authUrl;
  }

  public function writeAssertion() {
    $key = openssl_pkey_get_private($this->sk_key, $this->sk_pass);
    $claims = [
      'iss' => $this->clientId,
      'sub' => $this->assertId,
      'box_sub_type' => $this->assertType,
      'aud' => $this->authUrl,
      'jti' => base64_encode(random_bytes(64)),
      'exp' => time() + 45,
      'kid' => $this->pk_key
    ];
    return \Firebase\JWT\JWT::encode($claims, $key, 'RS512');
  }

  public function setAppDetails($clientId, $clientSecret) {
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
  }

  public function setPrivateKey($privateKey, $passphrase) {
    $this->sk_key = $privateKey;
    $this->sk_pass = $passphrase;
  }

  public function setPublicKey($publicKey) {
    $this->pk_key = $publicKey;
  }

  public function setConnectionDetails($sub_type, $sub) {
    $this->assertType = $sub_type;
    $this->assertId = $sub;
  }
}

?>
