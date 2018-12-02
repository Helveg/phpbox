<?php

namespace PhpBox\Items;

class Item implements ItemInterface {
  protected $data;
  protected $id;
  protected $type;

  public function __construct($data) {
    $this->data = $data;
    $this->type = $data->type;
    $this->id = $data->id;
  }

  public function getId() {
    return $this->id;
  }

  public function getType() {
    return $this->type;
  }

  public function isFolder() {
    return $this->type == 'folder';
  }

  public function isFile() {
    return $this->type == 'file';
  }
}

?>
