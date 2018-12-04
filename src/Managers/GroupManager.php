<?php

namespace PhpBox\Managers;

class GroupManager extends ObjectManager {

  public function create(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    parent::base_create($params, $fields);
  }
}

?>
