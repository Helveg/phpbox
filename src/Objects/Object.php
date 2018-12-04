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
    if(!isset($data->id)) throw new \Exception("All responses must contain an 'id' field.");
    if(!isset($data->type)) throw new \Exception("All responses must contain a 'type' field.");
    $this->id = $data->id;
    $this->type = $data->type;
    $this->parseResponse($data);
  }

  protected function tryFromData(\stdClass $data, $extract, $map = NULL) {
    if(!is_array($extract)) $extract = [$extract];
    foreach ($extract as $key) {
      if(property_exists($this, $key)) {
        if(isset($data->$key)) {
          $cell = $data->$key;
          if($map !== NULL) $cell = $map($cell);
          $this->$key = $cell;
        }
      } else {
        throw new \Exception("Key '$key' does not exist in class ".get_class($this));
      }
    }
  }

  protected function tryObjectFromData(\stdClass $data, $objectName, $prop, $key) {
    if(property_exists($this, $key)) {
      if(isset($data->$key)) {
        $sub_data = $data->$key;
        $this->$prop = new $objectName($this->box, $sub_data);
      }
    } else {
      throw new \Exception("Object '$key' does not exist in class ".get_class($this));
    }
  }

  public function request($fields = []) {
    if($ret = $this->box->guzzleObject(self::toBoxObjectString(static::class)."s/{$this->$id}", $fields))
      $this->parseResponse($ret);
    return $ret;
  }

  public function getId() {
    return $this->id;
  }

  public function getType() {
    return $this->type;
  }

  public function isFolder() {
    return $this->type == 'folder';
  }

  public function isFile() {
    return $this->type == 'file';
  }

  public function isUser() {
    return $this->type == 'user';
  }

  public function toFolder() {
    if($this->isFolder()) return new Folder($this->box, $this->data);
    throw new Exception("This is not a folder.");
  }

  public function toFile() {
    if($this->isFile()) return new File($this->box, $this->data);
    throw new Exception("This is not a file.");
  }

  public function toUser() {
    if($this->isUser()) return new User($this->box, $this->data);
    throw new Exception("This is not a user.");
  }
  /**
   * Differentiate generic response data into an Object type based on the `type`
   * field if such a class exists. If it doesn't a generic Object is returned.
   *
   * Follows this naming convention:
   * * "file" => \PhpBox\Objects\File
   * * "file_version" => \PhpBox\Objects\FileVersion
   * @param  \PhpBox\Box      $box  Box connection that provided this object
   * @param  \stdClass $data        Parsed JSON response
   * @return \PhpBox\Objects\Object (Un)differentiated Object.
   */
  public static function differentiate(Box $box, \stdClass $data) {
    if(isset($data->type)) {
      $className = "\PhpBox\Objects\\".self::toPhpBoxObjectString($data->type); // snake_case to CamelCase
      if(class_exists($className)) {
        return new $className($box, $data);
      }
    }
    return new Object($box, $data);
  }

  public static function toPhpBoxObjectString($phpboxobject) {
    return str_replace("_", "", ucwords($phpboxobject, "_"));
  }

  public static function toBoxObjectString($boxobject) {
    // Thanks to cletus@StackOverflow https://stackoverflow.com/users/18393/cletus
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $boxobject, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
  }
}
