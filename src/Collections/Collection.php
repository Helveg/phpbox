<?php

namespace PhpBox\Collections;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class Collection extends \ArrayObject {
  protected $box;
  protected $marker, $nextmarker, $page, $limit;

  public function __construct(Box $box, $data) {
    if(isset($data->page)) $this->page = $data->page;
    if(isset($data->marker)) $this->marker = $data->marker;
    if(isset($data->nextmarker)) $this->nextmarker = $data->nextmarker;
    if(isset($data->limit)) $this->limit = $data->limit;

    $this->box = $box;
    $arr = [];
    if(is_array($data)) {
      $arr = $data;
    } elseif($data instanceof \stdClass) {
      if(!isset($data->entries)) throw new \Exception("Invalid collection data: does not contain an 'entries' field.");
      foreach ($data->entries as $entry) {
        $arr[] = Object::differentiate($box, $entry);
      }
    } else {
      throw new \Exception("Invalid data passed to collection constructor: must be either array or stdClass (json data)");
    }
    parent::__construct($arr);
  }

  public function byId($id) {
    foreach ($this as $value) {
      if($value->id === $id) return $value;
    }
    return false;
  }

  public function first() {
    if($this->count() === 0) throw new \Exception("Can't get first element of empty collection.");
    return array_values($this->getArrayCopy())[0];
  }
}
