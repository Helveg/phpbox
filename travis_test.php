<?php

include(__DIR__."/vendor/autoload.php");
$boxClientId = $_SERVER['BOX_CLIENT_ID'];
$boxClientSecret = $_SERVER['BOX_CLIENT_SECRET'];
$boxEnterpriseId = $_SERVER['BOX_ENTERPRISE_ID'];
$boxPrivateKey = $_SERVER['BOX_PRIVATE_KEY'];
$boxPrivateKeyPass = $_SERVER['BOX_PRIVATE_KEY_PASS'];
$boxPublicKey = $_SERVER['BOX_PUBLIC_KEY'];

use PhpBox\Config\Config;
use PhpBox\Box;

function testError($msg, $code = 1) {
  echo "[ERROR] $msg\n";
  exit($code);
}

function testOK($msg) {
  echo "[OK] $msg\n";
}

$config = new Config();
$config->setAppDetails($boxClientId, $boxClientSecret);
$config->setPrivateKey(str_replace("\\n","\n",$boxPrivateKey),$boxPrivateKeyPass);
$config->setPublicKey($boxPublicKey);
$config->setConnectionDetails('enterprise', $boxEnterpriseId);

$box = new Box($config);

if(!$box->getAccessToken()) {
  testError("Couldn't get valid access token", 1);
} else {
  testOK("Access token valid");
}
foreach(glob(__DIR__."/src/Objects/*.php") as $file) {
  require_once($file);
  $obj = basename($file);
  if($obj == "Item.php") continue;
  if($obj == "BoxObject.php") continue;
  $objShortName = substr($obj, 0, strlen($obj) - 4);
  $obj = "\\PhpBox\\Objects\\$objShortName";
  $myObj = new $obj($box, (object)["id"=>"5"]);
  testOK("$objShortName can be initialised.");
}

if($folder = $box->Folder->request("0")) {
  testOK("Root folder accessed.");
  if(($count = $folder->getItems()->count()) > 0) {
    testOK("Item collection with $count items.");
  } else {
    testError("Empty root folder");
  }
}

if($folder2 = $folder->create(uniqid())) {
  testOK("Created folder in root folder.");
} else {
  testError("Couldn't create folder in root folder.");
}

if($file = $box->File->create(uniqid().".txt", "wow im good")) {
  testOK("Created file in root folder");
} else {
  testError("Couldn't create file in root folder.");
}
$rename_new_name = uniqid().".txt";
if($check_rename = $file->rename($rename_new_name) && $file->name == $rename_new_name) {
  testOK("File renamed.");
} elseif($check_rename === false) {
  testError("File couldn't be renamed. Request failed.");
} else {
  testError("File couldn't be renamed. New name '{$file->name}' doesn't match input '$rename_new_name'");
}

if($file->description("Fanny packs are toolbelts") && $file->description == "Fanny packs are toolbelts") {
  testOK("File description changed.");
} else {
  testError("File description couldn't be changed. '{$file->description}' should be 'Fanny packs are toolbelts'.");
}

if($file->move($folder2) && $folder2->request()->getItems()->count() == 1) {
  testOK("File moved.");
} else {
  testError("File couldn't be moved.");
}

if($folder->create(uniqid().".txt","to be deleted")->delete()) {
  testOK("File created through folder and chain deleted.");
} else {
  testError("File creation through folder failed, or chain deletion failed.");
}

if($folder->create(uniqid())->delete()) {
  testOK("Empty folder deleted (chained).");
} else {
  testError("Empty folder couldn't be deleted/chained.");
}

if($folder2->delete(true)) {
  testOK("Non-empty folder deleted.");
} else {
  testError("Couldn't remove non-empty folder.");
}

exit(0);

?>
