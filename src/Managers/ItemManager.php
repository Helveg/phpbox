<?php

namespace PhpBox\Managers;
use \PhpBox\Objects\{BoxObject,Folder};

class ItemManager extends BoxObjectManager {
  public function update($id, $params, $fields) {
    $ret = $this->base_update($id, $params, [], $fields);
    return $ret;
  }

  public function rename($id, string $new_name) {
    return $this->update($id, ["name" => $new_name], ["name"]);
  }

  public function move($id, $new_folder) {
    $id = Folder::toId($new_folder);
    var_dump($id);
    return $this->update($id, ["parent" => ["id" => $id]], ["parent"]);
  }

  public function description($id, $new_description) {
    return $this->update($id, ["description" => (string)$new_description], ["description"]);
  }
}

?>
