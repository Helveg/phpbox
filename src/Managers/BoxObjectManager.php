<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\BoxObject;

class BoxObjectManager extends BaseManager{

  public function request($id, $fields = [], $query = []) {
    $className = BoxObject::short(static::class);
    $BoxObjectName = substr($className, 0, strlen($className) - 7);
    $BoxObjectClassName = "\\PhpBox\\Objects\\$BoxObjectName";
    $boxBoxObjectName = BoxObject::toBoxObjectstring($BoxObjectName);
    if($id instanceof BoxObject) {
      if($id->type == $boxBoxObjectName) {
        $id = $id->id;
      } else {
        throw new \Exception("BoxObject of wrong type given to request, $boxBoxObjectName expected.");
      }
    }
    return $this->base_request("{$boxBoxObjectName}s/$id", $fields, $query);
  }

  public function delete($id, $query = []) {
    $className = BoxObject::short(static::class);
    $BoxObjectName = substr($className, 0, strlen($className) - 7);
    $BoxObjectClassName = "\\PhpBox\\Objects\\$BoxObjectName";
    $endpoint = $BoxObjectClassName::getEndpoint();
    if($id instanceof BoxObject) {
      if($id->type == BoxObject::toBoxObjectstring($BoxObjectName)) {
        $id = $id->id;
      } else {
        throw new \Exception("BoxObject of wrong type given to request, $boxBoxObjectName expected.");
      }
    }
    $this->base_delete($endpoint.$id, $query);
  }
}

?>
