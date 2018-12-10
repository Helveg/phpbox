<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Comment extends BoxObject {
  protected $item, $created_by, $is_reply_comment;
  protected $message, $tagged_message, $created_at, $modified_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryBoxObjectFromData($data, Item::class, "item");
    $this->tryBoxObjectFromData($data, User::class, "created_by");

    $this->tryFromData($data, ["is_reply_comment","message","tagged_message"]);
    $this->tryFromData($data, ["created_at","modified_at"],
      function($x){return new \DateTime($x);});
  }
}

?>
