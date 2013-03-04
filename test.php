<?php
require_once('cloudfiles.php');
$auth = new CF_Authentication('k5nmzy5445', 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1', NULL, "https://auth.sinas3.com");
$auth->authenticate();
$conn = new CF_Connection($auth);
$txt = $conn->get_container('lazy');
//info
$info = $conn->get_info();
var_dump($info);

$bday = $txt->create_object("first_test.txt");
$bday->__set_content_type('text/plain');
$re = $bday->write('This is just frist test');
