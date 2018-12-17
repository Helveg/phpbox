<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\ItemCollection;

class Folder extends Item {
  public static $autoRequest = true;
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
        throw new \Exception("No item_collection in Folder BoxObject. Try calling request to get full folder BoxObject or add 'item_collection' to the fields parameter of the last request performed on this BoxObject. Alternatively you might have set Folder::\$autoRequest to false.");
      } elseif($this->self_check_flag) {
        throw new \Exception("Folder item_collection autoRequest failed: item_collection not found after requesting folder BoxObject.");
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
    return new ItemCollection($this->box, $ret);
  }

  public function getFileByName($name) {
    $this->checkItems();
    $arr = array_filter($this->getFiles()->getArrayCopy(),function($x) use ($name) {return $x->name == $name;});
    if(count($arr) === 1) return array_values($arr)[0];
    return false;
  }

  public function getFolderByName($name) {
    $this->checkItems();
    $arr = array_filter($this->getFolders()->getArrayCopy(),function($x) use ($name) {return $x->name == $name;});
    if(count($arr) === 1) return array_values($arr)[0];
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

  public function rename(string $new_name) {
    $ret = $this->box->Folder->rename($this, $new_name);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }

  public function description(string $description) {
    $ret = $this->box->Folder->description($this, $description);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }

  public function move($new_folder) {
    $ret = $this->box->Folder->move($this, $new_folder);
    if($ret && $this->box->getResponseCode() == 200) {
      $this->parseResponse($ret);
    }
    return $ret;
  }
}

?>
