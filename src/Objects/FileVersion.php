<?php

namespace PhpBox\Objects;


class FileVersion extends Object {
  protected $sha1, $name, $size, $created_at, $modified_at, $modified_by;
  
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["sha1","name","size"]);
    $this->tryFromData($data, ["created_at","modified_at"],
      function($x){return new \DateTime($x);});
    $this->tryObjectFromData($data, User::class, "modified_by");
  }
}
