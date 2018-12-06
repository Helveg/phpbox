<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class ObjectManager extends BaseManager{

  public function request($id, $fields = [], $query = []) {
    $className = basename(static::class);
    $objectName = substr($className, 0, strlen($className) - 7);
    $objectClassName = "\\PhpBox\\Objects\\$objectName";
    $boxObjectName = Object::toBoxObjectString($objectName);
    if($id instanceof Object) {
      if($id->type == Object::toBoxObjectString(static::class)) {
        $id = $id->id;
      } else {
        throw new \Exception("Object of wrong type given to request");
      }
    }
    return $this->base_request("{$boxObjectName}s/$id", $fields, $query);
  }
}

?>
