<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\ItemCollection;

abstract class Item extends Object {
  protected $name, $item_status;
  protected $size, $description, $sequence_id, $etag, $created_at;
  protected $modified_at, $trashed_at, $purged_at, $content_created_at;
  protected $content_modified_at, $expires_at, $has_collaborations;
  protected $permissions, $tags, $is_externally_owned, $can_non_owners_invite;
  protected $is_collaboration_restricted_to_enterprise, $allowed_shared_link_access_levels;
  protected $allowed_invitee_roles;
  protected $is_watermarked;
  protected $path_collection;

  protected $parent;
  protected $created_by, $modified_by, $owned_by;
  protected $shared_link;
  protected $metadata;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name"]);
    $this->tryFromData($data, ["permissions", "tags", "is_externally_owned"]);
    $this->tryFromData($data, ["sequence_id","etag","description","size", "item_status",
      "has_collaborations","allowed_invitee_roles"]);
    $this->tryFromData($data, [
      "created_at","modified_at","trashed_at","purged_at","content_created_at",
      "content_modified_at","expires_at"],
      function($x){return new DateTime($x);});
    if(isset($data->watermark_info)) {
      $this->responseFields[] = "is_watermarked";
      $this->is_watermarked = $data->watermark_info->is_watermarked;
    }
    $this->tryObjectFromData($data, SharedLink::class, "shared_link");
    $this->tryObjectFromData($data, User::class, "owned_by");
    $this->tryObjectFromData($data, User::class, "modified_by");
    $this->tryObjectFromData($data, User::class, "created_by");
    $this->tryObjectFromData($data, Folder::class, "parent");
    $this->tryCollectionFromData($data, ItemCollection::class, "path_collection");
  }

  public function getName() {
    return $this->name;
  }

  public function getPathFolders() {
    if(isset($this->path_collection))
      return $this->path_collection->getArrayCopy();
    throw new Exception("Attempting to access path collection while it was not in the response data for this item. Add it to the fields parameter.");
  }

  public function getPath() {
    if($this->id == 0) return "/";
    return implode("/", array_map(function($x){ return $x->id == 0 ? "" : $x->getName(); }, $this->getPathFolders()));
  }
}

?>
