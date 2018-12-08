<?php

namespace PhpBox\Managers;
use PhpBox\Objects\Object;

class GroupMembershipManager extends ObjectManager {

  public function create($user, $group, $params = [], $fields = []) {
    if($user instanceof Object && $user->isUser()) {
      $user = $user->id;
    }
    if($group instanceof Object && $group->isUser()) {
      $group = $group->id;
    }
    $params['group'] = ["id"=>$group];
    $params['user'] = ["id"=>$user];
    return $this->base_create($params, $fields);
  }
}

?>
