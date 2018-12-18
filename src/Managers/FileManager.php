<?php

namespace PhpBox\Managers;
use \PhpBox\Objects\Folder;
use \PhpBox\Collections\Collection;

class FileManager extends ItemManager {

  public function create(string $name, $contents, $parent = "0", $params = []) {
    if(is_resource($contents)) {
      $fh = $contents;
    } elseif(is_string($contents)) {
      $fh = tmpfile();
      fwrite($fh, $contents);
      rewind($fh);
    }
    $multipartParams = [];
    $meta = stream_get_meta_data($fh);
    $params['headers'] = ["Content-MD5" => sha1_file($meta['uri'])];
    $params['name'] = $name;
    $params['parent'] = ["id" => Folder::toId($parent)];
    $multipartParams[] = [
      "name" => "attributes",
      "contents" => json_encode($params, JSON_FORCE_OBJECT)
    ];
    $multipartParams[] = [
      "name" => "file",
      "contents" => $fh
    ];
    $ret = $this->box->guzzle('POST', 'https://upload.box.com/api/2.0/files/content', [
      'multipart' => $multipartParams
    ]);
    if($ret) {
      return (new Collection($this->box, $ret))->first();
    } elseif($this->box->getResponseCode() == 409) {
      throw new \Exception("A file named '$name' aready exists in folder '$parent'.");
    }
    return false;
  }

}

?>
