<?php

namespace PhpBox\Objects;
use PhpBox\Box;
use PhpBox\Collections\TemplateFieldCollection;

class Metadata extends Object {
  protected $meta_template, $meta_scope, $meta_version, $meta_id, $meta_type, $meta_typeVersion;
  protected $meta = [];

  public function __construct(Box $box, \stdClass $data) {
    $this->responseFields[] = "meta";
    parent::__construct($box, $data);
  }

  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data,["\$template","\$scope","\$parent","\$version","\$id","\$type","\$typeVersion"]); // Will become the meta keys above.
    foreach ($data as $key => $value) {
      if(substr($key,0,1) !== "\$") {
        // It's a meta key
        $this->meta[$key] = $value;
      }
    }
  }

  public function get($key) {
    if(isset($this->meta[$key]))
      return $this->meta[$key];
    return NULL;
  }
}

?>
