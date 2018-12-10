<?php

namespace PhpBox\Managers;
use PhpBox\Objects\BoxObject;

class GroupMembershipManager extends BoxObjectManager {

  public function create($user, $group, $params = [], $fields = []) {
    if($user instanceof BoxObject && $user->isUser()) {
      $user = $user->id;
    }
    if($group instanceof BoxObject && $group->isUser()) {
      $group = $group->id;
    }
    $params['group'] = ["id"=>$group];
    $params['user'] = ["id"=>$user];
    return $this->base_create($params, $fields);
  }
}

?>
