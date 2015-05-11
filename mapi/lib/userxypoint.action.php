<?php
class userxypoint
{
	public function index()
	{

		$root = array();
		$root['return'] = 0;

		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = md5(addslashes($GLOBALS['request']['pwd']));//密码

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);

		$latitude = floatval($GLOBALS['request']['latitude']);//ypoint
		$longitude = floatval($GLOBALS['request']['longitude']);//xpoint


		if ($user_id > 0 && $latitude > 0 && $longitude > 0){
			$user_x_y_point = array(
								'uid' => $user_id,
								'xpoint' => $longitude,
								'ypoint' => $latitude,
								'locate_time' => get_gmtime(),
			);
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_x_y_point", $user_x_y_point, 'INSERT');
			$sql = "update ".DB_PREFIX."user set xpoint = $longitude, ypoint = $latitude, locate_time = ".get_gmtime()." where id = $user_id";
			$GLOBALS['db']->query($sql);

			$root['return'] = 1;
		}

		output($root);
	}
}
?>