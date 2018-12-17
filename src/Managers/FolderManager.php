<?php

namespace PhpBox\Managers;
use \PhpBox\Objects\BoxObject;

class FolderManager extends ItemManager {

  public function create($parent, string $name = "", $params = [], $fields = []) {
    if($name == "") {
      if(!empty($params) || !empty($fields)) throw new \Exception("New folder name cannot be empty string.");
      $name = $parent; // If only 1 argument is given use it as the name of a new folder in the root folder.
      $parent = "0";
    }
    if($parent instanceof BoxObject && $parent->isItem()) {
      $parent = $parent->id;
    }
    $params['name'] = $name;
    $params['parent'] = ["id"=>$parent];
    return $this->base_create($params, $fields);
  }

  public function request($id = "0", $fields = [], $query = []) {
    return parent::request($id, $fields, $query);
  }

  public function delete($id, $recursive = false, $ifMatch = "") {
    $headers = [];
    if($ifMatch != "") $headers["If-Match"] = $ifMatch;
    return parent::delete($id, ["recursive" => $recursive], $headers);
  }
}

?>
