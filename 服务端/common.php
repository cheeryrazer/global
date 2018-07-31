<?php
error_reporting(E_ERROR);
define('APP_ID', 'wxdc90e92xxxx');
define('APP_SECRET', '68e20f5f256xxxxxxxxxxxxxx');
define('MCH_ID', '141xxxxx');
define('MCH_SIGN_KEY', 'lsdjflnfgsmvlskuf32xxxxxxx');

function init(){
    if (!function_exists('curl_file_create')) {
        function curl_file_create($filename, $mimetype = '', $postname = '') {
            return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
        }
    }

}

init();

/**
 * 模拟post/get进行url请求
 *
 * @since 2016-06-03 Fri 09:37:53
 * @author PHPJungle
 * @param string $url
 * @param mix $param [array or string]
 * @param bool $is_post [default:post ,false:get]
 * @return string
 * @abstract <pre>
 *      方法说明:为了保证和以前使用方法兼容，故将$is_post默认值为true,如果需要get请求，将其置为false即可
 */
function request_put($url = '', $param = '')
{
    $url = trim($url);
    if (empty($url)) {
        return false;
    }
    $queryStr = '';
    if(is_array($param)){
        foreach($param as $k=>$v){
            $v = trim($v);
            if('' === $v)
                unset($param[$k]);
        }
        $queryStr = http_build_query($param); # 代码优化，减少网络开支
    }else{
        $queryStr = trim($param);
    }
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_TIMEOUT,8); //执行超时时间 秒
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,8); //链接超时时间 秒


    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($queryStr)));# put请求必须要指定长度
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $queryStr);

    curl_setopt($ch, CURLOPT_URL,$url);

    $data = curl_exec($ch);//运行curl
    $info = curl_getinfo($ch);
    $info['xml'] = $data;
    file_put_contents('log_curl.txt', json_encode($info));
    curl_close($ch);
    return $data;
}

/**
 * 模拟post/get进行url请求
 *
 * @since 2016-06-03 Fri 09:37:53
 * @author PHPJungle
 * @param string $url
 * @param mix $param [array or string]
 * @param bool $is_post [default:post ,false:get]
 * @return string
 * @abstract <pre>
 *      方法说明:为了保证和以前使用方法兼容，故将$is_post默认值为true,如果需要get请求，将其置为false即可
 */
function request_post($url = '', $param = '' , $is_post = true)
{
    $url = trim($url);
    if (empty($url)) {
        return false;
    }
    $queryStr = '';
    if(is_array($param)){
        if( !$_FILES or ! IS_POST_ARRAY){
            foreach($param as $k=>$v){
                $v = trim($v);
                if('' === $v)
                    unset($param[$k]);
            }
            $queryStr = http_build_query($param); # 代码优化，减少网络开支
        }else{
            if($_FILES){
                # 创建CURLFile对象
                foreach($_FILES as $form_name =>$fileInfo){
                    $filePath = $fileInfo['tmp_name'];
                    $fileName = $fileInfo['name'];
                    $fileType = $fileInfo['type'];

                    !file_exists($filePath)  or $param[$form_name] = curl_file_create($filePath,$fileType,$fileName);
                }
            }

            $queryStr = $param;
        }

    }else{
        $queryStr = trim($param);
    }
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_TIMEOUT,8); //执行超时时间 秒
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,8); //链接超时时间 秒

    if($is_post){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryStr);
    }else{
        empty($queryStr) or $url .= '?' . $queryStr;
    }
    curl_setopt($ch, CURLOPT_URL,$url);


    $data = curl_exec($ch);//运行curl
    
    $info = curl_getinfo($ch);
    $info['xml'] = $data;
    file_put_contents('log_curl.txt', json_encode($info));
    curl_close($ch);
    return $data;
}


/**
 * 格式化参数格式化成url参数
 */
function toUrlParams($data)
{
    $buff = "";
    foreach ($data as $k => $v)
    {
        if($k != "sign" && $v != "" && !is_array($v)){
            $buff .= $k . "=" . $v . "&";
        }
    }

    $buff = trim($buff, "&");
    return $buff;
}

function getSign($data){
    //签名步骤一：按字典序排序参数
    ksort($data);
    $string = toUrlParams($data);
    //签名步骤二：在string后加入KEY
    $string = $string . "&key=".MCH_SIGN_KEY;
    //签名步骤三：MD5加密
    $string = md5($string);
    //签名步骤四：所有字符转为大写
    $result = strtoupper($string);
    return $result;
}