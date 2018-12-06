<?php

namespace PhpBox\Collections;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class Collection extends \ArrayObject {
  protected $box;

  public function __construct(Box $box, $data) {
    $this->box = $box;
    $arr = [];
    if(is_array($data)) {
      $arr = $data;
    } elseif($data instanceof \stdClass) {
      if(!isset($data->entries)) throw new Exception("Invalid collection data: does not contain an 'entries' field.");
      foreach ($data->entries as $entry) {
        $arr[] = Object::differentiate($box, $entry);
      }
    } else {
      throw new Exception("Invalid data passed to collection constructor: must be either array or stdClass (json data)");
    }
    parent::__construct($arr);
  }

  public function byId($id) {
    foreach ($this as $value) {
      if($value->id === $id) return $value;
    }
    return false;
  }
}
