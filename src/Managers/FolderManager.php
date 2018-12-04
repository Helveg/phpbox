<?php

namespace PhpBox\Managers;
use \PhpBox\Objects\Object;

class FolderManager extends ObjectManager {

  public function create($parent, string $name = "", $params = [], $fields = []) {
    if($name == "") {
      if(!empty($params) || !empty($fields)) throw new \Exception("New folder name cannot be empty string.");
      $name = $parent; // If only 1 argument is given use it as the name of a new folder in the root folder.
      $parent = "0";
    }
    if($parent instanceof Object && $parent->isFolder()) {
      $parent = $parent->getId();
    }
    $params['name'] = $name;
    $params['parent'] = ["id"=>$parent];
    parent::base_create($params, $fields);
  }
}

?>
