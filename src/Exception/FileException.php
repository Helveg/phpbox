<?php

namespace PhpBox\Exception;

class FileException extends \Exception {
  private $fn;
  public function __construct(string $fileName, string $msg = 'File not found', $code = 404) {
    $this->fn = $fileName;
    $this->code = $code;
    $this->message = $msg;
  }

  public function __toString() {
    return __CLASS__." ({$this->code}) '{$this->fn}' {$this->message}";
  }

}

?>
