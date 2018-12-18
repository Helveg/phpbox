<?php

namespace PhpBox\Objects;
use PhpBox\Box;

abstract class BoxObject{
  abstract protected function parseResponse(\stdClass $data);
  protected $box;
  protected $data;
  protected $id;
  protected $type;
  protected $responseFields;

  const ALL_Objects = [
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
    $className = BoxObject::short(static::class);
    $boxBoxObjectName = BoxObject::toBoxObjectstring($className);
    // eg "policy" to "policies"
    if(substr($boxBoxObjectName,-1) == "y") $boxBoxObjectName = substr($boxBoxObjectName, 0, strlen($boxBoxObjectName) - 1)."ie";
    return "{$boxBoxObjectName}s/";
  }

  public function __construct(Box $box, \stdClass $data) {
    // Array to store fields with magical readonly access
    // because they are loaded from the guzzle response and are part of the box BoxObject
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

  protected function tryBoxObjectFromData(\stdClass $data, $BoxObjectName, $key) {
    if(!in_array(BoxObject::short($BoxObjectName), self::ALL_Objects)) throw new \Exception("'$BoxObjectName' is not a PhpBox\\BoxObject or Collection type.");
    $this->responseFields[] = $key;
    if(property_exists($this, $key)) {
      if(isset($data->$key)) {
        $this->$key = new $BoxObjectName($this->box, $data->$key);
      }
    } else {
      throw new \Exception("BoxObject '$key' does not exist in class ".get_class($this));
    }
  }

  protected function differentiateFromData(\stdClass $data, $key) {
    if(!isset($data->$key) || !isset(($data->$key)->type)) return false;
    if(in_array($phpboxObj = BoxObject::toPhpBoxObjectstring(($data->$key)->type), BoxObject::ALL_Objects))
      $this->tryBoxObjectFromData($data, "\\PhpBox\\Objects\\$phpboxObj", $key);
  }

  protected function tryCollectionFromData(\stdClass $data, $collectionName, $key) {
    if(property_exists($this, $key)) {
      $this->responseFields[] = $key;
      if(isset($data->$key)) {
        $this->$key = new $collectionName($this->box, $data->$key);
      }
    } else {
      throw new \Exception("BoxObject '$key' does not exist in class ".get_class($this));
    }
  }

  public function __call($name, $args) {
    $BoxObjectName = substr($name, 2);
    $className = "\\PhpBox\\Objects\\$BoxObjectName";
    $isBoxObject = "is$BoxObjectName";
    if(substr($name, 0, 2) == "is" && in_array($BoxObjectName, self::ALL_Objects)) {
      // isBoxObject()
      return $className::isType($this);
    } elseif(substr($name, 0, 2) == "to" && in_array($BoxObjectName, self::ALL_Objects)) {
      // toBoxObject()
      if($this->$isBoxObject()) return new $className($this->box, $this->data);
      throw new Exception("This is not a $BoxObjectName.");
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
    $managerName = BoxObject::short(static::class);
    if(!isset($this->box->$managerName)) throw new \Exception("Objects of type ".static::class." cannot be requested by id. (No manager in PhpBox.)");
    if($this->box->$managerName->request($this->id, $fields)) {
      $this->parseResponse($this->box->getResponseJSON());
    }
    return $this;
  }

  public function delete($query = []) {
    $managerName = BoxObject::short(static::class);
    if(!isset($this->box->$managerName)) throw new \Exception("Objects of type ".static::class." cannot delete themselves. (No manager in PhpBox.)");
    $this->box->$managerName->delete($this, $query);
  }

  /**
   * Differentiate generic response data into an BoxObject type based on the `type`
   * field if such a class exists. If it doesn't a generic BoxObject is returned.
   *
   * Follows this naming convention:
   * * "file" => \PhpBox\Objects\File
   * * "file_version" => \PhpBox\Objects\FileVersion
   * @param  \PhpBox\Box      $box  Box connection that provided this BoxObject
   * @param  \stdClass $data        Parsed JSON response
   * @return \PhpBox\Objects\BoxObject (Un)differentiated BoxObject.
   */
  public static function differentiate(Box $box, \stdClass $data) {
    if(isset($data->type)) {
      $BoxObjectName = self::toPhpBoxObjectstring($data->type);
      $className = "\PhpBox\Objects\\$BoxObjectName"; // snake_case to CamelCase
      if(in_array($BoxObjectName, self::ALL_Objects)) {
        return new $className($box, $data);
      }
    }
    throw new \Exception("Unknown box BoxObject type received: '{$data->type}'");
  }

  public static function toPhpBoxObjectstring($boxBoxObject) {
    return str_replace("_", "", ucwords($boxBoxObject, "_"));
  }

  public static function toBoxObjectstring($phpboxBoxObject) {
    // Thanks to cletus@StackOverflow https://stackoverflow.com/users/18393/cletus
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $phpboxBoxObject, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
  }

  public static function isType($input) {
    return $input instanceof BoxObject && self::toPhpBoxObjectstring($input->type) === BoxObject::short(static::class);
  }

  public function isSelf() {
    $isSelf = "is".BoxObject::short(static::class);
    echo "isSelf: $isSelf\n";
    return $this->$isSelf();
  }

  public static function toId($input) {
    if(is_string($input) || is_numeric($input)) return $input;
    if(!($input instanceof BoxObject)) throw new \Exception("Can only convert numbers, strings or BoxObjects to id.");
    $myClass = static::class;
    // Make sure we inherit from static before we switch the static context to that of $input in the isSelf() call.
    if(($input instanceof $myClass) && $input->isSelf()) return $input->id;
    elseif ($input instanceof $myClass) throw new \Exception("BoxObject with type '{$input->type}' received but ".BoxObject::short(static::class)." expected.");
    else throw new \Exception("BoxObject '".get_class($input)."' does not extend '".static::class."' and cannot be a valid id in this context.");
  }
}
