<?php

namespace PhpBox\Managers;

interface ObjectManagerInterface {
  function request($id, $fields = [], $query = []);
}

?>
