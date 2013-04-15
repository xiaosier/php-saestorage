<?php
require_once('saestorage.class.php');
$storage = new SaeStorage('k5nmzy5445','lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1');
$result = $storage->getList('lazy');
var_dump($result);