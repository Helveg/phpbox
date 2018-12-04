<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class Item extends Object implements ObjectInterface {
  protected $name;

  public function __construct(Box $box, \stdClass $data) {
    $this->tryFromData($data, ["name"]);
    parent::__construct($box, $data);
  }

  public function getName() {
    return $this->name;
  }
}

?>
