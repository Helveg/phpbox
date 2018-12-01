<?php

namespace PhpBox\Items;

class Folder extends Item {
  protected $contains = [];

  public function __construct($data) {
    parent::__construct($data);
    if(isset($data->item_collection)) {
      foreach ($data->item_collection->entries as $key => $value) {
        $this->contains[] = new Item($value);
      }
    }
  }

  public function getItems() {
    return $this->contains;
  }

  public function getFiles() {
    return array_filter($this->contains, function($x) {return $x->isFile();});
  }

}

?>
