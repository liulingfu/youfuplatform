<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH.'app/Lib/message.php';

class msgModule extends ShopBaseModule
{
	public function index()
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.trim($_REQUEST['act']).$GLOBALS['deal_city']['id']);	
		if($GLOBALS['tmpl']->is_cached("msg_index.html",$cache_id))
		{
			
		}	
		$GLOBALS['tmpl']->display("msg_index.html",$cache_id);
	}
	
	//不可接收购买评论
	public function add()
	{				
		$user_info = $GLOBALS['user_info'];
		$ajax = intval($_REQUEST['ajax']);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax);
			}
		}
		
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		
		$rel_table = addslashes(trim($_REQUEST['rel_table']));
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."' and type_name <> 'supplier'");
		if(!$message_type)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE'],$ajax);
		}		
		$message_group = addslashes(trim($_REQUEST['message_group']));	
		//添加留言
		$message['title'] = $_REQUEST['title']?htmlspecialchars(addslashes($_REQUEST['title'])):htmlspecialchars(addslashes($_REQUEST['content']));
		$message['content'] = htmlspecialchars(addslashes(valid_str($_REQUEST['content'])));
		$message['title'] = valid_str($message['title']);
		if($message_group)
		{
			$message['title']="[".$message_group."]:".$message['title'];
			$message['content']="[".$message_group."]:".$message['content'];
		}		
		$message['create_time'] = get_gmtime();
		$message['rel_table'] = $rel_table;
		$message['rel_id'] = addslashes(trim($_REQUEST['rel_id']));
		$message['user_id'] = intval($GLOBALS['user_info']['id']);
		if(intval($_REQUEST['city_id'])==0)
		$message['city_id'] = $deal_city['id'];
		else
		$message['city_id'] = intval($_REQUEST['city_id']);
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$message_effect = 0;
		}
		else
		{
			$message_effect = $message_type['is_effect'];
		}
		$message['is_effect'] = $message_effect;		
		$message['is_buy'] = 0;
		$message['contact'] = $_REQUEST['contact']?htmlspecialchars(addslashes($_REQUEST['contact'])):'';
		$message['contact_name'] = $_REQUEST['contact_name']?htmlspecialchars(addslashes($_REQUEST['contact_name'])):'';
		$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
		if($message_group=='退款'&&$rel_table=='deal_order')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 1 where id = ".intval($message['rel_id']));
		}
		if($message_group=='退货'&&$rel_table=='deal_order')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set retake_status = 1 where id = ".intval($message['rel_id']));
		}
		showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS'],$ajax);
	}
}
?>