<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Folder extends Item {
  protected $contains = [];
  const endpointUrl = Box::baseUrl.'folders/';

  public function parseResponse(\stdClass $data) {
    if(isset($data->item_collection)) {
      foreach ($data->item_collection->entries as $key => $value) {
        $this->contains[] = Object::differentiate($this->box, $value);
      }
    }
  }

  public function getItems() {
    return $this->contains;
  }

  public function getItemByName($name) {
    foreach ($this->contains as $item)
      if($item->getName() == $name)
        return $item;
    return false;
  }

  public function getFiles() {
    return array_filter($this->contains, function($x) {return $x->isFile();});
  }

}

?>
