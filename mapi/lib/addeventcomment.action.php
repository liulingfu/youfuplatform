<?php
class addeventcomment
{
	public function index()
	{
		
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		$event_id = intval($GLOBALS['request']['event_id']);
		//没有分享ID直接退出
		if($event_id == 0)
		{
			$root['status'] = 0;
			$root['info'] = "不存在的活动ID";
			output($root);
		}
		$content = strim($GLOBALS['request']['content']);
		$source = strim($GLOBALS['request']['source']);
		$source = str_replace("来自","",$source);
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['status'] = 0;
			$root['user_login_status'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		$root['user_login_status'] = 1;
		$event_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where is_effect = 1 and id = ".$event_id);
		if(empty($event_info))
		{
			$root['status'] = 0;
			$root['info'] = "不存在的活动ID";
			output($root);
		}
		
		$reply_data = array();
		$reply_data['rel_table'] = "event";
		$reply_data['rel_id'] = intval($event_id);		
		$reply_data['content'] = valid_str($content);
		
		

		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
				$message_effect = 0;
		}
		else
		{
				$message_effect = 1;//$message_type['is_effect'];
		}
		
		$reply_data['is_effect'] = $message_effect;
		$reply_data['create_time'] = get_gmtime();
		$reply_data['user_id'] = intval($user_data['id']);	
		
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."message",$reply_data);
		increase_user_active($user_data['id'],"点评了一个活动");
		
			$title = "对".$event_info['name']."发表了点评";
			$url_route = array(
					'rel_app_index'	=>	'youhui',
					'rel_route'	=>	'edetail',
					'rel_param' => 'id='.$event_info['id']
				);
			
			$tid = insert_topic($reply_data['content'],$title,"eventcomment",$group="", $relay_id = 0, $fav_id = 0,$group_data ="",$attach_list=array(),$url_route);
			if($tid)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '".$source."' where id = ".intval($tid));
			}
			$GLOBALS['db']->query("update ".DB_PREFIX."event set reply_count = reply_count+1 where id =".$event_id);
		
		
		$root['status'] = 1;
		$root['info'] = "感谢您的点评";
		output($root);
	}
}
?>