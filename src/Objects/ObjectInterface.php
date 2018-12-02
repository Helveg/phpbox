<?php

namespace PhpBox\Objects;
use PhpBox\Box;

interface ObjectInterface {
  public function getId();
  public function getType();
  public static function differentiate(Box $box, \stdClass $data);
}

?>
