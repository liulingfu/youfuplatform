<?php
class my_order_list{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);			
			
		$root = array();
		$root['return'] = 1;		
		if($user_id>0)
		{
			$root['user_login_status'] = 1;		
			
			$nowPage = intval($GLOBALS['request']['page']); //当前分页
			$totalRows = intval($GLOBALS['request']['totalRows']); //总记录数	
			$pageRows = PAGE_SIZE;//每页显示记录数	
			
			$limit = (($nowPage-1)*$pageRows).",".$pageRows;
			
			if ($totalRows == 0){		
				$totalRows = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0");	
			
			}
			$totalPages = ceil($totalRows / $pageRows); //总页数
		
			//$root = array();
			
			$root['totalPages'] = $totalPages; //总页数
			$root['pageRows'] = $pageRows; //页记录数
			$root['nowPage'] = $nowPage; //当前页
			$root['totalRows'] = $totalRows;//总记录数
		
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0 order by create_time desc limit ".$limit);		
			
			$root['return'] = 1;
		
			$orderlist = array();
			foreach($list as $item)
			{
				$orderlist[] = get_order_goods($item);
			}
			$root['item'] = $orderlist;
					
			$root['page'] = array("page"=>$nowPage,"page_total"=>$totalPages);		
		}
		else
		{
			$root['user_login_status'] = 0;		
		}		
	
		output($root);
	}
}
?>