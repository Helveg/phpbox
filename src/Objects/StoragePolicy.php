<?php

namespace PhpBox\Objects;

class StoragePolicy extends Policy {
  protected $name;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name"]);
  }
}
