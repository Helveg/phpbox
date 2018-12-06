<?php

namespace PhpBox\Collections;
use \PhpBox\Box;
use \PhpBox\Objects\TemplateField;

class TemplateFieldCollection extends Collection {

  public function __construct(Box $box, \stdClass $data) {
    $arr = [];
    if(is_array($data)) {
      foreach ($data as $entry) {
        $arr[] = new TemplateField($box, $entry);
      }
    } else {
      throw new \Exception("Invalid data passed to TemplateFieldCollection constructor: must be either array of stdClasses");
    }
    parent::__construct($box, $arr);
  }
}
