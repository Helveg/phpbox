<?php

namespace PhpBox\Managers;
use PhpBox\Objects\{Item,User,Group};

class CollaborationManager extends BoxObjectManager {

  public function create(Item $item, $accessible_by, $role = "previewer", $query = [], $params = [], $fields = []) {
    $params['accessible_by'] = [];
    $params['item'] = ['id'=>$item->id,'type'=>$item->type];
    $params['role'] = $role;
    if(is_string($accessible_by)) {
      if(strpos("@",$accessible_by) === false) {
        throw new \Exception("If the 2nd parameter of Collab->create is a string, it should be the email address of the person to invite to collaborate. (And should contain an @)");
      } else {
        $params['accessible_by']['login'] = $accessible_by;
      }
    }  elseif($accessible_by instanceof User) {
      $params['accessible_by']['type'] = 'user';
      $params['accessible_by']['id'] = $accessible_by->id;
    } elseif($accessible_by instanceof Group) {
      $params['accessible_by']['type'] = 'group';
      $params['accessible_by']['id'] = $accessible_by->id;
    }
    // $params['accessible_by']['login'] = 'AutomationUser_710566_z3cqAASuvI@boxdevedition.com';
    return $this->base_create($params, $fields, $query);
  }
}

?>
