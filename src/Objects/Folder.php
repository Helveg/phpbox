<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\ItemCollection;

class Folder extends Item {
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

  public function getFiles() {
    return new ItemCollection($this->box, array_filter($this->item_collection->getArrayCopy(), function($x) {return $x->isFile();}));
  }

  public function getFolders() {
    return new ItemCollection($this->box, array_filter($this->item_collection->getArrayCopy(), function($x) {return $x->isFolder();}));
  }

  public function getItems() {
    return $this->item_collection;
  }

  public function findItems($name) {
    $ret = [];
    foreach ($this->item_collection as $key => $value) {
      if(strpos($name, $value->name) !== FALSE) $ret[] = $value;
    }
    return $ret;
  }

  public function getFileByName($name) {
    $arr = array_filter(function($x){return $x->name == $name;},$this->getFiles()->getArrayCopy());
    if(count($arr) === 1) return $arr[0];
    return false;
  }

  public function getFolderByName($name) {
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
