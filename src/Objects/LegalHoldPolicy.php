<?php

namespace PhpBox\Objects;

class LegalHoldPolicy extends Policy {
  protected $policy_name, $description, $status, $assignment_counts, $created_by, $created_at, $modified_at, $deleted_at, $filter_started_at, $filter_ended_at, $release_notes;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["policy_name","description","status", "assignment_counts","release_notes"]);
    $this->tryObjectFromData($data, User::class, "created_by");
    $this->tryFromData($data, ["created_at","modified_at","deleted_at","filter_started_at","filter_ended_at"],
      function($x){return new \DateTime($x);});
  }
}
