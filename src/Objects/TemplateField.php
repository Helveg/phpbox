<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class TemplateField extends Object {
  protected $key, $displayName, $hidden;
  public function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["key","displayName","hidden"]);
  }
}

?>
