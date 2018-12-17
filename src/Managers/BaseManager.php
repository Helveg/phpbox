<?php

namespace PhpBox\Managers;
use \PhpBox\Box;
use \PhpBox\Objects\BoxObject;

class BaseManager {
  protected $box;

  public function __construct(\PhpBox\Box $box) {
    $this->box = $box;
  }

  protected function base_request($url, $fields = [], $query = []) {
    $className = BoxObject::short(static::class);
    $BoxObjectName = substr($className, 0, strlen($className) - 7);
    $BoxObjectClassName = "\\PhpBox\\Objects\\$BoxObjectName";
    $boxBoxObjectName = BoxObject::toBoxObjectstring($BoxObjectName);
    $query = Box::fieldsQuery($fields);
    if($ret = $this->box->guzzle("GET", $url, ["query" => $query]))
      $ret = new $BoxObjectClassName($this->box, $ret);
    return $ret;
  }

  protected function base_create($params = [], $fields = [], $query = []) {
    // https://upload.box.com/api/2.0/files/content
    $className = BoxObject::short(static::class);
    $BoxObjectName = substr($className, 0, strlen($className) - 7);
    $BoxObjectClassName = "\\PhpBox\\Objects\\$BoxObjectName";
    $response = $this->box->guzzle('POST', $BoxObjectClassName::getEndpoint(), [
      'query' => Box::fieldsQuery($fields, $query),
      'json' => $params
    ]);
    if($response) {
      return new $BoxObjectClassName($this->box, $response);
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

  protected function base_update($id, $params, $query, $fields) {
    $className = BoxObject::short(static::class);
    $BoxObjectName = substr($className, 0, strlen($className) - 7);
    $BoxObjectClassName = "\\PhpBox\\Objects\\$BoxObjectName";
    $id = $BoxObjectClassName::toId($id);
    return $this->box->guzzle('PUT', $BoxObjectClassName::getEndpoint().$id, [
      "json" => $params,
      "query" => Box::fieldsQuery($fields, $query)
    ]);
  }
}
