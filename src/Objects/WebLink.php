<?php

namespace PhpBox\Objects;

class WebLink extends Object {
  protected $parent, $name, $url, $description;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name","url","description"]);
    $this->tryObjectFromData($data, Item::class, "parent");
  }
}
