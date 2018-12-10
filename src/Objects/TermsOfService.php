<?php

namespace PhpBox\Objects;

class TermsOfService extends BoxObject {
  protected $status, $enterprise, $tos_type, $text, $created_at, $modified_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["status","enterprise","tos_type","text"]);
    $this->tryFromData($data, ["created_at", "modified_at"],
      function($x){return new \DateTime($x);});
  }
}
