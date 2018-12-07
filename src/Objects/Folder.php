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

}

?>
