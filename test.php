<?php
require_once('saestorage.class.php');
$storage = new SaeStorage('k5nmzy5445','lzxkxy0x2iyili3k113iiw1mz5kimlwk33j5wyl1');
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
$result = $storage->setDomainAttr("lazy", $attr);
var_dump($result);