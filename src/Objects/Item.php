<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class Item extends Object implements ObjectInterface {
  public function __construct(Box $box, \stdClass $data) {
    $this->name = $data->name;
    parent::__construct($box, $data);
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

  public function toFolder() {
    if($this->isFolder()) return new Folder($this->box, $this->data);
    throw new Exception("This is not a folder.");
  }

  public function toFile() {
    if($this->isFile()) return new File($this->box, $this->data);
    throw new Exception("This is not a file.");
  }

  public function request($fields = []) {

  }
}

?>
