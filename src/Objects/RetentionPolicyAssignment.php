<?php

namespace PhpBox\Objects;

class RetentionPolicyAssignment extends Assignment {
  protected $retention_policy, $assigned_to, $assigned_by, $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["retention_policy"]);
    $this->tryBoxObjectFromData($data, Item::class, "assigned_to");
    $this->tryBoxObjectFromData($data, User::class, "assigned_by");
    $this->tryFromData($data, ["created_at","modified_at"],
      function($x){return new \DateTime($x);});
  }
}
