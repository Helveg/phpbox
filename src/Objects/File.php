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
    $this->tryObjectFromData($data, FileVersion::class, "Version", "file_version");
  }
}

?>
