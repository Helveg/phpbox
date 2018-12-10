<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Webhook extends BoxObject {
  protected $target, $created_by, $created_at, $address, $triggers;

  protected function parseResponse(\stdClass $data) {
    $this->tryBoxObjectFromData($data, Item::class, "target");
    $this->tryBoxObjectFromData($data, User::class, "created_by");

    $this->tryFromData($data, ["address","triggers"]);
    $this->tryFromData($data, ["created_at"],
      function($x){return new \DateTime($x);});
  }
}

?>
