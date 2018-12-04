<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class ObjectManager implements ObjectManagerInterface {
  protected $box;

  public function __construct(\PhpBox\Box $box) {
    $this->box = $box;
  }

  public function request($id, $fields = [], $query = []) {
    $className = basename(static::class);
    $objectName = substr($className, 0, strlen($className) - 7);
    $boxObjectName = Object::toBoxObjectString($objectName);
    $objectClassName = "\\PhpBox\\Objects\\$objectName";
    if($id instanceof Object && $id->getType() == Object::toBoxObjectString(static::class)) {
      $id = $id->getId();
    }
    $query = Box::fieldsQuery($fields);
    echo "{$boxObjectName}s/$id";
    if($ret = $this->box->guzzle("GET", "{$boxObjectName}s/$id", ["query" => $query]))
      $ret = new $objectClassName($this->box, $ret);
    return $ret;
  }
}

?>
