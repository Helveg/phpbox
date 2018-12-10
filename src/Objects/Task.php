<?php

namespace PhpBox\Objects;
use PhpBox\Box;

class Task extends BoxObject {
  protected $created_by, $item;
  protected $message,$action,$task_assigment_collection,$is_completed;
  protected $due_at, $created_at;

  protected function parseResponse(\stdClass $data) {
    $this->tryBoxObjectFromData($data, Item::class, "item");
    $this->tryBoxObjectFromData($data, User::class, "created_by");

    $this->tryFromData($data, ["message","action","task_assigment_collection","is_completed"]);
    $this->tryFromData($data, ["due_at","created_at"],
      function($x){return new \DateTime($x);});
  }
}

?>
