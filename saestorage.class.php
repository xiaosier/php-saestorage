<?php
/**
 * This is the PHP SDK API For SAE Storage Service.
 *
 *
 * See COPYING for license information.
 *
 * @author Lazypeople <lazy@changes.com.cn>
 * @copyright Copyright (c) 2013, Sina App Engine.
 * @package sae
 */
require_once(dirname(__FILE__).'/swiftclient.php');
define('SAE_CDN_ENABLED',false);

class SaeStorage
{
	private $accessKey  = '';
	private $secretKey  = '';
	private $errMsg     = 'success';
	private $errNum     = 0;
	private $appName    = '';
	private $restUrl    = '';
	private $filePath   = '';
	private $basedomain = 'stor.sinaapp.com';
	private $cdndomain  = 'sae.sinacdn.com';
    #swift class
    private $auth;
    private $swift_conn;
	
	/**
     * Class constructor (PHP 5 syntax)
     *
     * @param string $accessKey AccessKey of Appname
     * @param string $secretKey SecretKey of Appname
     * @param string $appName   Appname 
     */
    function __construct($accessKey=NULL, $secretKey=NULL)
    {

        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->auth = new CF_Authentication($this->accessKey,$this->secretKey, NULL, "https://auth.sinas3.com");
        $this->auth->authenticate();
        $this->swift_conn = new CF_Connection($this->auth);
    }

    /**
     * Get the Error Message when something goes wrong
     */
    public function errmsg()
    {
        $ret = $this->errMsg." url(".$this->filePath.")";
        $this->restUrl = '';
        $this->errMsg = 'Success';
        return $ret;
    }

   /**
     * Get the Error Code when something goes wrong
     */
    public function errno()
    {
        $ret = $this->errNum;
        $this->errNum = 0;
        return $ret;
    }


    /**
     * Set the Appname,provide for Admin class
     */
    public function setAppname()
    {
        $storage_url = $this->auth->storage_url;
        $explode_array = explode("_", $storage_url,2);
        $this->appName = $explode_array[1];
    }

    /**
     * Get the Appname,provide for Admin class
     */
    public function getAppname()
    {
        return $this->appName;
    }

    /**
    * List domain
    */
    public function lsdom()
    {
        $info = $this->swift_conn->list_containers();
        return $info;
    }

    /**
     * Get the CDN url of a storage file when user's CDN is enabled.
     *
     * Example:
     * <code>
     * #Get a CDN url
     * $stor = new SaeStorage();
     * $cdn_url = $stor->getCDNUrl("domain","cdn_test.txt");
     * </code>
     *
     * @param string $domain Domain name
     * @param string $filename Filename you save
     * @return string.
     */
    public function getCDNUrl( $domain, $filename ) 
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( SAE_CDN_ENABLED ) {
            $filePath = "http://".$this->appName.'.'.$this->cdndomain . "/.app-stor/$domain/$filename";
        } else {
            $domain = $this->getDom($domain);
            $filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        }
        return $filePath;
    }

    /**
     * Get the url of a storage file.
     *
     * Example:
     * <code>
     * #Get the url of a stored file
     * $stor = new SaeStorage();
     * $file_url = $stor->getUrl("domain","cdn_test.txt");
     * </code>
     *
     * @param string $domain Domain name
     * @param string $filename Filename you save
     * @return string.
     */
    public function getUrl( $domain, $filename ) 
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);
        $domain = $this->getDom($domain);

        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        return $this->filePath;
    }

    /**
     * Set File url.
     *
     * @param string $domain 
     * @param string $filename The filename you wanna set
     * @return string.
     */
    private function setUrl( $domain , $filename )
    {
        $domain   = trim($domain);
        $filename = trim($filename);
        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
    }


     /**
     * Write a file to storage.
     *
     * Example:
     * <code>
     * # Write some content into a storage file
     * #
     * $storage = new SaeStorage();
     * $domain = 'domain';
     * $destFileName = 'write_test.txt';
     * $content = 'Hello,I am from the method of write'
     * $attr = array('encoding'=>'gzip');
     * $result = $storage->write($domain,$destFileName, $content, -1, $attr, true);
     *
     * </code>
     *
     * The `domain` must be Exist
     *
     * @param string $domain Domain name
     * @param string $destFileName The destiny fileName.
     * @param string $content The content of the file
     * @param int    $size The length of file content,the overflower will be truncated and by default there is no limit.
     * @param array  $attr File attributes, set attributes refer to SaeStorage :: setFileAttr () method
     * @param boolean $compress 
     * #Note: Whether gzip compression. 
     *        If true, the file after gzip compression and then stored in Storage, 
     *        often associated with $attr=array('encoding'=>'gzip') used in conjunction 
     * @return mixed 
     * #Note: If success,return the url of the file
     *        If faild;return false 
     */
    public function write( $domain, $destFileName, $content, $size = -1, $attr = array(), $compress = false )
    {
        $domain = trim($domain);
        $destFileName = $this->formatFilename($destFileName);

        if (empty($domain) or empty($destFileName)) {
            $this->errMsg = 'The value of parameter (domain,destFileName,content) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        if ( $size > -1 )
            $content = substr( $content, 0, $size );

        $srcFileName = tempnam('/tmp', 'SAE_STOR_UPLOAD');
        if ($compress) {
            file_put_contents("compress.zlib://" . $srcFileName, $content);
        } else {
            file_put_contents($srcFileName, $content);
        }

        $re = $this->upload($domain, $destFileName, $srcFileName, $attr);
        unlink($srcFileName);
        return $re;
    }

    /**
     * upload a file to storage.
     *
     * Example:
     * <code>
     * #
     * $storage = new SaeStorage();
     * $domain = 'domain';
     * $destFileName = 'write_test.txt';
     * $srcFileName = $_FILE['tmp_name']
     * $attr = array('encoding'=>'gzip');
     * $result = $storage->upload($domain,$destFileName, $srcFileName, -1, $attr, true);
     *
     * </code>
     *
     * The `domain` must be Exist
     *
     * @param string $domain Domain name
     * @param string $destFileName The destiny fileName.
     * @param string $srcFileName The source of the uoload file
     * @param array  $attr File attributes, set attributes refer to SaeStorage :: setFileAttr () method
     * @param boolean $compress 
     * #Note: Whether gzip compression. 
     *        If true, the file after gzip compression and then stored in Storage, 
     *        often associated with $attr=array('encoding'=>'gzip') used in conjunction 
     * @return mixed 
     * #Note: If success,return the url of the file
     *        If faild;return false 
     */
    public function upload( $domain, $destFileName, $srcFileName, $attr = array(), $compress = false )
    {
        $domain = trim($domain);
        $destFileName = $this->formatFilename($destFileName);

        if ( empty($domain) or empty($destFileName) or empty($srcFileName)) {
            $this->errMsg = 'The value of parameter (domain,destFile,srcFileName) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        if ($compress) {
            $srcFileNew = tempnam( '/tmp', 'SAE_STOR_UPLOAD');
            file_put_contents("compress.zlib://" . $srcFileNew, file_get_contents($srcFileName));
            $srcFileName = $srcFileNew;
        }
        $parseAttr = $this->parseFileAttr($attr);
        $this->setUrl( $domain, $destFileName );
        $container = $this->swift_conn->get_container($domain);
        $object = $container->create_object($destFileName);
        $object->__getMimeType($destFileName);
        $result = $object->load_from_filename($srcFileName);
        if($result) {
            return $this->getUrl($domain,$destFileName);
        } else {
            $this->errMsg = 'Failed to store to filesystem!!';
            $this->errNum = 121;
            return false;
        }

    }

    /**
     * 获取指定domain下的文件名列表
     *
     * <code>
     * <?php
     * // 列出 Domain 下所有路径以photo开头的文件
     * $stor = new SaeStorage();
     *
     * $num = 0;
     * while ( $ret = $stor->getList("test", "photo", 100, $num ) ) { 
     *      foreach($ret as $file) {
     *          echo "{$file}\n";
     *          $num ++; 
     *      }   
     * }
     * 
     * echo "\nTOTAL: {$num} files\n";
     * ?>
     * </code>
     *
     * @param string $domain    存储域,在在线管理平台.storage页面可进行管理
     * @param string $prefix    路径前缀
     * @param int $limit        返回条数,最大100条,默认10条
     * @param int $offset       起始条数。limit与offset之和最大为10000，超过此范围无法列出。
     * @return array 执行成功时返回文件列表数组，否则返回false
     */
    public function getList( $domain, $prefix=NULL, $limit=10, $offset = 0 )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'The value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $list_detail = $container->get_objects($limit,$offset,$prefix);
        $list_detail_array = $this->std_class_object_to_array($list_detail);
        $list_detail_new = array();
        foreach($list_detail_array as $small) {
            $list_detail_new[] = array(
                'fileName'=>$small['name'],
                'datetime'=>$small['last_modified'],
                'content_type'=>$small['content_type'],
                'length'=>$small['content_length'],
                'md5sum'=>$small['etag']
                );
        }
        return $list_detail_new;
    }

    /**
     * 获取指定Domain、指定目录下的文件列表
     *
     * @param string $domain    存储域
     * @param string $path      目录地址
     * @param int $limit        单次返回数量限制，默认100，最大1000
     * @param int $offset       起始条数
     * @param int $fold         是否折叠目录
     * @return array 执行成功时返回列表，否则返回false
     */
    public function getListByPath( $domain, $path = NULL, $limit = 100, $offset = 0, $fold = true )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $list_detail = $container->get_objects($limit,$offset,NULL,$path);
        $list_detail_array = $this->std_class_object_to_array($list_detail);
        $list_detail_new = array();
        foreach($list_detail_array as $small) {
            $list_detail_new[] = array(
                'fileName'=>$small['name'],
                'datetime'=>$small['last_modified'],
                'content_type'=>$small['content_type'],
                'length'=>$small['content_length'],
                'md5sum'=>$small['etag'],
                'expires'=>array_key_exists('Expires-Rule', $small['metadata'])?$small['metadata']['Expires-Rule']:NULL
                );
        }
        return $list_detail_new;        
    }

    /**
     * 获取指定domain下的文件数量
     *
     *
     * @param string $domain    存储域,在在线管理平台.storage页面可进行管理
     * @param string $path      目录(暂没实现)
     * @return array 执行成功时返回文件数，否则返回false
     */
    public function getFilesNum( $domain, $path = NULL )
    {
        $domain = trim($domain);

        if ( $domain == '' ) {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $info = $this->swift_conn->get_container($domain);
        $info_array = $this->std_class_object_to_array($info);
        return $info_array['object_count'];
    }

    /**
     * 获取文件属性
     *
     * @param string $domain    存储域
     * @param string $filename  文件地址
     * @param array  $attrKey    属性值,如 array("fileName", "length")，当attrKey为空时，以关联数组方式返回该文件的所有属性。
     * @return array 执行成功以数组方式返回文件属性，否则返回false
     */
    public function getAttr( $domain, $filename, $attrKey=array() )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $object = $container->get_object($filename);
        $object = $this->std_class_object_to_array($object);
        if(!empty($object['last_modified'])) {
            $file_attr = array(
                'fileName'=>$object['name'],
                'datetime'=>$object['last_modified'],
                'content_type'=>$object['content_type'],
                'length'=>$object['content_length'],
                'md5sum'=>$object['etag'],
                'expires'=>array_key_exists('Expires-Rule', $object['metadata'])?$object['metadata']['Expires-Rule']:NULL
                );
        } else {
            $file_attr = false;
        }
        return $file_attr;       
    }


    /**
     * 检查文件是否存在
     *
     * @param string $domain    存储域
     * @param string $filename  文件地址
     * @return bool
     */
    public function fileExists( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $file_exist = $this->getAttr($domain,$filename);
        return ($file_exist === false)?false:true;
    }

    /**
     * 获取文件的内容
     *
     * @param string $domain 
     * @param string $filename 
     * @return string 成功时返回文件内容，否则返回false
     */
    public function read( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $object = $container->get_object($filename);
        $data = $object->read();
        return $data;        
    }

    /**
     * 删除文件
     *
     * @param string $domain 
     * @param string $filename 
     * @return bool
     */
    public function delete( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $result = $container->delete_object($filename);
        return $result;        
    }

    /**
     * 设置文件属性
     *
     * 目前支持的文件属性
     *  - expires: 浏览器缓存超时，功能与Apache的Expires配置相同
     *  - encoding: 设置通过Web直接访问文件时，Header中的Content-Encoding。
     *  - type: 设置通过Web直接访问文件时，Header中的Content-Type。
     *  - private: 设置文件为私有，则文件不可被下载。
     *
     * <code>
     * <?php
     * $stor = new SaeStorage();
     * 
     * $attr = array('expires' => 'access plus 1 year');
     * $ret = $stor->setFileAttr("test", "test.txt", $attr);
     * if ($ret === false) {
     *      var_dump($stor->errno(), $stor->errmsg());
     * }
     * ?>
     * </code>
     *
     * @param string $domain 
     * @param string $filename  文件名
     * @param array $attr       文件属性。格式：array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * @return bool
     */
    public function setFileAttr( $domain, $filename, $attr = array() )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter domain,filename can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $parseAttr = $this->parseFileAttr($attr);
        if ($parseAttr == false) {
            $this->errMsg = 'the value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $container = $this->swift_conn->get_container($domain);
        $object = $container->get_object($filename);
        $object->metadata = $attr;
        $result = $object->sync_metadata();
        return $result;
    }

    /**
     * 设置Domain属性
     *
     * 目前支持的Domain属性
     *  - expires: 浏览器缓存超时，功能与Apache的Expires配置相同
     *  - allowReferer: 根据Referer防盗链
     *  - private: 是否私有Domain
     *  - 404Redirect: 404跳转页面，只能是本应用页面，或本应用Storage中文件。例如http://appname.sinaapp.com/404.html或http://appname-domain.stor.sinaapp.com/404.png
     *  - tag: Domain简介。格式：array('tag1', 'tag2')
     * <code>
     * <?php
     * // 缓存过期设置
     * $expires = 'ExpiresActive On
     * ExpiresDefault "access plus 30 days"
     * ExpiresByType text/html "access plus 1 month 15 days 2 hours"
     * ExpiresByType image/gif "modification plus 5 hours 3 minutes"
     * ExpiresByType image/jpg A2592000
     * ExpiresByType text/plain M604800
     * ';
     *
     * // 防盗链设置
     * $allowReferer = array();
     * $allowReferer['hosts'][] = '*.elmerzhang.com';       // 允许访问的来源域名，千万不要带 http://。支持通配符*和?
     * $allowReferer['hosts'][] = 'elmer.sinaapp.com';
     * $allowReferer['hosts'][] = '?.elmer.sinaapp.com';
     * $allowReferer['redirect'] = 'http://elmer.sinaapp.com/'; // 盗链时跳转到的地址，仅允许跳转到本APP的页面，且不可使用独立域名。如果不设置或者设置错误，则直接拒绝访问。
     * //$allowReferer = false;  // 如果要关闭一个Domain的防盗链功能，直接将allowReferer设置为false即可
     * 
     * $stor = new SaeStorage();
     * 
     * $attr = array('expires'=>$expires, 'allowReferer'=>$allowReferer);
     * $ret = $stor->setDomainAttr("test", $attr);
     * if ($ret === false) {
     *      var_dump($stor->errno(), $stor->errmsg());
     * }
     *
     * ?>
     * </code>
     *
     * @param string $domain 
     * @param array $attr       Domain属性。格式：array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * @return bool
     */
    public function setDomainAttr( $domain, $attr = array() )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter domain can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $parseAttr = $this->parseDomainAttr($attr);

        if ($parseAttr == false) {
            $this->errMsg = 'the value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $container = $this->swift_conn->get_container($domain);
        $container->metadata = $attr;
        $result = $container->sync_metadata();
        return($result);      
    }

    /**
     * 获取domain所占存储的大小
     *
     * @param string $domain 
     * @return int
     */
    public function getDomainCapacity( $domain )
    {
        $domain = trim($domain);
        if (empty($domain)) {
            $this->errMsg = 'The value of parameter \'$domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $info = $this->swift_conn->get_container($domain);
        $info_array = $this->std_class_object_to_array($info);
        return $info_array['bytes_used'];
    }

    /**
     * domain拼接
     * @param string $domain
     * @param bool $concat
     * @return string
     * @author Elmer Zhang
     * @ignore
     */
    protected function getDom($domain, $concat = true) {
        $this->setAppname();
        $domain = strtolower(trim($domain));

        if ($concat) {
            if( strpos($domain, '-') === false ) {
                $domain = $this->appName .'-'. $domain;
            }
        } else {
            if ( ( $pos = strpos($domain, '-') ) !== false ) {
                $domain = substr($domain, $pos + 1);
            }
        }
        return $domain;
    }


    /**
     * Format Filename.
     *
     * @param string $filename
     * @return string.
     */
    private function formatFilename($filename) 
    {
        $filename = trim($filename);
        $encodings = array( 'UTF-8', 'GBK', 'BIG5' );
        $charset = mb_detect_encoding( $filename , $encodings);
        if ( $charset !='UTF-8' ) {
            $filename = mb_convert_encoding( $filename, "UTF-8", $charset);
        }

        $filename = preg_replace('/\/\.\//', '/', $filename);
        $filename = ltrim($filename, '/');
        $filename = preg_replace('/^\.\//', '', $filename);
        while ( preg_match('/\/\//', $filename) ) {
            $filename = preg_replace('/\/\//', '/', $filename);
        }
        return $filename;
    }

    /**
     * @ignore
     */
    protected function parseDomainAttr($attr) 
    {
        $parseAttr = array();

        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }

        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case '404redirect':
                    if ( !empty($a) && is_string($a) ) {
                        $parseAttr['404Redirect'] = trim($a);
                    }
                    break;
                case 'private':
                    $parseAttr['private'] = $a ? true : false;
                    break;
                case 'expires':
                    $parseAttr['expires'] = $this->parseExpires($a);
                    break;
                case 'allowreferer':
                    if ( isset($a['hosts']) && is_array($a['hosts']) && !empty($a['hosts']) ) {
                        $parseAttr['allowReferer'] = array();
                        $parseAttr['allowReferer']['hosts'] = $a['hosts'];

                        if ( isset($a['redirect']) && is_string($a['redirect']) ) {
                            $parseAttr['allowReferer']['redirect'] = $a['redirect'];
                        }
                    } else {
                        $parseAttr['allowReferer']['host'] = false;
                    }
                    break;
                case 'tag':
                    if (is_array($a) && !empty($a)) {
                        $parseAttr['tag'] = array();
                        foreach ($a as $v) {
                            $v = trim($v);
                            if (is_string($v) && !empty($v)) {
                                $parseAttr['tag'][] = $v;
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return $parseAttr;
    }

    /**
     * @ignore
     */
    protected function parseFileAttr($attr) 
    {
        $parseAttr = array();

        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }

        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case 'expires':
                    $parseAttr['expires'] = $a;
                    break;
                case 'encoding':
                    $parseAttr['encoding'] = $a;
                    break;
                case 'type':
                    $parseAttr['type'] = $a;
                    break;
                case 'private':
                    $parseAttr['private'] = intval($a);
                    break;
                default:
                    break;
            }
        }

        return $parseAttr;
    }

    /**
     * @ignore
     */
    protected function parseExpires($expires) 
    {
        $expires = trim($expires);
        $expires_arr = array();
        $expires_arr['active'] = 1;

        $expires = preg_split("/(\n|\r\n)/", $expires);
        if (is_array($expires) && !empty($expires)) {
            foreach ($expires as $e) {
                $e = trim($e);
                if ( preg_match("/^ExpiresActive\s+(on|off)$/i", strtolower($e), $matches) ) { 
                    if ($matches[1] == "on") {
                        $expires_arr['active'] = 1;
                    } else {
                        $expires_arr['active'] = 0;
                    }
                } elseif ( preg_match("/^ExpiresDefault\s+(A\d+|M\d+|\"(.+)\")$/i", $e, $matches) ) { 
                    if (isset($matches[2])) {
                        $expires_arr['default'] = $matches[2];
                    } else {
                        $expires_arr['default'] = $matches[1];
                    }
                } elseif ( preg_match("/^ExpiresByType\s+(?P<type>.+)\s+(?P<expires>A\d+|M\d+|\"(.+)\")$/i", $e, $matches) ) { 
                    if (isset($matches[3])) {
                        $expires_arr['byType'][strtolower($matches['type'])] = $matches[3];
                    } else {
                        $expires_arr['byType'][strtolower($matches['type'])] = $matches[2];
                    }
                }
            }
        }

        return $expires_arr;
    }

    private function std_class_object_to_array($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        $array = array();
        foreach ($_array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? $this->std_class_object_to_array($value) : $value;
            $array[$key] = $value;
        }

        return $array;
    }

}