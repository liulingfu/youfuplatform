<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'QQ登录插件',
	'app_key'	=>	'QQAPI应用appid',
	'app_secret'	=>	'QQAPI应用appkey',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //腾讯API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //腾讯API应用的密码值
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
	if(ACTION_NAME=='install')
	{
		//更新字段
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `qq_id`  varchar(255) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Qq';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// QQ的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Qq_api implements api_login {
	
	private $api;
	//回调地址
	private $redirback;
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	    //回调地址要用urlencode编码
		$this->redirback=urlencode(get_domain().APP_ROOT."/api_callback.php?c=Qq");
	}
	
	public function get_api_url()
	{
		es_session::start();
		define('QQ_SCOPE',"get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo");
		
		$url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$this->api['config']['app_key']."&redirect_uri=".$this->redirback."&scope=".QQ_SCOPE;	
		$str = "<a href='".$url."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
	
	public function get_big_api_url()
	{
		es_session::start();
		define('QQ_SCOPE',"get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo");
		
		$url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$this->api['config']['app_key']."&redirect_uri=".$this->redirback."&scope=".QQ_SCOPE;
		$str = "<a href='".$url."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
		
	public function callback()
	{
		es_session::start();
		//获取token	
		$token=$this->getAccessToken();
		//获取openid
        $opendid=$this->getQqOpenid($token);
        //获取返回的user
         $arr=$this->getQqUserInfo($this->api['config']['app_key'],$token,$opendid);
		
		$msg['field'] = 'qq_id';
		$msg['id'] = $opendid;
		$msg['name'] = $arr["nickname"];
		es_session::set("api_user_info",$msg);
		if(!$msg['name'])app_redirect(url("index"));
		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where qq_id = '".$opendid."' and qq_id <> ''");	
		if($user_data)
		{
			require_once APP_ROOT_PATH."system/libs/user.php";
			auto_do_login_user($user_data['user_name'],$user_data['user_pwd'],$from_cookie = false);	
			es_session::delete("api_user_info");
			app_recirect_preview();
		}
		else
		{
			$this->create_user();
			app_redirect(url("shop","user#stepone"));
		}
		
	}
	
  //返回token
	function getAccessToken()
	{
		    $token_url="https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id="
		    .$this->api['config']['app_key']."&client_secret=".$this->api['config']['app_secret']."&code=".$_REQUEST["code"]."&redirect_uri=".$this->redirback;
	        $response = file_get_contents($token_url);
	        if (strpos($response, "callback") !== false)
	        {
	            $lpos = strpos($response, "(");
	            $rpos = strrpos($response, ")");
	            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
	            $msg = json_decode($response);
	            if (isset($msg->error))
	            {
	                echo "<h3>error:</h3>" . $msg->error;
	                echo "<h3>msg  :</h3>" . $msg->error_description;
	                exit;
	            }
	        }
	        $params = array();
	        parse_str($response, $params);
	        return $params["access_token"];
	}
     function getQqOpenid($access_token)
	{
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
	    $str  = file_get_contents($graph_url);
	    if (strpos($str, "callback") !== false)
	    {
	        $lpos = strpos($str, "(");
	        $rpos = strrpos($str, ")");
	        $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
	    }
	
	    $user = json_decode($str);
	    if (isset($user->error))
	    {
	        echo "<h3>error:</h3>" . $user->error;
	        echo "<h3>msg  :</h3>" . $user->error_description;
	        exit;
	    }
	    return $user->openid;
	}
	function getQqUserInfo($appid,$access_token,$openid)
	{
	    $get_user_info = "https://graph.qq.com/user/get_user_info?"
	        . "access_token=" . $access_token
	        . "&oauth_consumer_key=" . $appid
	        . "&openid=" . $openid
	        . "&format=json";
	
	    $info = file_get_contents($get_user_info);
	    $arr = json_decode($info, true);
	    return $arr;
	}

	public function get_title()
	{
		return 'QQ登录接口，需要php_curl扩展的支持';
	}
	public function create_user()
	{
		$s_api_user_info = es_session::get("api_user_info");
		$user_data['user_name'] = $s_api_user_info['name'];
		$user_data['user_pwd'] = md5(rand(100000,999999));
		$user_data['create_time'] = get_gmtime();
		$user_data['update_time'] = get_gmtime();
		$user_data['login_ip'] = get_client_ip();
		$user_data['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
		$user_data['is_effect'] = 1;
		$user_data['qq_id'] = $s_api_user_info['id'];
		$origin_username = $user_data['user_name'];
		$count = 0;
		do{
			if($count>0)
			$user_data['user_name'] = $origin_username.get_gmtime();
			if($user_data['qq_id'])
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT",'','SILENT');
			$rs = $GLOBALS['db']->insert_id();
			$count++;
		}while(intval($rs)==0&&$user_data['qq_id']);
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($rs));
		es_session::set("user_info",$user_info);
		es_session::delete("api_user_info");
	}	
}
?>