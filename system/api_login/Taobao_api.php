<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'支付宝快捷登录',
	'app_key'	=>	'合作者身份ID',
	'app_secret'	=>	'安全检验码',
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
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `taobao_id`  varchar(255) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Taobao';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// QQ的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Taobao_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	}
	
	public function get_api_url()
	{
		es_session::start();
		es_session::set("taobao_app_key",$this->api['config']['app_key']);
		es_session::set("taobao_app_secret",$this->api['config']['app_secret']);
		es_session::set("taobao_callback",get_domain().APP_ROOT."/api_callback.php?c=Taobao");
		
		$url = get_domain().APP_ROOT."/system/api_login/taobao/redirect.php";	
		$str = "<a href='".$url."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
	
	public function get_big_api_url()
	{
		es_session::start();
		es_session::set("taobao_app_key",$this->api['config']['app_key']);
		es_session::set("taobao_app_secret",$this->api['config']['app_secret']);
		es_session::set("taobao_callback",get_domain().APP_ROOT."/api_callback.php?c=Taobao");
		
		$url = get_domain().APP_ROOT."/system/api_login/taobao/redirect.php";	
		$str = "<a href='".$url."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}	
	public function callback()
	{
		es_session::start();	
		$aliapy_config['partner']		= $this->api['config']['app_key'];
		$aliapy_config['key']			=  $this->api['config']['app_secret'];
		$aliapy_config['return_url']   = get_domain().APP_ROOT."/api_callback.php?c=Taobao";
		$aliapy_config['sign_type']    = 'MD5';
		$aliapy_config['input_charset']      = 'utf-8';
		$aliapy_config['transport']    = 'http';
		require_once APP_ROOT_PATH."system/api_login/taobao/alipay_notify.class.php";
		
		unset($_GET['c']);
		
		$alipayNotify = new AlipayNotify($aliapy_config);
		$verify_result = $alipayNotify->verifyReturn();
		
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代码
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		    $user_id	= $_GET['user_id'];	//支付宝用户id
		    $token		= $_GET['token'];	//授权令牌
			$real_name=$_GET['real_name'];
		
			//执行商户的业务程序
			$msg['id'] = $user_id;
			$msg['name'] = $real_name;			
			$msg['field'] = 'taobao_id';
			es_session::set("api_user_info",$msg);
			if(!$msg['name'])app_redirect(url("index"));
	
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where taobao_id = '".$msg['id']."' and taobao_id <> ''");	
			if($user_data)
			{
					require_once APP_ROOT_PATH."system/libs/user.php";
					auto_do_login_user($user_data['user_name'],$user_data['user_pwd'],$from_cookie = false);
					es_session::delete("api_user_info");
					app_recirect_preview();
			}
			else{
			   $this->create_user();
	           app_redirect(url("shop","user#stepone"));
			}

			
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
		    //验证失败
		    //如要调试，请看alipay_notify.php页面的return_verify函数，比对sign和mysign的值是否相等，或者检查$veryfy_result有没有返回true
		   echo "验证失败";
		}
		
	}
	
	public function get_title()
	{
		return '支付宝快捷登录';
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
		$user_data['taobao_id'] = $s_api_user_info['id'];
		$origin_username = $user_data['user_name'];
		$count = 0;
		do{
			if($count>0)
			$user_data['user_name'] = $origin_username.get_gmtime();
			if($user_data['taobao_id'])
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT",'','SILENT');
			$rs = $GLOBALS['db']->insert_id();
			$count++;
		}while(intval($rs)==0&&$user_data['taobao_id']);
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($rs));
		es_session::set("user_info",$user_info);
		es_session::delete("api_user_info");
	}	
}
?>