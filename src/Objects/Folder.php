<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\ItemCollection;

class Folder extends Item {
  public static $autoRequest = false;
  private $self_check_flag = false; // Used in checkItems as recursion flag.
  protected $item_collection;
  protected $folder_upload_email, $sync_state, $can_non_owners_invite;
  protected $is_collaboration_restricted_to_enterprise, $allowed_shared_link_access_levels;
  const endpointUrl = Box::baseUrl.'folders/';

  public function parseResponse(\stdClass $data) {
    parent::parseResponse($data);
    $this->tryFromData($data, ["folder_upload_email", "sync_state", "can_non_owners_invite",
    "is_collaboration_restricted_to_enterprise", "allowed_shared_link_access_levels"]);
    $this->tryCollectionFromData($data, ItemCollection::class, "item_collection");
  }

  private function checkItems() {
    if($this->item_collection === NULL) {
      if(self::$autoRequest === false) {
        throw new \Exception("No item_collection in Folder object. Try calling request to get full folder object or add 'item_collection' to the fields parameter. Alternatively set Folder::\$autoRequest to true to automatically request missing item_collections.");
      } elseif($this->self_check_flag) {
        throw new \Exception("Folder item_collection autoRequest failed: item_collection not found after requesting folder object.");
      } else {
        $this->request();
        $this->self_check_flag = true;
        $this->checkItems();
        $this->self_check_flag = false;
      }
    }
  }

  public function getFiles() {
    $this->checkItems();
    return new ItemCollection($this->box, array_filter($this->item_collection->getArrayCopy(), function($x) {return $x->isFile();}));
  }

  public function getFolders() {
    $this->checkItems();
    return new ItemCollection($this->box, array_filter($this->item_collection->getArrayCopy(), function($x) {return $x->isFolder();}));
  }

  public function getItems() {
    $this->checkItems();
    return $this->item_collection;
  }

  public function findItems($name) {
    $this->checkItems();
    $ret = [];
    foreach ($this->item_collection as $key => $value) {
      if(strpos($name, $value->name) !== FALSE) $ret[] = $value;
    }
    return $ret;
  }

  public function getFileByName($name) {
    $this->checkItems();
    $arr = array_filter(function($x){return $x->name == $name;},$this->getFiles()->getArrayCopy());
    if(count($arr) === 1) return $arr[0];
    return false;
  }

  public function getFolderByName($name) {
    $this->checkItems();
    $arr = array_filter(function($x){return $x->name == $name;},$this->getFolders()->getArrayCopy());
    if(count($arr) === 1) return $arr[0];
    return false;
  }

  public function create($name, $content = NULL) {
    if($content !== NULL) { // File
      throw new \Exceptiont("File creation not implemented yet.");
    } else {
      return $this->box->Folder->create($this, $name);
    }
  }

  public function delete($recursive = false) {
    $query = ["recursive" => $recursive];
    return $this->box->Folder->delete($this, $query);
  }

}

?>
