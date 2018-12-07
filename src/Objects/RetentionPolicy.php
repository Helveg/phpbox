<?php

namespace PhpBox\Objects;

class RetentionPolicy extends Policy {
  protected $policy_name, $policy_type, $retention_length, $disposition_action, $status, $created_by, $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["policy_name","policy_type","retention_length","disposition_action","status"]);
    $this->tryObjectFromData($data, User::class, "created_by");
    $this->tryFromData($data, ["created_at","modified_at"],
      function($x){return new \DateTime($x);});
  }
}
