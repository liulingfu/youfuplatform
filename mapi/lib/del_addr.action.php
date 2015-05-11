<?php
class del_addr{
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
			$root['user_login_status'] = 0;		
			$id = intval($GLOBALS['request']['id']);//id,有ID值则更新，无ID值，则插入

	
			$sql = "delete from ".DB_PREFIX."user_consignee where user_id = {$user_id} and id = {$id}";
			$GLOBALS['db']->query($sql);
		
			$root = array();
			$root['return'] = 1;
			$root['info'] = "数据删除成功!";
		}
		else
		{
			$root['user_login_status'] = 1;		
		}		
	
		output($root);
	}
}
?>