<?php

namespace PhpBox\Exception;

class BoxException extends \Exception {
  public function __construct(string $msg, $code = 404) {
    $this->code = $code;
    $this->message = $msg;
  }
}

?>
