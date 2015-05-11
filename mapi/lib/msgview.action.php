<?php
class msgview
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
		$group_key = addslashes(trim($GLOBALS['request']['mid']));
		
		$sql = "select count(*) as count,max(system_msg_id) as system_msg_id,max(id) as id from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
				and group_key = '".$group_key."'";
		$row = $GLOBALS['db']->getRow($sql);
		if($row['count']==0)
		{
			$root['return'] = 0;			
		}
		elseif($row['system_msg_id']>0)
		{
			//系统消息，仅查看
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_box where id = ".$row['id']." and is_delete = 0");
			$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set is_read = 1 where id = ".$row['id']);
			$root['return'] = 1;
			$root['msg'] = array(
				'mid'	=> $group_key,
				'title'	=> $data['title'],
				'message'	=>	$data['content'],
				'time'	=>	pass_date($data['create_time'])
			);
			
		}
		else
		{
			$root['return'] = 1;
			$root['lid'] = $group_key;
			//消息记录
			$sql = "select * from ".DB_PREFIX."msg_box  
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
					and group_key = '".$group_key."' 
					order by create_time desc limit ".$limit;
			$sql_count = "select count(*) from ".DB_PREFIX."msg_box  
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1)) and group_key = '".$group_key."'";
		
			$upd_sql = "update ".DB_PREFIX."msg_box set is_read = 1 
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
					and group_key = '".$group_key."' ";
					
			$GLOBALS['db']->query($upd_sql);
			$list = $GLOBALS['db']->getAll($sql);
		
			foreach($list as $k=>$v)
			{
				if($v['to_user_id']!=$user_id)
				{
					$dest_user_id = $v['to_user_id'];
					break;
				}
				if($v['from_user_id']!=$user_id)
				{
					$dest_user_id = $v['from_user_id'];
					break;
				}
			}
		
			
			$dest_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$dest_user_id);
			$root['title'] = "与".$dest_user_name."的交流";
			$root['t_name'] = $dest_user_name;
			
			
			$count = $GLOBALS['db']->getOne($sql_count);
			$page_info['page'] = $page;
			$page_info['page_total'] = ceil($count/$page_size);
			$root['page'] = $page_info;
			
			$msg_list = array();
			foreach ($list as $k=>$v)
			{
				$msg_list[] = array(
					"miid" => $v['id'],
					"mlid"	=> $v['group_key'],
					"uid"	=>	$v['from_user_id'],
					"message"	=> $v['content'],
					"time"	=>	pass_date($v['create_time']),
					"tuid"	=> $v['to_user_id'],
					"tuser_name"	=> $v['to_user_id']==$user_id?"我":$dest_user_name,
					"tuser_avatar"	=> get_abs_img_root(get_muser_avatar($v['to_user_id'],"big")),
					"content"	=>$v['content'],
					"user_name"	=> $v['from_user_id']==$user_id?"我":$dest_user_name,
					"user_avatar"	=>  get_abs_img_root(get_muser_avatar($v['from_user_id'],"big"))
				);
			}
			
			$root['msg_list'] = $msg_list;
		}
		output($root);
	}
}
?>