<?php
class addcomment
{
	public function index()
	{
		
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		$share_id = intval($GLOBALS['request']['share_id']);
		//没有分享ID直接退出
		if($share_id == 0)
		{
			$root['status'] = -2;
			output($root);
		}
		$content = strim($GLOBALS['request']['content']);
		$source = strim($GLOBALS['request']['source']);
		$source = str_replace("来自","",$source);
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		$is_relay = intval($GLOBALS['request']['is_relay']);
		$parent_id = intval($GLOBALS['request']['parent_id']);
		if($parent_id>0)
		$parent_reply = $GLOBALS['db']->getRow("select id,user_id,user_name from ".DB_PREFIX."topic_reply where id = ".$parent_id);
		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['return'] = 0;
			$root['user_login_status'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		$root['user_login_status'] = 1;
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and id = ".$share_id);
		if(empty($topic))
		{
			$root['status'] = -3;
			output($root);
		}
		
		$reply_data = array();
		$reply_data['topic_id'] = $share_id;
		$reply_data['user_id'] = intval($user_data['id']);
		$reply_data['user_name'] = $user_data['user_name'];
		$reply_data['reply_id'] = intval($parent_reply['id']);
		$reply_data['reply_user_id'] = intval($parent_reply['user_id']);
		$reply_data['reply_user_name'] = strim($parent_reply['user_name']);
		$reply_data['create_time'] = get_gmtime();
		$reply_data['is_effect'] = 1;
		$reply_data['is_delete'] = 0;
		$reply_data['content'] = valid_str($content);
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_reply",$reply_data);
		$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count + 1,last_time = ".get_gmtime().",last_user_id=".$user_data['id']." where id = ".$share_id);
		increase_user_active($user_data['id'],"转发了一则分享");
		if($is_relay==1)
		{
			$cnt = $topic['content']."@".$user_data['user_name']." 评论:".valid_str($content);
			$id = insert_topic($cnt,$title="",$type="",$group="",$relay_id=$share_id,$fav_id=0);
			if($id)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '".$source."' where id = ".intval($id));
			}
		}
		
		$root['return'] = 1;
		$root['status'] = 1;
		output($root);
	}
}
?>