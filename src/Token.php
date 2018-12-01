<?php

namespace PhpBox;

class Token {
  protected $token;
  protected $expireDateTime;
  protected $type;

  public function __construct($token) {
    if($token instanceof \stdClass) {
      $this->token = $token->access_token;
      $this->expireDateTime = (new \DateTime())->add(new \DateInterval("PT{$token->expires_in}S"));
      $this->type = $token->token_type;
    } elseif(is_string($token)) { // Assume token is valid?
      $this->token = $token;
      $this->type = 'bearer';
      $this->expireDateTime = (new \DateTime())->add(new \DateInterval("PT5M"));
    } else {
      throw new \Exception("Invalid token data.");
    }
  }

  public function __toString() {
    if($this->expireDateTime > new \DateTime()) {
      $expireStr = " (expires at ".$this->expireDateTime->format("d/m/Y H:i:s").")";
    } else {
      $expireStr = " (expired)";
    }
    return "Token [{$this->token}]$expireStr";
  }
}

?>
