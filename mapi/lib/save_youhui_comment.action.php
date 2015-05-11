<?php
class save_youhui_comment
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
		
		$yh_id = intval($GLOBALS['request']['yh_id']);
		$content = addslashes($GLOBALS['request']['content']);

		//$merchant_id = intval($GLOBALS['db']->getOne("select supplier_location_id from ".DB_PREFIX."youhui where id='".$yh_id."'"));

		$merchant_youhui_comment = array(
					'user_id' => $user_id,
					'rel_id' => $yh_id,
                    'rel_table' =>'youhui',
					//'supplier_location_id' => $merchant_id,
					'title' => $content,
                    'content' => $content,
					'is_effect' => 1,
					'create_time' => get_gmtime(),
		);

		$GLOBALS['db']->autoExecute(DB_PREFIX."message", $merchant_youhui_comment, 'INSERT');
		$id = $GLOBALS['db']->insert_id();
		$root['id'] = $id;
		if($id > 0)
		{
			increase_user_active($user_id,"点评了一个优惠券");
			$root['status'] = 1;
			$root['info'] = "添加成功";
		}else{
			$root['status'] = 0;
			$root['info'] = "添加失败";
		}
		output($root);
	}
}
?>