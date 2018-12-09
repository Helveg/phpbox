<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Collaboration extends Object {
  protected $item, $accessible_by, $created_by, $role, $can_view_path;
  protected $status, $expires_at, $acknowledged_at, $created_at, $modified_at;
  protected function parseResponse(\stdClass $data) {
    $this->differentiateFromData($data, "item");
    $this->tryObjectFromData($data, User::class, "accessible_by");
    $this->tryObjectFromData($data, User::class, "created_by");

    $this->tryFromData($data, ["role","can_view_path","status"]);
    $this->tryFromData($data, ["expires_at", "acknowledged_at","created_at","modified_at"],
      function($x){return new \DateTime($x);});
  }
}

?>
