<?php
class atmelist
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);		
		$result = do_login_user($email,$pwd);
		$user_data = es_session::get('user_info');		
		$uid = intval($user_data['id']);
		if($uid == 0)
		{
			$root['info'] = "请先登陆";
			output($root);
		}
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
		
			$user_info['uid'] = $user_data['id'];
			$user_info['email'] = $user_data['email'];
			$user_info['user_name'] = $user_data['user_name'];
			$user_info['user_avatar'] =	get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
			$root['home_user'] = $root['user'] = $user_info;
			 	
			$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;	
	
			
			$user_name = $user_data['user_name'];
			$user_name_unicode = str_to_unicode_string($user_name);
			$condition = " match(user_name_match) against('".$user_name_unicode."' IN BOOLEAN MODE) ";
			
			$topic_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and ".$condition." order by create_time desc limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and ".$condition);
			
			foreach($topic_list as $k=>$v)
			{
				$topic_list[$k] = m_get_topic_item($v);
				if($v['fav_id']>0||$v['relay_id']>0)
				$relay_share = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and id = ".$v['origin_id']);
				
				if($relay_share)
				$topic_list[$k]['relay_share'] = m_get_topic_item($relay_share);
			}			
			
			$root = array();
			$root['return'] = 1;		
			$root['item'] = $topic_list;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($total/PAGE_SIZE));

		output($root);
	}
}
?>