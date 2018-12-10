<?php

namespace PhpBox\Objects;

class WebLink extends BoxObject {
  protected $parent, $name, $url, $description;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name","url","description"]);
    $this->tryBoxObjectFromData($data, Item::class, "parent");
  }
}
