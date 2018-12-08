<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\Object;

class BaseManager {
  protected $box;

  public function __construct(\PhpBox\Box $box) {
    $this->box = $box;
  }

  protected function base_request($url, $fields = [], $query = []) {
    $className = Object::short(static::class);
    $objectName = substr($className, 0, strlen($className) - 7);
    $objectClassName = "\\PhpBox\\Objects\\$objectName";
    $boxObjectName = Object::toBoxObjectString($objectName);
    $query = Box::fieldsQuery($fields);
    if($ret = $this->box->guzzle("GET", $url, ["query" => $query]))
      $ret = new $objectClassName($this->box, $ret);
    return $ret;
  }

  protected function base_create($params = [], $fields = []) {
    // https://upload.box.com/api/2.0/files/content
    $className = Object::short(static::class);
    $objectName = substr($className, 0, strlen($className) - 7);
    $objectClassName = "\\PhpBox\\Objects\\$objectName";
    $boxObjectName = Object::toBoxObjectString($objectName);
    $response = $this->box->guzzle('POST', "{$boxObjectName}s/", [
      'query' => Box::fieldsQuery($fields),
      'json' => $params
    ]);
    if($response) {
      return new $objectClassName($this->box, $response);
    }
    return false;
  }

  protected function base_delete($url, $query) {
    $response = $this->box->guzzle('DELETE', $url, ['query' => $query]);
    if($this->box->getResponseCode() == 204) {
      return true;
    }
    return false;
  }
}
