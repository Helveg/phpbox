<?php

namespace PhpBox\Objects;

class StoragePolicyAssignment extends Assignment {
  protected $storage_policy, $assigned_to;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["storage_policy"]);
    $this->tryObjectFromData($data, Item::class, "assigned_to");
  }
}
