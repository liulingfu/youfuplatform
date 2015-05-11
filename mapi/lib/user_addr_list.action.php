<?php
class user_addr_list{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);			
			
		$root = array();
		$root['return'] = 1;		
		if($user_id>0)
		{
			$root['user_login_status'] = 1;		
			$addr_list = getUserAddr($user_id,true);
			$root['item'] = $addr_list;
		}
		else
		{
			$root['user_login_status'] = 0;		
		}		
	
		output($root);
	}
}
?>