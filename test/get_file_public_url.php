<?php
/*
* 获取storage中文件的url
*/
require_once('../saestorage.php');
//k5nmzy5445和lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1分别是你的sae app AccessKey 和 SecretKey
$auth = new CF_Authentication('k5nmzy5445', 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1', NULL, "https://auth.sinas3.com");
$auth->authenticate();
$conn = new CF_Connection($auth);
$file = $conn->get_container('lazy');
$doc = $file->get_object("remote_file.txt");
var_dump($doc->sae_public_uri());