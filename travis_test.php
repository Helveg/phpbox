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
  if($obj == "Object.php") continue;
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

if($folder2 = $folder->create("Subfolder test")) {
  testOK("Created folder in root folder.");
} else {
  testError("Couldn't create folder in root folder.");
}

$folder2->delete();

if($box->getResponseCode() == 204) {
  testOK("Subfolder deleted.");
} else {
  testError("Subfolder couldn't be removed.");
}

exit(0);

?>
