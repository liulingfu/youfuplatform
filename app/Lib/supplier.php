<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

function get_supplier_list($limit,$cate_id=0,$city_id=0,$where = '')
{
	$condition = " is_effect = 1 ";
	
	if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
				$condition .= " and cate_id in (".implode(",",$ids).")";
			}
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{			
				$ids =  load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				$condition .= " and city_id in (".implode(",",$ids).")";
			}
	
	if($where != '')
		{
			$condition.=" and ".$where;
		}
	
	$sql = "select * from ".DB_PREFIX."supplier where ".$condition." order by sort desc limit ".$limit;
	$count_sql = "select count(*) from ".DB_PREFIX."supplier where ".$condition;
	
	$suppliers = $GLOBALS['db']->getAll($sql);		
	$suppliers_count = $GLOBALS['db']->getOne($count_sql);
	
	foreach($suppliers as $k=>$v)
	{
		$suppliers[$k]['url'] = url("shop","brand",array("id"=>$v['id']));
		$main_location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$v['id']." and is_main = 1");
		$suppliers[$k]['tel'] = $main_location['tel'];
		$suppliers[$k]['address'] = $main_location['address'];
		$suppliers[$k]['contact'] = $main_location['contact'];
		$suppliers[$k]['brief'] = $main_location['brief'];
		$suppliers[$k]['deal_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where supplier_id = ".$v['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1 and time_status > 0");
		$max_price = $GLOBALS['db']->getOne("select max(current_price) from ".DB_PREFIX."deal where supplier_id = ".$v['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1");
		$min_price = $GLOBALS['db']->getOne("select min(current_price) from ".DB_PREFIX."deal where supplier_id = ".$v['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1");
		
		if($max_price == $min_price)
		$suppliers[$k]['deal_price'] = format_price($max_price);
		else
		$suppliers[$k]['deal_price'] = format_price($min_price)." - ".format_price($max_price);
		$suppliers[$k]['comment_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$v['id']);
		$suppliers[$k]['comment1_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$v['id']." and point = 1"); //差
		$suppliers[$k]['comment2_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$v['id']." and point = 2"); //中
		$suppliers[$k]['comment3_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$v['id']." and point = 3"); //好
	}
	
	return array('list'=>$suppliers,'count'=>$suppliers_count);	
}

function get_supplier_info($id)
{
	$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where is_effect = 1 and id=".$id);
	if($supplier_info)
	{
		
		$main_location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_info['id']." and is_main = 1");
		$supplier_info['tel'] = $main_location['tel'];
		$supplier_info['address'] = $main_location['address'];
		$supplier_info['contact'] = $main_location['contact'];
		$supplier_info['brief'] = $main_location['brief'];
		$supplier_info['deal_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where supplier_id = ".$supplier_info['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1 and time_status > 0");
		$max_price = $GLOBALS['db']->getOne("select max(current_price) from ".DB_PREFIX."deal where supplier_id = ".$supplier_info['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1");
		$min_price = $GLOBALS['db']->getOne("select min(current_price) from ".DB_PREFIX."deal where supplier_id = ".$supplier_info['id']." and is_effect = 1 and is_delete = 0 and buy_type <> 1");
		
		if($max_price == $min_price)
		$supplier_info['deal_price'] = format_price($max_price);
		else
		$supplier_info['deal_price'] = format_price($min_price)." - ".format_price($max_price);
		$supplier_info['comment_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$supplier_info['id']);
		$supplier_info['comment1_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$supplier_info['id']." and point = 1"); //差
		$supplier_info['comment2_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$supplier_info['id']." and point = 2"); //中
		$supplier_info['comment3_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table='supplier' and rel_id = ".$supplier_info['id']." and point = 3"); //好
		$supplier_info['location_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_info['id']);
		
	}
	return $supplier_info;
}

?>