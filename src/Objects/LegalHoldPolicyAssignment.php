<?php

namespace PhpBox\Objects;

class LegalHoldPolicyAssignment extends Assignment {
  protected $legal_hold_policy, $assigned_to, $assigned_by, $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["legal_hold_policy"]);
    $this->tryObjectFromData($data, Item::class, "assigned_to");
    $this->tryObjectFromData($data, User::class, "assigned_by");
    $this->tryFromData($data, ["created_at","modified_at"],
      function($x){return new \DateTime($x);});
  }
}
