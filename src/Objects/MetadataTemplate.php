<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\TemplateFieldCollection;

class MetadataTemplate extends Object {
  protected $templateKey, $scope, $displayName, $hidden;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data,["templateKey","scope","displayName","hidden"]);
    $this->tryObjectFromData($data, TemplateFieldCollection::class, "fields");
  }
}
