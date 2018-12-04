<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class Object implements ObjectInterface {
  abstract protected function parseResponse(\stdClass $data);
  protected $box;
  protected $data;
  protected $id;
  protected $type;

  const ALL_OBJECTS = [
    "Collaboration",
    "File",
    "Folder",
    "Group",
    "GroupMembership",
    "User"
  ];

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
    if(!in_array($objectName, self::ALL_OBJECTS)) throw new \Exception("$objectName is not a PhpBox\\Object type.");
    if(property_exists($this, $key)) {
      if(isset($data->$key)) {
        $sub_data = $data->$key;
        $this->$prop = new $objectName($this->box, $sub_data);
      }
    } else {
      throw new \Exception("Object '$key' does not exist in class ".get_class($this));
    }
  }

  public function __call($name, $args) {
    $objectName = substr($name, 2);
    $isObject = "is$objectName";
    if(substr($name, 0, 2) == "is" && in_array($objectName, self::ALL_OBJECTS)) {
      // isObject()
      return $this->type == self::toBoxObjectString(basename(static::class));
    } elseif(substr($name, 0, 2) == "to" && in_array($objectName, self::ALL_OBJECTS)) {
      // toObject()
      if($this->$isObject()) return new $objectName($this->box, $this->data);
      throw new Exception("This is not a $objectName.");
    }
    throw new \Exception("Attempt to call unknown method '$name' in '".static::class."'");
  }

  public function __get($name) {
    // To allow readonly access to all the parseResponse fields. Maybe keep a list of all valid props through
    // the tryFromData calls.
    if(property_exists(static::class, $name)) return $this->$name;
    throw new \Exception("Attempt to get unknown/inaccessible property '$name' in '".static::class."'");
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
      $objectName = self::toPhpBoxObjectString($data->type);
      $className = "\PhpBox\Objects\\$objectName"; // snake_case to CamelCase
      if(in_array($objectName, self::ALL_OBJECTS)) {
        return new $className($box, $data);
      }
    }
    throw new \Exception("Unknown box object type received: '{$data->type}'");
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
