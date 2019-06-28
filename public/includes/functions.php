<?php

/**
 * 获取get 请求参数
 *
 * @param string     $key     参数名称
 * @param mixed|null $default 默认值
 *
 * @return null|mixed
 */
function get($key, $default = null)
{
    if ($key === null) {
        return $_GET;
    }

    return get_value($_GET, $key, $default);
}

/**
 * 获取 post 请求参数
 *
 * @param string     $key     参数名称
 * @param mixed|null $default 默认值
 *
 * @return null|mixed
 */
function post($key = null, $default = null)
{
    if ($key === null) {
        return $_POST;
    }

    return get_value($_POST, $key, $default);
}

/**
 * 获取目录下的文件信息
 *
 * @param  string $path 目录路径
 *
 * @return array 文件信息
 */
function get_path_file_name($path)
{
    $arrReturn = [];
    if (is_dir($path)) {
        $resource = opendir($path);
        if ($resource) {
            while (!!($file = readdir($resource))) {
                if (is_file($path . '/' . $file)) {
                    $arrReturn[] = pathinfo($path . '/' . $file, PATHINFO_FILENAME);
                }
            }

            closedir($resource);
        }
    }

    return $arrReturn;
}

/**
 * PHP获取字符串中英文混合长度
 *
 * @param   string $str     字符串
 * @param   string $charset 编码( 默认为UTF-8 )
 *
 * @return  int 返回长度，1中文=1位，2英文=1位
 */
function str_length($str, $charset = 'utf-8')
{
    // 字符编码的转换 iconv() 将utf-8的字符编码转换为gb2312的编码
    if ($charset == 'utf-8') {
        $str = iconv('utf-8', 'gb2312', $str);
    }

    $num   = strlen($str);
    $cnNum = 0;

    for ($i = 0; $i < $num; $i++) {
        if (ord(substr($str, $i + 1, 1)) > 127) {
            $cnNum++;
            $i++;
        }
    }

    $enNum  = $num - $cnNum * 2;
    $number = $enNum / 2 + $cnNum;
    return ceil($number);
}

/**
 * PHP获取随机字符串验证码
 *
 * @param   int $length 验证码长度( 默认为四位 )
 *
 * @return  string    返回一个包含 A-Z a-z 0-9 的随机字符串
 */
function get_code($length = 4)
{
    $pass = '';
    // 获取需要长度的字符串
    for ($i = 0; $i < $length; $i++) {
        $intRand = mt_rand(1, 10);
        if ($intRand <= 3) // A-Z 概率为 30 %
        {
            $pass .= chr(mt_rand(65, 90));
        } else if ($intRand <= 7) // a-z 概率为 40%
        {
            $pass .= chr(mt_rand(97, 122));
        } else // 0-9 概率为 30%
        {
            $pass .= chr(mt_rand(48, 57));
        }

    }

    return $pass;
}

/**
 * 上传文件处理
 *
 * @param string $file    上传文件名称
 * @param string $dirName 保存目录
 * @param array  $params  上传其他配置参数
 *                        ```
 *                        [
 *                        size       => 10000000,                      // 允许上传文件大小kb
 *                        allow_type => ['jpg', 'jpeg', 'gif', 'png'], // 允许类型
 *                        prefix     => '',                            // 文件前缀名称
 *                        ]
 *                        ```
 *
 * @return array
 */
function file_upload($file, $dirName = 'uploads', array $params = [])
{
    $uploadSize = get_value($params, 'size', 10000000);
    $allowType  = get_value($params, 'allow_type', ['jpg', 'jpeg', 'gif', 'png']);
    $prefixName = get_value($params, 'prefix', '');

    // 1、判断是否已经上传
    if (empty($_FILES[$file]) || empty($_FILES[$file]['name'])) {
        return [false, '没有上传文件', 1];
    }

    // 3、判断上传错误信息
    if ($_FILES[$file]['error'] > 0) {
        return [false, '上传出现错误', $_FILES[$file]['error']];
    }

    // 4、判断上传文件大小
    if ($_FILES[$file]['size'] > $uploadSize) {
        return [false, '上传文件过大', 8];
    }

    // 5、判断上传文件类型
    $extension = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION); // 获取文件后缀名
    $extension = strtolower($extension); // 后缀名转小写
    if (!in_array($extension, $allowType)) {
        return [false, '上传文件类型错误', 9];
    }

    // 6、判断文件是否通过HTTP POST 上传的
    if (!is_uploaded_file($_FILES[$file]['tmp_name'])) {
        return [false, '上传方式错误', 10];
    }

    // 7、创建上传文件目录创建目录
    $dir = trim($dirName, '/') . '/' . date('Ymd') . '/';
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }

    if (!file_exists($dir)) {
        return [false, '创建上传目录失败', 11];
    }

    // 文件名称
    $randName = empty($prefixName) ? 'tmp_' . uniqid() : $prefixName . '_' . pathinfo($_FILES[$file]['name'], PATHINFO_FILENAME);
    $randName .= '.' . $extension;
    $filePath = $dir . $randName; // 上传文件移动后的完整文件路径

    // 8、移动上传文件到指定上传文件目录
    if (!move_uploaded_file($_FILES[$file]['tmp_name'], $filePath)) {
        return [false, '移动上传文件失败', 11];
    }

    return [true, '上传成功', array_merge($_FILES[$file], [
        'file_name' => $randName,
        'file_path' => $filePath,
        'extension' => $extension,
    ])];
}

/**
 * 获取唯一码
 *
 * @param  string $prefix 默认空
 *
 * @return string 返回前缀加 + 年月日时分秒 + 微妙数 + 6位随机码
 */
function get_unique_code($prefix = '')
{
    return $prefix . date('YmdHis') . substr(microtime(), 2, 6) . sprintf('%06d', mt_rand(0, 999999), STR_PAD_LEFT);
}

/**
 * 随机生成16位字符串
 *
 * @param int $length
 *
 * @return string 生成的字符串
 */
function get_random_str($length = 16)
{
    return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwzxyABCDEFGHIJKLMNOPQRSTUVWZXY'), 0, $length);
}

/**
 * [getSign description]
 *
 * @param  array  $params [description]
 * @param  string $key    [description]
 *
 * @return [type]         [description]
 */
function get_sign($params = [], $key = 'NtuGEpZiKjfS91Fy')
{
    ksort($params);
    $str_params = http_build_query($params);
    return md5($str_params . $key);
}

/**
 * [validateSign description]
 *
 * @param  [type] $params [description]
 * @param  [type] $sign   [description]
 *
 * @return [type]         [description]
 */
function validate_sign($params, $sign)
{
    if (isset($params['sign'])) {
        unset($params['sign']);
    }

    return get_sign($params) === $sign;
}

/**
 * 输出json 信息
 *
 * @param      $mixed
 * @param bool $isExit
 * @param int  $options
 */
function echo_json($mixed, $isExit = true, $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
{
    header('content-type:application/json; charset=UTF-8');
    echo json_encode($mixed, $options);
    $isExit && exit();
}

/**
 * 返回错误信息
 *
 * @param  integer $code 错误码,默认401
 * @param  string  $msg  错误提示信息
 *
 * @return void
 */
function error($code = 401, $msg = 'fail')
{
    echo_json(['code' => $code, 'msg' => $msg]);
}

/**
 * 返回正确数据
 *
 * @param  array|mixed $data 返回data 数据信息
 * @param  string      $msg  提示信息
 *
 * @return void
 */
function success($data, $msg = 'success')
{
    echo_json(['code' => 0, 'msg' => $msg, 'data' => $data]);
}

if (!function_exists('get_value')) {
    /**
     * 获取数组的值
     *
     * @param      $array
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    function get_value($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = get_value($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = get_value($array, substr($key, 0, $pos), $default);
            $key   = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }
}

/**
 * 获取签名
 *
 * @param string $str            需要加密的字符串
 * @param string $rsaPrivateFile 私钥文件地址
 *
 * @return string
 */
function get_rsa_sign($str, $rsaPrivateFile)
{
    $privateKey = file_get_contents($rsaPrivateFile);
    $resource   = openssl_get_privatekey($privateKey);
    openssl_sign($str, $sign, $resource, OPENSSL_ALGO_SHA256);
    openssl_free_key($resource);
    return base64_encode($sign);
}

/**
 * 验证签名
 *
 * @param string $sign          需要验证的签名字符串
 * @param string $str           参与加密的字符串
 * @param string $rsaPublicFile 公钥文件地址
 *
 * @return bool
 */
function check_rsa_sign($sign, $str, $rsaPublicFile)
{
    $public_key = file_get_contents($rsaPublicFile);
    $resource   = openssl_get_publickey($public_key);
    $result     = (bool)openssl_verify($str, $sign, $resource, OPENSSL_ALGO_SHA256);
    openssl_free_key($resource);
    return $result;
}

/**
 * 判断是否为空 0 值不算
 *
 * @param mixed $value 判断的值
 *
 * @return boolean 是空返回 true
 */
function is_empty($value)
{
    return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
}

/**
 * 过滤数组中的无效值
 *
 * @param $array
 *
 * @return array
 */
function filter_array($array)
{
    if (!is_array($array)) {
        return $array;
    }

    foreach ($array as $key => $value) {
        if (is_empty($value)) {
            unset($array[$key]);
        }
    }

    return $array;
}

if (!function_exists('studly_case')) {
    /**
     * 大驼峰法
     *
     * @param string $str
     *
     * @return mixed
     */
    function studly_case($str)
    {
        $str = str_replace(['-', '_'], ' ', $str);
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }
}
