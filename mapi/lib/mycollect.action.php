<?php
class mycollect
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;

		
		$page = intval($_FANWE['requestData']['page']); //分页
		$page=$page==0?1:$page;
		
		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id == 0){
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			output($root);
		}else{
			$root['user_login_status'] = 1;
		}
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
				
		$sql_count = "select count(*) from ".DB_PREFIX."youhui as yh left join ".DB_PREFIX."youhui_sc as sc on yh.id = sc.youhui_id ";
		
		$sql = "select yh.id, yh.supplier_id as merchant_id,yh.name as title,yh.list_brief as content,yh.icon as merchant_logo,yh.create_time,yh.xpoint,yh.ypoint,yh.address as api_address,yh.icon as image_1 from ".DB_PREFIX."youhui as yh left join ".DB_PREFIX."youhui_sc as sc on yh.id = sc.youhui_id ";
		
		$where = " 1 = 1 and yh.is_effect = 1 and sc.uid = ".$user_id;
		
		$sql_count.=" where ".$where;
		$sql.=" where ".$where;
		$sql.=" order by yh.create_time desc limit ".$limit;
				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);
		//echo $sql;
		
		$list = $GLOBALS['db']->getAll($sql);
		$youhui_list = array();
		foreach($list as $item){
			
			$youhui_list[] = m_youhuiItem($item);//			
			
		}

		$root['item'] = $youhui_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		$root['now'] = $now;
		
		output($root);
	}
}
?>