<?php

namespace PhpBox\Objects;

class CollaborationWhitelistEntry extends BoxObject {
  protected $domain, $direction, $enterprise, $created_at, $modified_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["domain","direction","enterprise"]);
    $this->tryFromData($data, ["created_at", "modified_at"],
      function($x){return new \DateTime($x);});
  }
}
