<?php
class darens
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
		$user_info['uid'] = $user_data['id'];
		$user_info['email'] = $user_data['email'];
		$user_info['user_name'] = $user_data['user_name'];
		$user_info['user_avatar'] =	get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
		
		
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;

			
		$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;	
		$list = $GLOBALS['db']->getAll("select id,user_name,daren_title from ".DB_PREFIX."user where is_delete = 0 and is_effect = 1 and is_daren = 1 order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where is_delete = 0 and is_effect = 1 and is_daren = 1 ");
		

		$darens = array();
		foreach($list as $k=>$v)
		{
			$darens[$k]['uid'] = $v['id'];
			$darens[$k]['user_name'] = $v['user_name'];
			if($v['daren_title']!='')
			$darens[$k]['user_name'] .= "[".$v['daren_title']."]";
			$darens[$k]['fans'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ". $v['id']);
			$darens[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['id'],"big"));
			if($v['id']==$user_data['id'])
			{
				$darens[$k]['is_follow'] = -1;
			}
			else
			{
				$focus_uid = intval($v['id']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_info['uid']." and focused_user_id = ".$focus_uid);
				if($focus_data)
				$darens[$k]['is_follow'] = 1;
				else
				$darens[$k]['is_follow'] = 0;
			}
		}
		
		$root['page'] = array("page"=>$page,"page_total"=>ceil($total/PAGE_SIZE));
		$root['item'] = $darens;
		$root['return'] = 1;
		$root['status'] = 1;
		output($root);
	}
}
?>