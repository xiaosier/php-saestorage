PHP SDK For SAE Storage
=================

此版本PHP SDK 不依赖于SAE WEB runtime，可以在远程使用SAE提供的云存储服务.

我们鼓励对于运行在SAE上的应用仍然使用我们集成在runtime中的storage api操作storage中存储的文件，但如果您有在您自己主机上使用
我们提供的云存储服务时您可以使用这个PHP SDK.


使用demo
-----

首先您需要注册一个SAE的账号，创建一个应用并创建一个domain初始化storage服务。以上的操作均需要到 http://sae.sina.com.cn 完成

然后就可以使用此php sdk，一个运行的例子:

``` php
<?php
require_once('cloudfiles.php');
$auth = new CF_Authentication('your accesskey', 'your secretkey', NULL, "https://auth.sinas3.com");
$auth->authenticate();
$conn = new CF_Connection($auth);
$txt = $conn->get_container('your domain name');
$bday = $txt->create_object("first_test.txt");
$bday->__set_content_type('text/plain');
$re = $bday->write('This is just frist test');
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