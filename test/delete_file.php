<?php
/*
* 删除storage中文件
*/
require_once('../saestorage.php');
//k5nmzy5445和lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1分别是你的sae app AccessKey 和 SecretKey
$auth = new CF_Authentication('k5nmzy5445', 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1', NULL, "https://auth.sinas3.com");
$auth->authenticate();
$conn = new CF_Connection($auth);
//lazy为你的domain
$file = $conn->get_container('lazy');
//first_test.txt是你待删除的文件
$doc = $file->delete_object("first_test.txt");