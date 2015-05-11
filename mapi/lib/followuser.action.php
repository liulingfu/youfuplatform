<?php
class followuser
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		$uid = intval($GLOBALS['request']['uid']);
		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['return'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		
		//开始关注
					$user_id = intval($user_data['id']);
					$focus_uid = $uid;
					if($user_id!=$focus_uid)
					{					
						$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
						if(!$focus_data&&$user_id>0&&$focus_uid>0)
						{
								$focused_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$focus_uid);
								$focus_data = array();
								$focus_data['focus_user_id'] = $user_id;
								$focus_data['focused_user_id'] = $focus_uid;
								$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
								$focus_data['focused_user_name'] = $focused_user_name;
								$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
								$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
								$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
	
						}
						elseif($focus_data&&$user_id>0&&$focus_uid>0)
						{
							$GLOBALS['db']->query("delete from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count - 1 where id = ".$user_id);
							$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count - 1 where id = ".$focus_uid);		

						}
					}
					//开始关注
		$root['status'] = 1;
		output($root);
	}
}
?>