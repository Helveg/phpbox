<?php

namespace PhpBox\Items;

class Item implements ItemInterface {
  protected $data;
  protected $id;
  protected $type;

  public function __construct($data) {
    $this->data = $data;
    $this->id = $data->id;
    $this->type = $data->type;
    $this->name = $data->name;
  }

  public function getId() {
    return $this->id;
  }

  public function getType() {
    return $this->type;
  }

  public function getName() {
    return $this->name;
  }

  public function isFolder() {
    return $this->type == 'folder';
  }

  public function isFile() {
    return $this->type == 'file';
  }
}

?>
