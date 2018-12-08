<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class Object{
  abstract protected function parseResponse(\stdClass $data);
  protected $box;
  protected $data;
  protected $id;
  protected $type;
  protected $responseFields;

  const ALL_OBJECTS = [
    "Collaboration","CollaborationWhitelistEntry",
    "Comment","DevicePinner","Event","File","FileVersion",
    "FileVersionRetention","Folder","Group","GroupMembership",
    "Item","LegalHold","LegalHoldPolicy","LegalHoldPolicyAssignment",
    "Metadata","MetadataCascadePolicy","MetadataTemplate",
    "RecentItem","RetentionPolicy","RetentionPolicyAssignment","SharedLink",
    "StoragePolicy","StoragePolicyAssignment","Task","TemplateField",
    "TermsOfService","User","Webhook","WebLink"
  ];

  public static function short($name) {
    $out = explode("\\",$name);
    return end($out);
  }

  public static function getEndpoint() {
    $className = Object::short(static::class);
    $boxObjectName = Object::toBoxObjectString($className);
    // eg "policy" to "policies"
    if(substr($boxObjectName,-1) == "y") $boxObjectName = substr($boxObjectName, 0, strlen($boxObjectName) - 1)."ie";
    return "{$boxObjectName}s/";
  }

  public function __construct(Box $box, \stdClass $data) {
    // Array to store fields with magical readonly access
    // because they are loaded from the guzzle response and are part of the box object
    $this->responseFields = ["id","type"];
    $this->box = $box;
    $this->data = $data;
    if(isset($data->id)) $this->id = $data->id;
    if(isset($data->type)) $this->type = $data->type;
    $this->parseResponse($data);
  }

  protected function tryFromData(\stdClass $data, $extract, $map = NULL) {
    if(!is_array($extract)) $extract = [$extract];
    foreach ($extract as $key) {
      $prop_key = (substr($key, 0, 1) === "\$" ? "meta_" : substr($key, 0, 1)).substr($key, 1); // Replace the $keys by meta_keys for Box metadata.
      if(property_exists($this, $prop_key)) {
        $this->responseFields[] = $prop_key;
        if(isset($data->$key)) {
          $cell = $data->$key;
          if($map !== NULL) $cell = $map($cell);
          $this->$prop_key = $cell;
        }
      } else {
        throw new \Exception("Key '$prop_key' does not exist in class ".get_class($this));
      }
    }
  }

  protected function tryObjectFromData(\stdClass $data, $objectName, $key) {
    if(!in_array(Object::short($objectName), self::ALL_OBJECTS)) throw new \Exception("'$objectName' is not a PhpBox\\Object or Collection type.");
    $this->responseFields[] = $key;
    if(property_exists($this, $key)) {
      if(isset($data->$key)) {
        $this->$key = new $objectName($this->box, $data->$key);
      }
    } else {
      throw new \Exception("Object '$key' does not exist in class ".get_class($this));
    }
  }

  protected function tryCollectionFromData(\stdClass $data, $collectionName, $key) {
    if(property_exists($this, $key)) {
      $this->responseFields[] = $key;
      if(isset($data->$key)) {
        $this->$key = new $collectionName($this->box, $data->$key);
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
      return $this->type == self::toBoxObjectString(Object::short(static::class));
    } elseif(substr($name, 0, 2) == "to" && in_array($objectName, self::ALL_OBJECTS)) {
      // toObject()
      if($this->$isObject()) return new $objectName($this->box, $this->data);
      throw new Exception("This is not a $objectName.");
    }
    throw new \Exception("Attempt to call unknown method '$name' in '".static::class."'");
  }

  public function isItem() {
    return $this->isFolder() || $this->isFile();
  }

  public function __get($name) {
    // To allow readonly access to all the Box response data fields.
    if(property_exists(static::class, $name) && in_array($name, $this->responseFields)) return $this->$name;
    throw new \Exception("Attempt to get unknown/inaccessible property '$name' in '".static::class."'");
  }

  public function request($fields = []) {
    $managerName = Object::short(static::class);
    if(!isset($this->box->$managerName)) throw new \Exception("Objects of type ".static::class." cannot be requested by id. (No manager in PhpBox.)");
    if($this->box->$managerName->request($this->id, $fields)) {
      $this->parseResponse($this->box->getResponseJSON());
    }
    return $this;
  }

  public function delete($query = []) {
    $managerName = Object::short(static::class);
    if(!isset($this->box->$managerName)) throw new \Exception("Objects of type ".static::class." cannot delete themselves. (No manager in PhpBox.)");
    $this->box->$managerName->delete($this, $query);
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

  public static function toPhpBoxObjectString($boxobject) {
    return str_replace("_", "", ucwords($boxobject, "_"));
  }

  public static function toBoxObjectString($phpboxobject) {
    // Thanks to cletus@StackOverflow https://stackoverflow.com/users/18393/cletus
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $phpboxobject, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
  }
}
