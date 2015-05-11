<?php
class saveuserfrequented
{
	public function index()
	{
		
		
		$root = array();
		
				
		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);		
		$user_id  = intval($user['id']);
		if ($user_id == 0){
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			output($root);
		}else{
			$root['user_login_status'] = 1;
		}
		
		$userfrequented = array(	
					'uid' => $user_id,
					'title' => $GLOBALS['request']['title'],
					'addr' => $GLOBALS['request']['addr'],
					'zoom_level' => floatval($GLOBALS['request']['zoom_level']),
					'latitude_top' => floatval($GLOBALS['request']['latitude_top']),
					'latitude_bottom' => floatval($GLOBALS['request']['latitude_bottom']),
					'longitude_left' => floatval($GLOBALS['request']['longitude_left']),
					'longitude_right' => floatval($GLOBALS['request']['longitude_right']),
					'xpoint' => floatval($GLOBALS['request']['xpoint']),
					'ypoint' => floatval($GLOBALS['request']['ypoint'])					
		);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_frequented", $userfrequented, 'INSERT');
		$uid = $GLOBALS['db']->insert_id();	
		$root['uid'] = $uid;
		if($uid > 0)
		{
			$root['return'] = 1;
			$root['info'] = "添加成功";
		}else{
			$root['return'] = 0;
			$root['info'] = "添加失败";
		}
		output($root);
	}
}
?>