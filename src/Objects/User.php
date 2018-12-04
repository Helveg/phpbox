<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class User extends Object {
  protected $name;
  protected $login;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name", "login"]);
  }
}

?>
