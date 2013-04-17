PHP SDK For SAE Storage
=================

此版本PHP SDK 不依赖于SAE WEB runtime，可以在远程使用SAE提供的云存储服务.

我们鼓励对于运行在SAE上的应用仍然使用我们集成在runtime中的storage api操作storage中存储的文件，但如果您有在您自己主机上使用
我们提供的云存储服务时您可以使用这个PHP SDK.

获取domain列表
``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
$storage = new SaeStorage($ak, $sk);
$domList = $storage->lsdom();
print_r($domList);
?>
```

获取指定domain文件列表

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$fileList = $storage->getList($domain);
print_r($fileList);
?>
```

写入一个文件

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$destFileName = 'write_test.txt';
$content = 'Hello,I am from the method of write';
$attr = array('encoding'=>'gzip');
$storage = new SaeStorage($ak, $sk);
$result = $storage->write($domain,$destFileName, $content, -1, $attr, true);
var_dump($result);
?>
```

获取一个文件URL地址

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$fileName = 'write_test.txt';
$storage = new SaeStorage($ak, $sk);
$fileUrl = $storage->getUrl($domain,$fileName);
var_dump($fileUrl);
?>
```

获取一个文件CDN URL地址(需要在SAE管理平台开启CDN)

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$fileName = 'write_test.txt';
$storage = new SaeStorage($ak, $sk);
$fileUrl = $storage->getCDNUrl($domain,$fileName);
var_dump($fileUrl);
?>
```

获取文件的属性

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
#your file name
$filename = 'interface.txt';
$storage = new SaeStorage($ak, $sk);
$fileAttr = $storage->getAttr($domain,$filename);
var_dump($fileAttr);
?>
```

读取一个文件内容

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
#your file name
$filename = 'interface.txt';
$storage = new SaeStorage($ak, $sk);
$fileContent = $storage->read($domain,$filename);
var_dump($fileContent);
?>
```

删除一个文件

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
#your file name
$filename = 'interface.txt';
$storage = new SaeStorage($ak, $sk);
$deleteResult = $storage->delete($domain,$filename);
var_dump($deleteResult);
?>
```

设置文件属性

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
#your file name
$filename = 'interface.txt';
$attr = array('expires' => 'access plus 1 year');
$storage = new SaeStorage($ak, $sk);
$setAttrResult = $storage->setFileAttr($domain,$filename,$attr);
var_dump($setAttrResult);
?>
```

设置domain属性

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$expires = 'ExpiresActive On
ExpiresDefault "access plus 30 days"
ExpiresByType text/html "access plus 1 month 15 days 2 hours"
ExpiresByType image/gif "modification plus 5 hours 3 minutes"
ExpiresByType image/jpg A2592000
ExpiresByType text/plain M604800
';
$allowReferer['hosts'][] = 'lazydemo.sinaapp.com';
$allowReferer['hosts'][] = '*.lazydemo.sinaapp.com';
$allowReferer['redirect'] = 'http://lazydemo.sinaapp.com/';
$tag = array('hellotag');
$attr = array('expires'=>$expires, 'allowReferer'=>$allowReferer,'tag'=>$tag);
$storage = new SaeStorage($ak, $sk);
$setDomainAttrResult = $storage->setDomainAttr($domain,$attr);
var_dump($setDomainAttrResult);
?>
```

获取domain下的文件总数

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$storage = new SaeStorage($ak, $sk);
$filesNum = $storage->getFilesNum($domain);
var_dump($filesNum);
?>
```

获取domain的容量信息

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
$storage = new SaeStorage($ak, $sk);
$filesNum = $storage->getDomainCapacity($domain);
var_dump($filesNum);
?>
```

判断文件是否存在

``` php
<?php
require_once('saestorage.class.php');
#your app accesskey
$ak = 'k5nmzy5445';
#your app secretkey
$sk = 'lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1';
#your domain name
$domain = 'lazy';
#your file name
$filename_exist = 'interface.txt';
$fikename_notexist = 'interface2.txt';
$storage = new SaeStorage($ak, $sk);
$exist = $storage->fileExists($domain,$filename_exist);
$not_exist = $storage->fileExists($domain,$fikename_notexist);
var_dump($exist,$not_exist);
?>
```

Bug tracker
-----------

Have a bug? Please create an issue here on GitHub!

https://github.com/xiaosier/php-saestorage/issues


Authors
-------

+ http://weibo.com/lazypeople
+ http://skirt.sinaapp.com


License
---------------------

Copyright 2011 SINA, Inc.
Copyright 2011 SAE

Licensed under the Apache License, Version 2.0: http://www.apache.org/licenses/LICENSE-2.0