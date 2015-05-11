<?php
class postcart{
	public function index()
	{
//		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
//		$pwd = strim($GLOBALS['request']['pwd']);//密码
//		
//		//检查用户,用户密码
//		$user = user_check($email,$pwd);
//		$user_id  = intval($user['id']);
//
//		$cartdata = $GLOBALS['request']['cartdata'];
//		$res = insertCartData($user_id,session_id(),$cartdata);
//		
//		$root = array();
//		if($res['info']=='')
//		{
//			$root['return'] = 1;
//			$root['info'] = "提交成功";
//		}
//		else
//		{
//			$root['return'] = 0;
//			$root['info'] = $res['info'];
//		}				
		$root['cartinfo'] = $GLOBALS['m_config']['yh'];		
		output($root);
	}
}
?>