<?php
class merchantlist
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
		
		$city_id = intval($GLOBALS['request']['city_id']);
		$quan_id = intval($GLOBALS['request']['quan_id']);
		$cate_id = intval($GLOBALS['request']['cate_id']);
		$brand_id = intval($GLOBALS['request']['brand_id']);
		$keyword = strim($GLOBALS['request']['keyword']);
		$page = intval($GLOBALS['request']['page']); //分页
		
		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
		$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint
		
		$page=$page==0?1:$page;
		
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		
		if($xpoint>0)
		{		
			$pi = 3.14159265;  //圆周率
			$r = 6378137;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance ";
			if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
			$condition = " and  ypoint > $ybottom and ypoint < $ytop and xpoint > $xleft and xpoint < $xright ";
			$orderby = " order by distance asc ";
		}
		else
		{
			$field_append = "";
			$orderby = " ";
		}
		
				
		$sql_count = "select count(*) from ".DB_PREFIX."supplier_location". " as a";
		
		$sql = "select a.supplier_id as id,a.name,a.mobile_brief as brief,a.tel,a.preview as logo,a.dp_count as comment_count,a.xpoint,a.ypoint,a.address as api_address, 0 as is_dy $field_append from   ".DB_PREFIX."supplier_location as a ";
		
		$where = "1 = 1 ";
		
		if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$where .= " and city_id in (".implode(",",$ids).")";
				}
			}
		
		if ($quan_id > 0){
			$sql_q = "select name from ".DB_PREFIX."area where id = ".intval($quan_id);
			$q_name = $GLOBALS['db']->getOne($sql_q);
			$q_name_unicode = str_to_unicode_string($q_name);
			$where .=" and (match(a.locate_match) against('".$q_name_unicode."' IN BOOLEAN MODE))";
			//$where .= " and a.locate_match = $quan_id";
		}
		
		if ($cate_id > 0)
		$where .= " and a.deal_cate_id = $cate_id";
				
		if ($brand_id > 0)
		  $where .= " and a.supplier_id = $brand_id";
				
		if($keyword){
	   		$GLOBALS['tmpl']->assign("keyword",$keyword);
	   		$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kw[$k] = str_to_unicode_string($item);
			}
			$kw_unicode = implode(" ",$kw);
			//有筛选
			$where .=" and (match(a.name_match,a.locate_match,a.deal_cate_match,a.tags_match) against('".$kw_unicode."' IN BOOLEAN MODE) or name like '%".$keyword."%')";
	   }
	   $where.=$condition;
		$sql_count.=" where ".$where;
		$sql.=" where ".$where;
		$sql.=$orderby;
		$sql.=" limit ".$limit;		
				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);


		
		$list = $GLOBALS['db']->getAll($sql);
	
		$merchant_list = array();
		foreach($list as $item){			
			$item = m_merchantItem($item);			
			$merchant_list[] = $item;
			
		}

		$root['item'] = $merchant_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		
		output($root);
	}
}
?>