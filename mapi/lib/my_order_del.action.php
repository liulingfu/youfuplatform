<?php
class my_order_del{
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
			
			$id = intval($GLOBALS['request']['id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_delete = 1,order_status = 1 where id = ".$id." and user_id = ".intval($user_id)." and pay_status = 0");
			$rs = $GLOBALS['db']->affected_rows();
			if($rs)
			{
				$root['return'] = 1;
				$root['info'] ="删除成功";
			}
			else
			{
				$root['return'] = 0;
				$root['info'] ="删除失败";
			}

		}
		else
		{
			$root['return'] = 0;
			$root['info'] ="未登录";
			$root['user_login_status'] = 0;		
		}		
	
		output($root);
	}
}
?>