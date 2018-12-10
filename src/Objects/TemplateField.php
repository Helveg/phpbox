<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class TemplateField extends BoxObject {
  protected $key, $displayName, $hidden;
  public function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["key","displayName","hidden"]);
  }
}

?>
