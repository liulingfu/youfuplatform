<?php
class u
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);		
		$result = do_login_user($email,$pwd);
		$user_data = es_session::get('user_info');		
		$uid = intval($user_data['id']);
		$home_uid = intval($GLOBALS['request']['uid']);
		$home_user_info_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$home_uid." and is_effect = 1 and is_delete = 0"); 
		if(!$home_user_info_data)	
		{
			$root['info'] = "非法的会员";
			output($root);
		}
		
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
				
			$root = array();
			$user_info['uid'] = $user_data['id'] = intval($user_data['id']);
			$user_info['email'] = $user_data['email'];
			$user_info['user_name'] = $user_data['user_name'];
			$user_info['user_avatar'] =	get_abs_img_root(get_muser_avatar($user_data['id'],"small"));
			$root['user'] = $user_info;
			
			$home_user_info['uid'] = $home_user_info_data['id'];
			$home_user_info['email'] = $home_user_info_data['email'];
			$home_user_info['user_name'] = $home_user_info_data['user_name'];
			$home_user_info['user_avatar'] = get_abs_img_root(get_muser_avatar($home_user_info_data['id'],"small"));
			$home_user_info['fans'] = $home_user_info_data['focused_count'];
			$home_user_info['follows'] = $home_user_info_data['focus_count'];
			$home_user_info['photos'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where user_id = ".$home_user_info_data['id']);
			$home_user_info['favs'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$home_user_info_data['id']." and fav_id <> 0 and is_delete = 0 and is_effect = 1");
			$home_user_info['goods'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$home_user_info_data['id']." and topic_group = 'Fanwe' and is_delete = 0 and is_effect = 1");
			$home_user_info['bfavs'] = $GLOBALS['db']->getOne("select sum(fav_count) from ".DB_PREFIX."topic where user_id = ".$home_user_info_data['id']." and is_delete = 0 and is_effect = 1");
			
			
			
			if($user_info['uid']==$home_user_info['uid'])
	        {
	        	$home_user_info['is_follow'] = -1;
	        }
	        else
	        {
				$focus_uid = intval($home_user_info['uid']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_info['uid']." and focused_user_id = ".$focus_uid);
				if($focus_data)
				$home_user_info['is_follow'] = 1;
				else
				$home_user_info['is_follow'] = 0;			
			 }
	        
			
			
			$root['home_user'] = $home_user_info;
			
			//关注的用户ID
			$uids = $GLOBALS['db']->getOne("select group_concat(focused_user_id) from ".DB_PREFIX."user_focus where focus_user_id = ".$user_data['id']." order by rand() limit 50");
		 	$uids.=",0";		 	
			$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;	
	
			$topic_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and user_id =".$home_user_info_data['id']." order by create_time desc limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and user_id =".$home_user_info_data['id']);
			
			foreach($topic_list as $k=>$v)
			{
				$topic_list[$k] = m_get_topic_item($v);
				if($v['fav_id']>0||$v['relay_id']>0)
				$relay_share = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and id = ".$v['origin_id']);
				
				if($relay_share)
				$topic_list[$k]['relay_share'] = m_get_topic_item($relay_share);
			}
			
			
			
			$root['return'] = 1;		
			$root['item'] = $topic_list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($total/PAGE_SIZE));

		output($root);
	}
}
?>