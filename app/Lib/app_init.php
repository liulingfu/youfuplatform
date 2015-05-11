<?php
require_once 'common.php';
filter_injection($_REQUEST);

if(!file_exists(APP_ROOT_PATH.'public/runtime/app/'))
{
	mkdir(APP_ROOT_PATH.'public/runtime/app/',0777);
}

//处理城市
//$city_count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
$city_name = trim(addslashes($_REQUEST['city'])); 
$deal_city = '';
if($city_name)
{
	if($city_name=='all')
	$deal_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where pid = 0");
	else
	$deal_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where uname='".$city_name."' and is_effect = 1 and is_delete = 0");
}
if(!$deal_city)
$deal_city = get_current_deal_city();
es_cookie::set("deal_city",$deal_city['id'],24*3600*30);


$GLOBALS['tmpl']->assign("deal_city",$deal_city);
//输出城市
$deal_city_list = get_deal_citys();
$GLOBALS['tmpl']->assign("deal_city_list",$deal_city_list['ls']);
$GLOBALS['tmpl']->assign("deal_city_list_zm",$deal_city_list['zm']);

$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
$GLOBALS['tmpl']->assign("deal_city",$deal_city);
$GLOBALS['tmpl']->assign("show_city_title",true);

if(count($deal_city_list['ls'])>1)
{
	$GLOBALS['tmpl']->assign("city_title",$deal_city['name']);
}

//输出根路径
$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);

//输出语言包的js
if(!file_exists(get_real_path()."public/runtime/app/lang.js"))
{			
		$str = "var LANG = {";
		foreach($lang as $k=>$lang_row)
		{
			$str .= "\"".$k."\":\"".str_replace("nbr","\\n",addslashes($lang_row))."\",";
		}
		$str = substr($str,0,-1);
		$str .="};";
		@file_put_contents(get_real_path()."public/runtime/app/lang.js",$str);
}

//会员自动登录及输出
$cookie_uname = es_cookie::get("user_name")?es_cookie::get("user_name"):'';
$cookie_upwd = es_cookie::get("user_pwd")?es_cookie::get("user_pwd"):'';
if($cookie_uname!=''&&$cookie_upwd!=''&&!es_session::get("user_info"))
{
	$cookie_uname = addslashes(trim(htmlspecialchars($cookie_uname)));
	$cookie_upwd = addslashes(trim(htmlspecialchars($cookie_upwd)));
	require_once APP_ROOT_PATH."system/libs/user.php";
	auto_do_login_user($cookie_uname,$cookie_upwd);
}

$user_info = es_session::get('user_info');
if($user_info)
{
$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where is_delete = 0 and is_effect = 1 and id = ".intval($user_info['id']));	
es_session::set('user_info',$user_info);
}

if($user_info)
{	
	$GLOBALS['tmpl']->assign("user_info",$user_info);
	if(check_ipop_limit(get_client_ip(),"auto_send_msg",30,$user_info['id']))  //自动检测收发件
	{
		//有会员登录状态时，自动创建消息
		$msg_systems = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."msg_system where (end_time = 0 or end_time > ".get_gmtime().") and user_ids = '' or user_ids like '%".$user_info['id']."|%'");
		foreach($msg_systems as $msg)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."msg_box where to_user_id = ".$user_info['id']." and system_msg_id = ".$msg['id'])==0)
			{
				send_user_msg($msg['title'],$msg['content'],0,$user_info['id'],$msg['create_time'],$msg['id'],true);
			}		
		}
	}
}

//保存返利的cookie
if($_REQUEST['r'])
{
	$rid = intval(base64_decode($_REQUEST['r']));
	$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($rid)));
	es_cookie::set("REFERRAL_USER",intval($ref_uid));
}
else
{
	//获取存在的推荐人ID
	if(intval(es_cookie::get("REFERRAL_USER"))>0)
	$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval(es_cookie::get("REFERRAL_USER"))));
}


//保存来路
if(!es_cookie::get("referer_url"))
{	
	if(!preg_match("/".urlencode(get_domain().APP_ROOT)."/",urlencode($_SERVER["HTTP_REFERER"])))
	es_cookie::set("referer_url",$_SERVER["HTTP_REFERER"]);
}
$referer = htmlspecialchars(trim(addslashes(es_cookie::get("referer_url"))));


//自动刷新购物车与会员的验证资料过期操作
if(check_ipop_limit(get_client_ip(),"auto_refresh_data",30,intval($user_info['id'])))
{
	//每小时清空一次购物车
	$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where ".get_gmtime()." - update_time > 3600");
	//清空会员验证码
	$GLOBALS['db']->query("update ".DB_PREFIX."user set lottery_mobile = '',lottery_verify = '', verify_create_time = '' where verify_create_time > 0 and lottery_verify <> '' and ".get_gmtime()." - verify_create_time > 1800");
}
//判断用户是否已经设置过资料
if($GLOBALS["user_info"])
{
	if($GLOBALS["user_info"]['step']==0&&strtolower($_REQUEST['ctl'])!='user'&&strtolower($_REQUEST['ctl'])!='avatar'&&strtolower($_REQUEST['ctl'])!='uc_account'&&strtolower($_REQUEST['ctl'])!='ajax')
	{
		$redirect = url("shop","user#stepone");
		app_redirect($redirect);
	}
}

?>