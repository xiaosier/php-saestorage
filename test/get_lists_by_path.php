<?php
/*
* 按路径读取domain中的文件列表
*/
require_once('../saestorage.php');
//k5nmzy5445和lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1分别是你的sae app AccessKey 和 SecretKey
$auth = new CF_Authentication('k5nmzy5445', 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1', NULL, "https://auth.sinas3.com");
$auth->authenticate();
$conn = new CF_Connection($auth);
$container = $conn->get_container('lazy');
$list = $container->list_objects(0,NULL,NULL,NULL);
var_dump($list);
//通过get_objects函数可以获得文件详细信息
$list_detail = $container->get_objects(0,NULL,NULL,NULL,NULL);
//var_dump($list_detail);