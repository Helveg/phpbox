<?php

namespace PhpBox\Collections;
use \PhpBox\Box;
use \PhpBox\Objects\Event;

class EventCollection extends Collection {
  public $chunk_size;
  public $next_stream_position;

  public function __construct(Box $box, \stdClass $data) {
    if(isset($data->chunk_size)) $this->chunk_size = $data->chunk_size;
    if(isset($data->next_stream_position))$this->next_stream_position = $data->next_stream_position;
    $arr = [];
    if(isset($data->entries)) {
      foreach ($data->entries as $entry) {
        $arr[] = new Event($box, $entry);
      }
    } else {
      throw new \Exception("Invalid data passed to EvejtCollection constructor, no entries field is set.");
    }
    parent::__construct($box, $arr);
  }
}
