<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class GroupMembership extends Object {
  protected $user;
  protected $group;
  protected $role;
  protected $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    if(isset($data->user))
      $this->user = new User($this->box, $data->user);
    if(isset($data->group))
      $this->group = new Group($this->box, $data->group);
    $this->tryFromData($data, ["role", "created_at", "modified_at"]);
  }
}

?>
