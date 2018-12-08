<?php

include(__DIR__."/vendor/autoload.php");
echo "Hello world! ".$_ENV['BOX_CLIENT_ID'];
var_dump($_ENV);
echo "a";
var_dump($BOX_CLIENT_ID);
echo "b";
var_dump($_SERVER);
echo "c";
var_dump($GLOBALS);
echo "d";
var_dump($BOX_CLIENT_SECRET);
exit(0);

?>
