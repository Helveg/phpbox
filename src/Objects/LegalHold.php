<?php

namespace PhpBox\Objects;
use PhpBox\Collections\LegalHoldPolicyAssignmentCollection;

class LegalHold extends Object {
  protected $file_version, $file, $legal_hold_policy_assignments, $deleted_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["deleted_at"],
      function($x){return new \DateTime($x);});
    $this->tryObjectFromData($data, FileVersion::class, "file_version");
    $this->tryObjectFromData($data, File::class, "file");
    if(isset($data->legal_hold_policy_assignments))
      $this->legal_hold_policy_assignments = new Collection($this->box, $data->legal_hold_policy_assignments);
  }
}
