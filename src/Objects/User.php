<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class User extends Object {
  protected $name, $login, $created_at, $modified_at, $language, $timezone, $space_amount, $space_used;
  protected $max_upload_size, $status, $job_title, $phone, $address, $avatar_url, $role, $tracking_codes;
  protected $can_see_managed_users, $is_sync_enabled, $is_external_collabed_restricted, $is_exempt_from_device_limits;
  protected $is_exempt_from_login_verification, $my_tags, $hostname, $is_platform_access_only;
  protected $enterprise;

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["name", "login", "language", "timezone", "space_amount", "space_used",
      "max_upload_size", "status", "job_title", "phone", "address", "avatar_url", "role", "tracking_codes",
      "can_see_managed_users", "is_sync_enabled", "is_external_collab_restricted", "is_exempt_from_device_limits",
      "is_exempt_from_login_verification", "my_tags", "hostname", "is_platform_access_only"]);
    $this->tryFromData($data, ["created_at", "modified_at"], function($x){return new \DateTime($x);});
    $this->tryFromData($data, "enterprise");
  }
}

?>
