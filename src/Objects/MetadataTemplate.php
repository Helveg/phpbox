<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\TemplateFieldCollection;

class MetadataTemplate extends Object {
  protected $templateKey, $scope, $displayName, $hidden, $fields;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data,["templateKey","scope","displayName","hidden"]);
    $this->tryCollectionFromData($data, TemplateFieldCollection::class, "fields");
  }
}
