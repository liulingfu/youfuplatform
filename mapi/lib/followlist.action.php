<?php
class followlist
{
	public function index()
	{

		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		

		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
				
		$home_uid = intval($GLOBALS['request']['uid']);
		$home_user_info_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$home_uid." and is_effect = 1 and is_delete = 0"); 
		if(!$home_user_info_data)	
		{
			$root['info'] = "非法的会员";
			output($root);
		}
		
		
		
		$user_info['uid'] = $user_data['id'];
		$user_info['email'] = $user_data['email'];
		$user_info['user_name'] = $user_data['user_name'];
		$user_info['user_avatar'] =	get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
		$root['user'] = $user_info;
			
		$home_user_info['uid'] = $home_user_info_data['id'];
		$home_user_info['email'] = $home_user_info_data['email'];
		$home_user_info['user_name'] = $home_user_info_data['user_name'];
		$home_user_info['user_avatar'] = get_abs_img_root(get_muser_avatar($home_user_info_data['id'],"big"));
		$home_user_info['fans'] = $home_user_info_data['focused_count'];
		$home_user_info['follows'] = $home_user_info_data['focus_count'];
		$home_user_info['photos'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where user_id = ".$home_user_info_data['id']);
		$home_user_info['favs'] = $GLOBALS['db']->getOne("select sum(fav_count) from ".DB_PREFIX."topic where user_id = ".$home_user_info_data['id']);
		$root['home_user'] = $home_user_info;
			
		$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;	
		$follow_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$home_user_info_data['id']." order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ". $home_user_info_data['id']);
		

		$follows = array();
		foreach($follow_list as $k=>$v)
		{
			$follows[$k]['uid'] = $v['id'];
			$follows[$k]['user_name'] = $v['user_name'];
			$follows[$k]['fans'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ". $v['id']);
			$follows[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['id'],"big"));
			if($v['id']==$user_data['id'])
			{
				$follows[$k]['is_follow'] = -1;
			}
			else
			{
				$focus_uid = intval($v['id']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_info['uid']." and focused_user_id = ".$focus_uid);
				if($focus_data)
				$follows[$k]['is_follow'] = 1;
				else
				$follows[$k]['is_follow'] = 0;
			}
		}
		
		$root['page'] = array("page"=>$page,"page_total"=>ceil($total/PAGE_SIZE));
		$root['item'] = $follows;
		$root['return'] = 1;
		$root['status'] = 1;
		output($root);
	}
}
?>