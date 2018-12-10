<?php

namespace PhpBox\Objects;

class RecentItem extends BoxObject {
  protected $item, $interaction_type, $interaction_shared_link, $interacted_at;
  protected function parseResponse(\stdClass $data) {
    $this->tryBoxObjectFromData($data, Item::class, "item");
    $this->tryFromData($data, ["interaction_type","interaction_shared_link"]);
    $this->tryFromData($data, ["interacted_at"],
      function($x){return new \DateTime($x);});
  }
}
