<?php

include(__DIR__."/vendor/autoload.php");
echo "Hello world! ".$_ENV['BOX_CLIENT_ID'];
var_dump($_ENV['BOX_PRIVATE_KEY']);
exit(0);

?>
