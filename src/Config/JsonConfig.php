<?php

namespace PhpBox\Config;

use PhpBox\Exception\{FileException, FileFormatException};

class JsonConfig extends Config implements ConfigInterface {

  public function __construct(string $filename) {
    if(!file_exists($filename)) throw new FileException($filename, "json config file not found");
    $json = file_get_contents($filename);
    $config = json_decode($json);
    if($config === NULL) {
      throw new FileFormatException($filename, "invalid json format", 0);
    }

    try { // Load configuration details
      $missing = 'private key (boxAppSettings.appAuth.privateKey)';
      $this->sk_key = $config->boxAppSettings->appAuth->privateKey;
      $missing = 'private key passphrase (boxAppSettings.appAuth.passphrase)';
      $this->sk_pass = $config->boxAppSettings->appAuth->passphrase;
      $missing = 'public key (boxAppSettings.appAuth.publicKeyID)';
      $this->pk_key = $config->boxAppSettings->appAuth->publicKeyID;
      $missing = 'client id (boxAppSettings.clientID)';
      $this->clientId = $config->boxAppSettings->clientID;
      $missing = 'client id (boxAppSettings.clientSecret)';
      $this->clientSecret = $config->boxAppSettings->clientSecret;
      $missing = 'enterprise id (enterpriseID)';
      $this->enterpriseId = $config->enterpriseID;
    } catch(Exception $e) {
      throw new FileFormatException($filename, "missing $missing. Take a look at the Box documentation (https://developer.box.com/docs/setting-up-a-jwt-app#section-step-2-generate-a-public-private-keypair) or our GitHub pages for an example of a correctly formatted basic json Box-app configuration file.", 1);
    }
  }

}

?>
