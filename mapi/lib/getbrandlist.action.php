<?php
class getbrandlist
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;

		$page = intval($GLOBALS['request']['page']); //分页
		
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
				
		$sql_count = "select count(*) from ".DB_PREFIX."brand";
		
		$sql = "select id,name,logo,dy_count from ".DB_PREFIX."brand";
		
		$where = "1 = 1";
		
		$sql_count.=" where ".$where;
		$sql.=" where ".$where. " order by sort desc, dy_count ";
		$sql.=" limit ".$limit;
				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);


		$list = $GLOBALS['db']->getAll($sql);
		$brand_list = array();
		foreach($list as $item){
			
			$brand_list[] = array("id"=>$item['id'],
								   "name"=>$item['name'],
								   "dy_count"=>$item['dy_count'],
									"logo"=> get_abs_img_root($item['logo']),
									"is_checked" => intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."brand_dy where uid = ".$user_id." and brand_id = ".$item['id']))
			
			);
		}
		

		$root['item'] = $brand_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		
		output($root);
	}
}
?>