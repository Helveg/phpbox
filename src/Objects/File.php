<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class File extends Item {
  protected $sha1, $version_number;
  protected $comment_count, $extension, $is_package, $expiring_embed_link;
  protected $file_version;
  protected $lock;

  protected function parseResponse(\stdClass $data) {
    parent::parseResponse($data);
    $this->tryFromData($data, ["sha1","version_number","comment_count",
      "extension","is_package",
      "expiring_embed_link", "lock"]);
    $this->tryBoxObjectFromData($data, FileVersion::class, "file_version");
  }

  public function rename(string $new_name) {
    $ret = $this->box->File->rename($this, $new_name);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
      return $this;
    }
    return false;
  }

  public function description(string $description) {
    $ret = $this->box->File->description($this, $description);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
      return $this;
    }
    return false;
  }

  public function move($new_folder) {
    $ret = $this->box->File->move($this, $new_folder);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
      return $this;
    }
    return false;
  }

  public function lock($expires_at = "", bool $is_download_prevented = false) {
    $ret = $this->box->File->lock($this, $expires_at, $is_download_prevented);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
      return $this;
    }
    return false;
  }
  public function unlock() {
    $ret = $this->box->File->unlock($this);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
      return $this;
    }
    return false;
  }

  public function write($contents, $ifMatch = "") {

  }
}

?>
