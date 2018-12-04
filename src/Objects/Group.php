<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Group extends Object {
  protected $name;
  protected $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name", "created_at", "modified_at"]);
  }
}

?>