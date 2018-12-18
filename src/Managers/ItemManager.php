<?php

namespace PhpBox\Managers;
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
}

?>
