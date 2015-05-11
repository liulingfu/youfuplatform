<?php
class delcomment
{
	public function index()
	{
		
		
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['return'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		
		$comment_id = intval($GLOBALS['request']['id']);
		$comment_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_reply where id = ".$comment_id);
		
		if($comment_data['user_id']!=intval($user_data['id']))
		{
			$root['return'] = 0;			
			$root['info'] = "这条评论是其他会员的";
			output($root);	
		}
		$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_reply where id = ".$comment_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count - 1 where id = ".$comment_data['topic_id']);
		$root['return'] = 1;
		output($root);
	}
}
?>