<?php
class ucommentlist
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);		
		$result = do_login_user($email,$pwd);
		$user_data = es_session::get('user_info');	
		$user_data['id'] = intval($user_data['id']);	
		$uid = intval($user_data['id']);
		if($uid == 0)
		{
			$root['return'] = 0;
			$root['info'] = "请先登陆";
			output($root);
		}
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
		
		$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;
		//输出回复
		$sql = "select r.* from ".DB_PREFIX."topic_reply as r left join ".DB_PREFIX."topic as t on r.topic_id = t.id 
				where (t.user_id = ".$uid." or r.user_id = ".$uid.") and r.is_effect = 1 and r.is_delete = 0 
				order by r.create_time desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."topic_reply as r left join ".DB_PREFIX."topic as t on r.topic_id = t.id 
				where (t.user_id = ".$uid." or r.user_id = ".$uid.") and r.is_effect = 1 and r.is_delete = 0";
		
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($sql_count);
		$comment_list = array();
		foreach($list as $k=>$v)
		{
			$comment_list[$k]['comment_id'] = $v['id'];
			$comment_list[$k]['share_id'] = $v['topic_id'];
			$comment_list[$k]['uid'] = $v['user_id'];
			$comment_list[$k]['parent_id'] = $v['reply_id'];
			$comment_list[$k]['content'] = $v['content'];
			$comment_list[$k]['create_time'] = $v['create_time'];
			$topic = $GLOBALS['db']->getRow("select user_name,content from ".DB_PREFIX."topic where id = ".$v['topic_id']);
			$comment_list[$k]['scontent'] = "//@".$topic['user_name'].":".$topic['content'];
			$comment_list[$k]['user_name'] = $v['user_name'];
			$comment_list[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['user_id'],"big"));
			$comment_list[$k]['time'] = pass_date($v['create_time']);
			$comment_list[$k]['parse_expres'] = get_parse_expres($comment_list[$k]['content'].$comment_list[$k]['scontent']);
			$comment_list[$k]['parse_user'] = get_parse_user($comment_list[$k]['content'].$comment_list[$k]['scontent']);
		}
		$root['item'] = $comment_list;
		$root['page'] = array("page"=>$page,"page_total"=>ceil($total/PAGE_SIZE));
		$root['return'] = 1;
		output($root);
	}
}
?>