<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class SharedLink extends BoxObject {
  protected $url, $download_url, $vanity_url, $access;
  protected $effective_access, $unshared_at, $is_password_enabled;
  protected $password, $permissions, $download_count, $preview_count;

  public function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["url", "download_url","vanity_url","access",
      "effective_access","is_password_enabled","password","permissions",
      "download_count", "preview_count"]);
    $this->tryFromData($data, ["unshared_at"], function($x){return new DateTime($x);});
  }

}

?>
