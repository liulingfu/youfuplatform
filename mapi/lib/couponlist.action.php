<?php
class couponlist{
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
			$status = intval($GLOBALS['request']['tag']);
	
			$page = intval($GLOBALS['request']['page']); //分页
			$page=$page==0?1:$page;
							
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;

			$ext_condition = '';
			$now = get_gmtime();
			if($status==1)//即将过期
			{
				$ext_condition = " and confirm_time = 0 and end_time > 0 and end_time > ".$now." and end_time - ".$now." < ".(72*3600);				
			}
			if($status==2)//未使用
			{
				$ext_condition = " and confirm_time = 0 and (end_time = 0 or (end_time>0 and end_time > $now))";
			}
			if($status==3)//已失效
			{
				$ext_condition = " and (confirm_time <> 0 or (end_time < $now and end_time > 0))";
			}
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition." order by order_id desc limit ".$limit);
			//echo "select * from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition." order by order_id desc limit ".$limit; exit;
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition);
			
			$page_total = ceil($count/$page_size);
			
			
			
			//$root = array();
			//$root['return'] = 1;
			
			//补充字段
			foreach($list as $k=>$v)
			{
				$list[$k]['createTime'] = "";
				if($v['end_time']>0)
				$list[$k]['endTime'] = to_date($list[$k]['end_time'],"Y-m-d");
				else 
				$list[$k]['endTime'] = "无限时";
				if($list[$k]['confirm_time']>0)
				$list[$k]['useTime'] = to_date($list[$k]['confirm_time'],"Y-m-d");
				else
				$list[$k]['useTime'] = "";
				$list[$k]['beginTime'] = "";
				//$list[$k]['dealIcon'] = get_abs_img_root(make_img($GLOBALS['db']->getOne("select img from ".DB_PREFIX."deal where id = ".$v['deal_id']),0));
				$list[$k]['dealIcon'] = get_abs_img_root(get_spec_image($GLOBALS['db']->getOne("select img from ".DB_PREFIX."deal where id = ".$v['deal_id']),160,160,1));
				if($v['end_time']>0)
				$list[$k]['lessTime'] = $v['end_time'] - get_gmtime();
				else
				$list[$k]['lessTime'] = "永久";
				
				$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal where id = ".$v['deal_id']));
				$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");
				
				$list[$k]['spName'] = $supplier_info['name']?$supplier_info['name']:"";
				$list[$k]['spTel'] = $supplier_info['tel']?$supplier_info['tel']:"";
				$list[$k]['spAddress'] = $supplier_info['address']?$supplier_info['address']:"";
		
				$list[$k]['couponSn'] = $v['sn'];
				$list[$k]['couponPw'] = $v['password'];
				$list[$k]['dealName'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$v['order_deal_id']);
			}
			
			$root['item'] = $list;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		
				
			output($root);
		}
		else
		{
			$root['user_login_status'] = 0;		
		}		
	
		output($root);
	}
}
?>