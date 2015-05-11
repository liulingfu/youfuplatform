<?php
class commentlist
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		$id = intval($GLOBALS['request']['share_id']);
		$page = intval($GLOBALS['request']['page']);
		
		$result = do_login_user($email,$pwd);
		$user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
        
		$root = array();
		$root['return'] = 1;
		$res = m_get_topic_reply($id,$page);
		$root['item'] = $res['list'];
		$root['page'] = $res['page'];
		
		
		output($root);
	}
}
?>