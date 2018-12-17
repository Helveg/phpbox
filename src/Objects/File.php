<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class File extends Item {
  protected $sha1, $version_number;
  protected $comment_count, $extension, $is_package, $expiring_embed_link;
  protected $Version;
  protected $Lock;

  protected function parseResponse(\stdClass $data) {
    parent::parseResponse($data);
    $this->tryFromData($data, ["sha1","version_number","comment_count",
      "extension","is_package",
      "expiring_embed_link"]);
    $this->tryBoxObjectFromData($data, FileVersion::class, "Version", "file_version");
  }

  public function rename(string $new_name) {
    $ret = $this->box->File->rename($this, $new_name);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }

  public function description(string $description) {
    $ret = $this->box->File->description($this, $description);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }

  public function move($new_folder) {
    $ret = $this->box->File->move($this, $new_folder);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }

  public function write($contents, $ifMatch = "") {
    
  }
}

?>
