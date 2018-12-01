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

  public static function isValid($token) {
    if($token == NULL) {
      return false;
    } elseif($token instanceof Token) {
      return $token->expireDateTime > (new \DateTime())->modify('+5 seconds');
    }
  }

  public function __toString() {
    return (string)$this->token;
  }
}

?>
