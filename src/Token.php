<?php

namespace PhpBox;

class Token {
  protected $box;
  protected $token;
  protected $expireDateTime;
  protected $type;

  public function __construct(Box $box, $token) {
    $this->box = $box;
    if($token instanceof \stdClass) { // We've received this token as parsed json data.
      $this->parseResponse($token);
    } elseif(is_string($token)) { // We received a token string.
      $this->token = $token;
      $this->type = 'bearer';
      $this->expireDateTime = (new \DateTime())->add(new \DateInterval("PT5M")); // Assume it is still valid for 5 minutes
    } else {
      throw new \Exception("Invalid token data.");
    }
  }

  protected function parseResponse(\stdClass $data) {
    $this->token = $data->access_token;
    $this->expireDateTime = (new \DateTime())->add(new \DateInterval("PT{$data->expires_in}S"));
    $this->type = $data->token_type;
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
