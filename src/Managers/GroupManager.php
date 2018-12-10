<?php

namespace PhpBox\Managers;
use PhpBox\Box;
use PhpBox\Collections\Collection;

class GroupManager extends BoxObjectManager {

  public function create(string $name, $params = [], $fields = []) {
    $params['name'] = $name;
    return $this->base_create($params, $fields);
  }

  public function byName(string $name, $query = [], $fields = []) {
    $query['name'] = $name;
    $response = $this->box->guzzle("GET", "groups/", [
      "query" => Box::fieldsQuery($fields, $query)
    ]);
    if($response) {
      return new Collection($this->box, $response);
    }
    return false;
  }
}

?>
