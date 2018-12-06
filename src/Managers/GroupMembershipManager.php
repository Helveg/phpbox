<?php

namespace PhpBox\Managers;

class GroupMembershipManager extends ObjectManager {

  public function create($user, $group, string $name, $params = [], $fields = []) {
    if($user instanceof Object && $user->isUser()) {
      $user = $user->getId();
    }
    if($group instanceof Object && $group->isUser()) {
      $group = $group->getId();
    }
    $params['name'] = $name;
    $params['group'] = ["id"=>$group];
    $params['user'] = ["id"=>$user];
    return $this->base_create($params, $fields);
  }
}

?>
