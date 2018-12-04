<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Group extends Object {
  protected $name;
  protected $created_at, $modified_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name", "created_at", "modified_at"]);
  }

  public function requestByName(string $name, $query = [], $fields = []) {
    $query['name'] = $name;
    $response = $this->box->guzzle("GET", "groups/", [
      "query" => Box::fieldsQuery($fields, $query)
    ]);
    if($response) {
      $ret = [];
      foreach($response->entries as $groupdata) {
        $ret[] = new Group($groupdata);
      }
      return $ret;
    }
    return false;
  }
}

?>
