<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class File extends Item {
  protected $sequence_id, $etag, $sha1, $description, $size, $created_at;
  protected $modified_at, $trashed_at, $purged_at, $content_created_at;
  protected $content_modified_at, $expires_at, $item_status, $version_number;
  protected $comment_count, $tags, $extension, $is_package, $expiring_embed_link;
  protected $allowed_invitee_roles, $is_externally_owned, $has_collaborations;
  protected $MetaData;
  protected $Watermark;
  protected $Version;
  protected $Creater, $Modifier, $Owner;
  protected $SharedLink;
  protected $PathCollection;
  protected $Parent;
  protected $Permissions;
  protected $Lock;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["sequence_id","etag","sha1","description","size",
      "item_status","version_number","comment_count","tags","extension","is_package",
      "expiring_embed_link","allowed_invitee_roles","is_externally_owned","has_collaborations"]);
    $this->tryFromData($data, ["created_at","modified_at","trashed_at","purged_at","content_created_at",
      "content_modified_at","expires_at"], function($x){return new DateTime($x);});
    $this->tryObjectFromData($data, FileVersion::class, "Version", "file_version");
  }
}

?>
