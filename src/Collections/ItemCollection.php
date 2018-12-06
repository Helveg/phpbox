<?php

namespace PhpBox\Collections;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class ItemCollection extends Collection {
  public function byName($name) {
    foreach ($this as $value) {
      if($value->name === $name) return $value;
    }
    return false;
  }
}
