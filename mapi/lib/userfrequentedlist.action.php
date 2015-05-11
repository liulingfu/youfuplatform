<?php
class userfrequentedlist
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;				
		
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
		
		$del_id = intval($GLOBALS['request']['del_id']);
		
		if ($del_id > 0){
			$sql = "delete from ".DB_PREFIX."user_frequented where id = ".$del_id. " and uid = ".$user_id;
			$GLOBALS['db']->query($sql);
		}
		
		$sql = "select * from ".DB_PREFIX."user_frequented where uid = ".$user_id;
		
		$list = $GLOBALS['db']->getAll($sql);
		$userfrequentedlist = array();
		foreach($list as $item){
			
			$userfrequentedlist[] = array("id"=>$item['id'],
								   "uid"=>$item['uid'],
									"title"=> $item['title'],
									"addr"=> $item['addr'],		
									"latitude_top"=>$item['latitude_top'],
									"latitude_bottom"=>$item['latitude_bottom'],
									"longitude_left"=>$item['longitude_left'],
									"longitude_right"=>$item['longitude_right'],
									"zoom_level"=>$item['zoom_level'],
									"xpoint"=>$item['xpoint'],
									"ypoint"=>$item['ypoint']
			
			);
		}

		$root['item'] = $userfrequentedlist;
		
		output($root);
	}
}
?>