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

  protected function tryFromData(\stdClass $data, $extract) {
    if(!is_array($extract)) $extract = [$extract];
    foreach ($extract as $key) {
      if(property_exists($this, $key)) {
        if(isset($data->$key)) $this->$key = $data->$key;
      } else {
        throw new \Exception("Key '$key' does not exist in class ".get_class($this));
      }
    }
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
      $className = "\PhpBox\Objects\\".str_replace("_", "", ucwords($data->type, "_")); // snake_case to CamelCase
      if(class_exists($className)) {
        return new $className($box, $data);
      }
    }
    return new Object($box, $data);
  }
}
