<?php

namespace PhpBox\Objects;

class DevicePinner extends BoxObject {
  protected $owned_by, $product_name, $created_at, $modified_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["product_name"]);
    $this->tryBoxObjectFromData($data, User::class, "owned_by");
    $this->tryFromData($data, ["created_at", "modified_at"],
      function($x){return new \DateTime($x);});
  }
}
