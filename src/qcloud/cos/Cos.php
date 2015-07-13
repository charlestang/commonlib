<?php

namespace charlestang\commonlib\qcloud\cos;

use \Httpful\Handlers\JsonHandler;
use \Httpful\Httpful;
use \Httpful\Mime;
use \Httpful\Request;
use \Httpful\Response;

defined('COS_APP_ID') or define('COS_APP_ID', 0);
defined('COS_SECRET_ID') or define('COS_SECRET_ID', 0);
defined('COS_SECRET_KEY') or define('COS_SECRET_KEY', 0);

Httpful::register(Mime::JSON, new JsonHandler(['decode_as_array' => true]));

/**
 * Cos对象，封装了腾讯云“对象存储服务”的相关API，提供了面向对象风格的文件系统操作接口。
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Cos
{

    const API_SCHEMA             = 'http://';
    const API_DOMAIN             = 'web.file.myqcloud.com';
    const API_BASE_URL           = '/files/v1';
    //签名类型
    const SIGN_TYPE_ONCE         = 1; //一次有效
    const SIGN_TYPE_MULT         = 2; //多次有效
    //签名有效时间
    const SIGN_EXPIRE            = 60; //60秒
    //列表顺序
    const LIST_ORDER_NORMAL      = 0;
    const LIST_ORDER_REVERSE     = 1;
    //列表的方式
    const LIST_PATTERN_BOTH      = 'eListBoth';
    const LIST_PATTERN_FILE_ONLY = 'eListFileOnly';
    const LIST_PATTERN_DIR_ONLY  = 'ListDirOnly';

    //以上常量定义

    private $appId;
    private $secretId;
    private $secretKey;

    /**
     * 构造函数
     *
     * @param string $appId
     * @param string $secretId
     * @param string $secretKey
     */
    public function __construct($appId = COS_APP_ID, $secretId = COS_SECRET_ID, $secretKey = COS_SECRET_KEY)
    {
        $this->appId     = $appId;
        $this->secretId  = $secretId;
        $this->secretKey = $secretKey;
    }

    /**
     * 在指定的Bucket中，创建一个目录
     * @param string $bucketName   bucket的名字
     * @param string $dirPath      要创建的目录的路径，要从根目录写起，以 / 结尾
     * @param string $attribute    自定义属性，可以是任意的一个字符串
     * @param int    $overwrite    如果目录已经存在，是否覆盖
     * @return array 返回数组，键 ctime 记录目录创建时间
     */
    public function createDirectory($bucketName, $dirPath, $attribute = '')
    {
        $path = $this->getAbsoluteDirPath($bucketName, $dirPath);
        $body = [
            'op'            => 'create',
        ];
        if (!empty($attribute)) {
            $body['biz_attr'] = $attribute;
        }
        $payload = json_encode($body);
        $request = Request::post($this->getBaseUrl() . $path, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    /**
     * 删除一个目录
     * @param string $bucketName
     * @param string $dirPath
     * @return boolean 成功删除返回true
     */
    public function deleteDirectory($bucketName, $dirPath)
    {
        $this->checkDirPath($dirPath);
        if ($dirPath == '/') {
            throw new Exception(Error::ERR_CANNOT_DELETE_ROOT, Error::msg(Error::ERR_CANNOT_DELETE_ROOT));
        }
        return $this->deleteNode($bucketName, $dirPath);
    }

    /**
     * 更新一个目录的自定义属性
     * @param string $bucketName
     * @param string $dirPath
     * @param string $bizAttr
     * @return
     */
    public function updateDirectory($bucketName, $dirPath, $bizAttr)
    {
        $this->checkDirPath($dirPath);
        return $this->updateNode($bucketName, $dirPath, $bizAttr);
    }

    /**
     * 底层方法，更新一个节点，可以更新一个目录，也可以更新一个文件的自定义属性
     * @param string $bucketName
     * @param string $nodePath
     * @param string $bizAttr
     * @return
     */
    public function updateNode($bucketName, $nodePath, $bizAttr)
    {
        $path    = $this->getAbsolutePath($bucketName, $nodePath);
        $body    = [
            'op'       => 'update',
            'biz_attr' => $bizAttr,
        ];
        $payload = json_encode($body);
        $request = Request::post($this->getBaseUrl() . $path, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path, self::SIGN_TYPE_ONCE));
        return $this->parseResponse($request->send());
    }

    /**
     * 实现了类似 Linux ls命令的方法
     * @param string $bucketName
     * @param string $nodePath     路径名，以 / 结尾
     * @param string $prefix      按照前缀过滤
     * @param string $offset      起始位置
     * @param int    $pageSize    每页数量
     * @param string $pattern     列表方式，缺省是文件夹和文件都显示
     * @param int    $direction   列表方向
     *
     * @return type
     */
    public function lsNode($bucketName, $nodePath, $prefix = '', $offset = '', $pageSize = 10,
        $pattern = self::LIST_PATTERN_BOTH, $direction = self::LIST_ORDER_NORMAL)
    {
        $path   = $this->getAbsolutePath($bucketName, $nodePath);
        $apiUrl = $this->getBaseUrl() . $path;
        if (!empty($prefix)) { //如果按照前缀搜索的话
            $apiUrl .= $prefix;
        }
        $query = [
            'op'      => 'list',
            'num'     => $pageSize,
            'pattern' => $pattern,
            'order'   => $direction,
        ];
        if (!empty($offset)) {
            $query['offset'] = $offset;
        }

        $apiUrl .= '?' . http_build_query($query);

        $request = Request::get($apiUrl, 'json')->addHeader('authorization', $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    /**
     * 删除节点
     *
     * 目录和文件都算是节点的一种，该方法可以用于删除目录或者文件。
     * 该方法被封装成更加高级的形式，如：deleteDirectory 和 deleteFile
     * @param string $bucketName
     * @param string $nodePath
     * @return boolean 删除成功返回true，删除失败则抛出异常
     */
    public function deleteNode($bucketName, $nodePath)
    {
        $path    = $this->getAbsolutePath($bucketName, $nodePath);
        $body    = [
            'op' => 'delete',
        ];
        $payload = json_encode($body);
        $request = Request::post($this->getBaseUrl() . $path, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path, self::SIGN_TYPE_ONCE));
        return $this->parseResponse($request->send());
    }

    public function listDirectory($bucketName, $dirPath, $prefix = '', $offset = '', $pageSize = 10,
        $direction = self::LIST_ORDER_NORMAL)
    {
        $this->checkDirPath($dirPath);
        return $this->lsNode($bucketName, $dirPath, $prefix, $offset, $pageSize, self::LIST_PATTERN_DIR_ONLY, $direction);
    }

    public function listFile($bucketName, $dirPath, $prefix = '', $offset = '', $pageSize = 10,
        $direction = self::LIST_ORDER_NORMAL)
    {
        $this->checkFilePath($filePath);
        return $this->lsNode($bucketName, $dirPath, $prefix, $offset, $pageSize, self::LIST_PATTERN_FILE_ONLY, $direction);
    }

    public function directoryExists($bucketName, $dirPath)
    {
        $exists = true;
        try {
            $this->listDirectory($bucketName, $dirPath);
        } catch (Exception $ex) {
            if ($ex->getCode() == Error::ERR_INDEX_NOT_FOUND) {
                $exists = false;
            } else {
                throw $ex;
            }
        }
        return $exists;
    }

    /**
     * 上传一个完整的文件
     * @param string $bucketName bucket的名字
     * @param string $filePath   云端文件的绝对路径
     * @param string $filename   文件在本地服务器的绝对路径
     * @param string $attribute  自定义文件属性
     * @return array
     */
    public function uploadFile($bucketName, $filePath, $filename, $attribute = '')
    {
        $path = $this->getAbsoluteFilePath($bucketName, $filePath);

        $fd       = fopen($filename, 'rb');
        $contents = fread($fd, filesize($filename));
        fclose($fd);

        $body = [
            'op'          => 'upload',
            'filecontent' => $contents,
            'sha'         => sha1_file($filename),
        ];
        if (!empty($attribute)) {
            $body['biz_attr'] = $attribute;
        }
        $request = Request::post($this->getBaseUrl() . $path, $body, Mime::UPLOAD)->expects('json')->addHeader('authorization', $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    /**
     * 删除文件
     * @param string $bucketName
     * @param string $filePath
     * @return boolean
     */
    public function deleteFile($bucketName, $filePath)
    {
        $this->checkFilePath($filePath);
        return $this->deleteNode($bucketName, $filePath);
    }

    /**
     * 更新文件的附加属性
     * @param string $bucketName
     * @param string $filePath
     * @param string $bizAttr
     * @return boolean
     */
    public function updateFileAttribute($bucketName, $filePath, $bizAttr)
    {
        $this->checkFilePath($filePath);
        return $this->updateNode($bucketName, $filePath, $bizAttr);
    }

    /**
     * 返回值的解析
     * @param Response $response Httpful包里，对返回值封装的对象
     * @return array/boolean  如果返回的是数据，就是一个关联数组，对应返回json的data属性，否则返回true
     * @throws Exception 如果返回值的code非0，则抛出异常
     */
    protected function parseResponse($response)
    {
        if ($response->hasErrors()) {
            if ($response->hasBody()) {
                throw new Exception($response->body['code'], $response->body['message']);
            } else {
                throw new Exception($response->code, 'HTTP Error.');
            }
        }
        if ($response->body['code'] == 0) {
            return isset($response->body['data']) ? $response->body['data'] : true;
        } else {
            throw new Exception($response->body['code'], $response->body['message']);
        }
    }

    /**
     * 生成授权签名
     * @param string $bucketName
     * @param string $path
     * @param int $type
     * @param int $expire
     *
     * @return string 签名字符串
     */
    public function getAuthorizationSign($bucketName, $path, $type = self::SIGN_TYPE_MULT, $expire = self::SIGN_EXPIRE)
    {

        if ($type == self::SIGN_TYPE_ONCE) { //一次有效的签名，$expire 必须填 0
            $expire = 0;
        } else {
            $expire = time() + $expire;
        }
        if ($type == self::SIGN_TYPE_MULT) { //多次有效的签名，$path 填空
            $path = '';
        }
        $params = [
            'a' => $this->appId,
            'k' => $this->secretId,
            'e' => $expire,
            't' => time(),
            'r' => rand(1000000000, 9999999999),
            'f' => $path,
            'b' => $bucketName
        ];

        $str = $this->buildString($params);

        return $this->encode($str, $this->secretKey);
    }

    protected function getBaseUrl()
    {
        return self::API_SCHEMA . self::API_DOMAIN . self::API_BASE_URL;
    }

    protected function getAbsolutePath($bucketName, $nodePath)
    {
        $this->checkBucketName($bucketName);
        return DIRECTORY_SEPARATOR . $this->appId . DIRECTORY_SEPARATOR . $bucketName . $nodePath;
    }

    protected function getAbsoluteDirPath($bucketName, $dirPath)
    {
        $this->checkDirPath($dirPath);
        return $this->getAbsolutePath($bucketName, $dirPath);
    }

    protected function getAbsoluteFilePath($bucketName, $filePath)
    {
        $this->checkFilePath($filePath);
        return $this->getAbsolutePath($bucketName, $filePath);
    }

    protected function buildString($arr)
    {
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        return rtrim($str, '&');
    }

    protected function encode($string, $key)
    {
        return base64_encode(hash_hmac('sha1', $string, $key, true) . $string);
    }

    protected function checkBucketName($bucketName)
    {
        if (!preg_match('/^[_0-9a-z]+$/i', $bucketName)) {
            throw new Exception(Error::ERR_INVALID_PARAM, Error::msg(Error::ERR_INVALID_PARAM));
        }
    }

    protected function checkDirPath($dirPath)
    {
        if (!preg_match('#(^\/$)|(^\/(.+)\/$)#i', $dirPath)) {
            throw new Exception(Error::ERR_INVALID_PARAM, Error::msg(Error::ERR_INVALID_PARAM));
        }
    }

    protected function checkFilePath($filePath)
    {
        if (!preg_match('#^\/(.*)[^/]$#i', $filePath)) {
            throw new Exception(Error::ERR_INVALID_PARAM, Error::msg(Error::ERR_INVALID_PARAM));
        }
    }

}
