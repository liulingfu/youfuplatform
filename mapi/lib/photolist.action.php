<?php
class photolist
{
	public function index()
	{

		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
				
		$uid = intval($GLOBALS['request']['uid']);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid." and is_effect = 1 and is_delete = 0"); 
		if(!$user_info)	
		{
			$root['info'] = "非法的会员";
			output($root);
		}
		
		$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;	
		$image_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_image where user_id = ".$user_info['id']." and topic_id > 0 order by create_time desc limit ".$limit);
		$image_total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where user_id = ".$user_info['id']." and topic_id > 0  ");
		
		$images  = array();
		foreach($image_list as $k=>$v)
		{
			$images[$k]['photo_id'] = $v['id'];
			$images[$k]['share_id'] = $v['topic_id'];
			$images[$k]['img'] = get_abs_img_root(get_spec_image($v['o_path'],200,0,0));	
			$images[$k]['height'] = floor($v['height'] * (200 / $v['width']));	
		}
		
		$root['page'] = array("page"=>$page,"page_total"=>ceil($image_total/PAGE_SIZE));
		$root['item'] = $images;
		$root['return'] = 1;

		output($root);
	}
}
?>