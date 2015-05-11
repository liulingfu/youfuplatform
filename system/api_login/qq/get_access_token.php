<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.2
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */

require_once("utils.php");

/**
 * @brief 获取access_token。请求需经过URL编码，编码时请遵循 RFC 1738
 *
 * @param $appid
 * @param $appkey
 * @param $request_token
 * @param $request_token_secret
 * @param $vericode
 *
 * @return 返回字符串格式为：oauth_token=xxx&oauth_token_secret=xxx&openid=xxx&oauth_signature=xxx&oauth_vericode=xxx&timestamp=xxx
 */

function get_access_token($appid, $appkey, $request_token, $request_token_secret, $vericode)
{
    //请求具有Qzone访问权限的access_token的接口地址, 不要更改!!
    $url    = "http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token?";
   
    //生成oauth_signature签名值。签名值生成方法详见（http://wiki.opensns.qq.com/wiki/【QQ登录】签名参数oauth_signature的说明）
    //（1） 构造生成签名值的源串（HTTP请求方式 & urlencode(uri) & urlencode(a=x&b=y&...)）
	$sigstr = "GET"."&".rawurlencode("http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token")."&";

    //必要参数，不要随便更改!!
    $params = array();
    $params["oauth_version"]          = "1.0";
    $params["oauth_signature_method"] = "HMAC-SHA1";
    $params["oauth_timestamp"]        = time();
    $params["oauth_nonce"]            = mt_rand();
    $params["oauth_consumer_key"]     = $appid;
    $params["oauth_token"]            = $request_token;
    $params["oauth_vericode"]         = $vericode;

    //对参数按照字母升序做序列化
    $normalized_str = get_normalized_string($params);
    $sigstr        .= rawurlencode($normalized_str);

    //echo "sigstr = $sigstr";

	//（2）构造密钥
    $key = $appkey."&".$request_token_secret;

	//（3）生成oauth_signature签名值。这里需要确保PHP版本支持hash_hmac函数
    $signature = get_signature($sigstr, $key);
    
	
	//构造请求url
    $url      .= $normalized_str."&"."oauth_signature=".rawurlencode($signature);

    return file_get_contents($url);
}


function get_qquser_info($appid, $appkey, $access_token, $access_token_secret, $openid)
{
	//获取用户信息的接口地址, 不要更改!!
    $url    = "http://openapi.qzone.qq.com/user/get_user_info";
    $info   = do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid);
    $arr = array();
    $arr = json_decode($info, true);

    return $arr;
}
?>
