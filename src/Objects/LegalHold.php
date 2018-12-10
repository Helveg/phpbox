<?php

namespace PhpBox\Objects;
use PhpBox\Collections\LegalHoldPolicyAssignmentCollection;

class LegalHold extends BoxObject {
  protected $file_version, $file, $legal_hold_policy_assignments, $deleted_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["deleted_at"],
      function($x){return new \DateTime($x);});
    $this->tryBoxObjectFromData($data, FileVersion::class, "file_version");
    $this->tryBoxObjectFromData($data, File::class, "file");
    $this->tryCollectionFromData($data, LegalHoldPolicyAssignmentCollection::class, "legal_hold_policy_assignments");
  }
}
