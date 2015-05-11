<?php

if(!defined('APP_ROOT_PATH')) 
define('APP_ROOT_PATH', str_replace('system/api_login/qq/redirect_to_login.php', '', str_replace('\\', '/', __FILE__)));
require_once APP_ROOT_PATH.'system/utils/es_session.php';

/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.2
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */
es_session::start();
require_once("get_request_token.php");
$appid = $_REQUEST['appid'];
$appkey = $_REQUEST['appkey'];
$callback = urldecode($_REQUEST['callback']);
/**
 * @brief 跳转到QQ登录页面.请求需经过URL编码，编码时请遵循 RFC 1738
 *
 * @param $appid
 * @param $appkey
 * @param $callback
 *
 * @return 返回字符串格式为：oauth_token=xxx&openid=xxx&oauth_signature=xxx&timestamp=xxx&oauth_vericode=xxx
 */
function redirect_to_login($appid, $appkey, $callback)
{
    //跳转到QQ登录页的接口地址, 不要更改!!
    $redirect = "http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize?oauth_consumer_key=$appid&";

    //调用get_request_token接口获取未授权的临时token
    $result = array();
    $request_token = get_request_token($appid, $appkey);
    parse_str($request_token, $result);

    //request token, request token secret 需要保存起来
    //在demo演示中，直接保存在全局变量中.
    //正式网站运营环境中，我们强烈建议你将这两个值保存在MySQL或者其他永久的存储中以便于后续使用
	//尤其是在网站不止一台服务器的情况下，两次请求的sessoin信息可能不会保存再同一台服务器导致访问出错
    es_session::set("token",$result["oauth_token"]);
    es_session::set("secret",$result["oauth_token_secret"]);


    if ($result["oauth_token"] == "")
    {
        //示例代码中没有对错误情况进行处理。真实情况下网站需要自己处理错误情况
        exit;
    }

    ////构造请求URL
    $redirect .= "oauth_token=".$result["oauth_token"]."&oauth_callback=".rawurlencode($callback);
    header("Location:$redirect");
}

//redirect_to_login接口调用示例(当用户点击QQ登录按钮时，应该调用该接口以引导用户到QQ登录页面)
redirect_to_login($appid,$appkey,$callback);
?>
