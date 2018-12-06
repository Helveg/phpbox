<?php

namespace PhpBox\Managers;

class GroupManager extends ObjectManager {

  public function create(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    return $this->base_create($params, $fields);
  }
}

?>
