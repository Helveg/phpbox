<?php

namespace PhpBox\Objects;

class MetadataCascadePolicy extends Object {
  protected $owner_enterprise, $scope, $templateKey, $parent;
  
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["owner_enterprise","scope","templateKey"]);
    $this->tryObjectFromData($data, Item::class, "parent");
  }
}
