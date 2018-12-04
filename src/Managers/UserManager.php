<?php

namespace PhpBox\Managers;

class UserManager extends ObjectManager {

  public function createAppUser(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    $params['is_platform_access_only'] = true;
    parent::base_create($params, $fields);
  }
}

?>
