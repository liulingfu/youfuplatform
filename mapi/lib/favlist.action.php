<?php
//接口名: favlist

class favlist
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$uid = intval($GLOBALS['request']['uid']);
		if($uid==0)
		{
			$email = strim($GLOBALS['request']['email']);
			$pwd = strim($GLOBALS['request']['pwd']);		
			$result = do_login_user($email,$pwd);
			$user_data = es_session::get('user_info');	
			$user_data['id'] = intval($user_data['id']);
			$uid = $user_data['id'];
		}		

		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;

		
			$page_size = 20;
		
			$limit = ($page-1)*$page_size.",".$page_size;
			
			$root = array();
			$root['return'] = 1;

			
			$condition = " 1 = 1 ";
			$sort = "";
			
			$condition .= " and user_id = ".$uid." and fav_id <> 0 ";
			$sort .= " order by  t.create_time desc  ";
			
			
			
			$sql = "select t.id,t.fav_id,t.origin_id from ".DB_PREFIX."topic as t  where ".$condition.$sort." limit ".$limit;
			$sql_total = "select count(*) from ".DB_PREFIX."topic as t where ".$condition;
			
			
			$total = $GLOBALS['db']->getOne($sql_total);		
			$result = $GLOBALS['db']->getAll($sql);

			
			$share_list =array();
			foreach($result as $k=>$v)
			{
				$share_list[$k]['share_id'] = $v['fav_id'];
				$image = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_image where topic_id = ".$v['origin_id']." limit 1");
				$share_list[$k]['img'] = get_abs_img_root(get_spec_image($image['o_path'],200,0,0));			
				$share_list[$k]['height'] = floor($image['height'] * (200 / $image['width']));			
			}
			$root['item'] = $share_list;
			
			//分页
			$page_info['page'] = $page;
			$page_info['page_total'] = ceil($total/$page_size);
			$root['page'] = $page_info;

		output($root);
		
	}
}
?>