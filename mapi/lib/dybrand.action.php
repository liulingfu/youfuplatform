<?php
class dybrand
{
	public function index()
	{
		$root = array();
		$root['return'] = 1;
		
		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		//print_r($user);exit;
		$user_id  = intval($user['id']);
		if ($user_id == 0){
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			output($root);
		}else{
			$root['user_login_status'] = 1;
		}
		
		$brand_id = intval($GLOBALS['request']['brand_id']);
		
		$data['uid'] = $user_id;
		$data['brand_id'] = $brand_id;
		
		$is_set = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."brand_dy where uid = ".$user_id." and brand_id = ".$brand_id));
		if($is_set==1)
		{
		
			$GLOBALS['db']->query("update ".DB_PREFIX."brand set dy_count = dy_count - 1 where dy_count >0 and id =".$brand_id);
		
			$GLOBALS['db']->query("delete from ".DB_PREFIX."brand_dy where uid = ".$user_id." and brand_id =".$brand_id);
			$root['status'] = 0;
			output($root);
		}
		else
		{
			$GLOBALS['db']->autoExecute(DB_PREFIX."brand_dy", $data, 'INSERT');
			
			$GLOBALS['db']->query("update ".DB_PREFIX."brand set dy_count = dy_count + 1 where id =".$brand_id);
			
			$root['status'] = 1;
			output($root);
		}
	}
}
?>