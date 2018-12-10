<?php

namespace PhpBox\Objects;

class MetadataCascadePolicy extends BoxObject {
  protected $owner_enterprise, $scope, $templateKey, $parent;
  
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["owner_enterprise","scope","templateKey"]);
    $this->tryBoxObjectFromData($data, Item::class, "parent");
  }
}
