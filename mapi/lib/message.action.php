<?php
class message
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);

		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');

		$page = intval($GLOBALS['request']['page'])>0?intval($GLOBALS['request']['page']):1;
		$page_size = 20;
		
		
		$limit = (($page-1)*$page_size).",".$page_size;
		$user_id = intval($GLOBALS['user_info']['id']);
		$sql = "select group_key,count(group_key) as total from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
				group by group_key 
				order by system_msg_id desc,max(create_time) desc limit ".$limit;
		$sql_count = "select count(distinct(group_key)) from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))";
		
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
			$list[$k] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_box where group_key = '".$v['group_key']."' and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  order by create_time desc limit 1");
			$list[$k]['total'] = $v['total'];
			if($list[$k]['system_msg_id']>0)
			{
				$sys_msgs[] = array(
					"mid" => $list[$k]['group_key'],
                    "uid"=> $list[$k]['to_user_id'],
                    "status" => 1,
                    "title" => $list[$k]['title'],
					"time" => pass_date($list[$k]['create_time'])
				);
			}
			else
			{
				$msg_list[] = array(
					"content" => $list[$k]['content'],
					"uid"	=>	$list[$k]['from_user_id'],
					"user_name"	=> $list[$k]['from_user_id']==$user_data['id']?"我":$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".intval($list[$k]['from_user_id'])),
 					"user_avatar" => get_abs_img_root(get_muser_avatar($list[$k]['from_user_id'],"big")),
					"tuid"	=>	$list[$k]['to_user_id'],
					"tuser_name"	=> $list[$k]['to_user_id']==$user_data['id']?"我":$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".intval($list[$k]['to_user_id'])),
 					"tuser_avatar" => get_abs_img_root(get_muser_avatar($list[$k]['to_user_id'],"big")),
					"time" => pass_date($list[$k]['create_time']),
					"msg_count"	=>	$list[$k]['total'],
					"mlid"	=>	$list[$k]['group_key']					
				);
			}
			
		}
		$count = $GLOBALS['db']->getOne($sql_count);
		
		
		$root['return'] = 1;
		$root['sys_msgs'] = $sys_msgs;
		$root['msg_list'] = $msg_list;
		
		//分页
		$page_info['page'] = $page;
		$page_info['page_total'] = ceil($count/$page_size);
		$root['page'] = $page_info;
		output($root);
	}
}
?>