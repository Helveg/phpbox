<?php

namespace PhpBox\Objects;

class FileVersionRetention extends BoxObject {
  protected $file_version, $file, $applied_at, $disposition_at, $winning_retention_policy;
  protected function parseResponse(\stdClass $data) {
    $this->tryFromData($data, ["applied_at","disposition_at"],
      function($x){return new \DateTime($x);});
    $this->tryBoxObjectFromData($data, FileVersion::class, "file_version");
    $this->tryBoxObjectFromData($data, File::class, "file");
    $this->tryBoxObjectFromData($data, RetentionPolicy::class, "winning_retention_policy");
  }
}
