<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\{BoxObject,Folder,File,Item};

class ItemManager extends BoxObjectManager {
  public function update($id, $params, $fields) {
    $ret = $this->base_update($id, $params, [], $fields);
    return $ret;
  }

  public function rename($id, string $new_name) {
    return $this->update($id, ["name" => $new_name], ["name"]);
  }

  public function move($id, $new_folder) {
    $id = Item::toId($id);
    $parentId = Folder::toId($new_folder);
    return $this->update($id, ["parent" => ["id" => $parentId]], ["parent"]);
  }

  public function description($id, $new_description) {
    return $this->update($id, ["description" => (string)$new_description], ["description"]);
  }

  public function copy($id, $new_folder = "", $new_name = "", $params = [], $fields = []) {
    $id = Item::toId($id);
    if($new_folder !== "") $params['parent'] = ["id" => Folder::toId($new_folder)];
    if($new_name !== "") $params['name'] = $new_name;
    $ret = $this->box->guzzle("POST", static::getEndpoint()."$id/copy", [
      'json' => $params,
      'query' => Box::fieldsQuery($fields)
    ]);
    if($ret) {
      $class = $this->getManagedObjectClass();
      return new $class($this->box, $ret);
    }
    return false;
  }
}

?>
