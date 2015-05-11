<?php
class check_ecv{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$ecvSn = strim($GLOBALS['request']['ecv_sn']);
		$ecvPassword = strim($GLOBALS['request']['ecv_pwd']);
		$now = get_gmtime();
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);	

		$root = array();
		$root['return'] = 1;
		$root['info'] = "";	
		$root['check_ecv_state'] = 0;//0:无效,1:有效
		if($user_id>0)
		{
			$root['user_login_status'] = 1;			
			if (!empty($ecvSn))
			{
				$ecv_sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvSn."' and e.password = '".
				$ecvPassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";
				
				$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
				
				if (!$ecv_data){
					$root['info'] = "无效的代金券";	
				}else{
					$root['check_ecv_state'] = 1;
					$root['info'] = "验证成功!";	
				}		
			}
			else
			{
				$root['info'] = "卡号不能为空!";	
			}	
		}
		else
		{
			$root['user_login_status'] = 0;
		}		
			
		
		output($root);
	}
}
?>