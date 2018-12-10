<?php

namespace PhpBox\Objects;


class Event extends BoxObject {
  protected $created_by;
  protected $event_id, $event_type, $session_id, $additional_details;
  protected $created_at, $recorded_at;
  protected $source;

  public function __construct(\PhpBox\Box $box, \stdClass $data) {
    $this->responseFields[] = "source";
  }

  protected function parseResponse(\stdClass $data) {
    $this->id = $data->event_id;
    $this->type = $data->event_type;
    $this->tryFromData($data, ["event_id","session_id","event_type", "additional_details"]);
    $this->tryFromData($data, ["created_at", "recorded_at"],
      function($x){return new \DateTime($x);});
    $this->tryBoxObjectFromData($data, User::class, "created_by");
    if(isset($data->source))
      $this->source = BoxObject::differentiate($box, $data->source);
  }
}
