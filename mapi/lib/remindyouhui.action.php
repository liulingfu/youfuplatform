<?php
class remindyouhui
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;


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
		
		$brand_ids = $GLOBALS['db']->getOne("select group_concat(brand_id) from ".DB_PREFIX."brand_dy where uid = ".$user_id);
		//print_r($brand_ids);
		if(!$brand_ids)
			$brand_ids = -1;
		if( substr($brand_ids,-1,1)==',')
		{
			$brand_ids = substr($brand_ids,0,-1);
		}

		$merchant_ids = $GLOBALS['db']->getOne("select group_concat(supplier_id) from ".DB_PREFIX."supplier_dy where uid = ".$user_id);
		if(!$merchant_ids)
			$merchant_ids = -1;
		if( substr($merchant_ids,-1,1)==',')
		{
			$merchant_ids = substr($merchant_ids,0,-1);
		}


		$page = intval($GLOBALS['request']['page']); //分页

		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;

		$sql_count = "select count(*) from ".DB_PREFIX."youhui ";

		//$sql = "select id,merchant_id,title,content,merchant_logo,create_time,merchant_xpoint,merchant_ypoint,merchant_api_address,image_1 from ".FDB::table("merchant_youhui");
		$sql = "select id, supplier_id as merchant_id,name as title,list_brief as content,icon as merchant_logo,create_time,xpoint,ypoint,address as api_address,icon as image_1 from ".DB_PREFIX."youhui ";
		$now = get_gmtime();

		$where = "1 = 1 and is_effect = 1 and (end_time = 0 or end_time > ".$now.") and  (brand_id in (".$brand_ids.") or supplier_id in (".$merchant_ids."))";

		$sql_count.=" where ".$where;
		$sql.=" where ".$where;
		$sql.=" order by create_time desc limit ".$limit;

		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);

		//print_r($sql); exit;
		$list = $GLOBALS['db']->getAll($sql);
		$youhui_list = array();
		foreach($list as $item){

			$youhui_list[] = m_youhuiItem($item);

		}

		$root['item'] = $youhui_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		$root['now'] = $now;

		output($root);
	}
}
?>