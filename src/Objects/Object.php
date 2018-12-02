<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class Object implements ObjectInterface {
  abstract protected function parseResponse(\stdClass $data);
  protected $box;
  protected $data;
  protected $id;
  protected $type;

  public function __construct(Box $box, \stdClass $data) {
    $this->box = $box;
    $this->data = $data;
    $this->id = $data->id;
    $this->type = $data->type;
    $this->parseResponse($data);
  }

  public function getId() {
    return $this->id;
  }

  public function getType() {
    return $this->type;
  }

  public static function differentiate(Box $box, \stdClass $data) {
    if(isset($data->type)) {
      $className = "\PhpBox\Objects\\".str_replace("_", "", ucwords($data->type, "_")); // snake_case to CamelCase
      if(class_exists($className)) {
        return new $className($box, $data);
      }
    }
    return new Object($box, $data);
  }
}
