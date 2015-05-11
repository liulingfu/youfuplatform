<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


function get_message_list($limit,$where='')
{
	$city_id = intval($GLOBALS['deal_city']['id']);
	$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				
	if($ids)
	{
	$sql = "select * from ".DB_PREFIX."message where pid = 0 and city_id in( ".implode(",",$ids).")";
	$sql_count = "select count(*) from ".DB_PREFIX."message where pid = 0 and city_id in( ".implode(",",$ids).")";
	}
	else
	{
		$sql = "select * from ".DB_PREFIX."message where pid = 0 ";
		$sql_count = "select count(*) from ".DB_PREFIX."message where pid = 0 ";
	
	}
	if($where!='')
	{
		$sql .= " and ".$where;
		$sql_count .=  " and ".$where;
	}
	
	$sql.=" order by create_time desc ";
	$sql.=" limit ".$limit;

	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	
	return array('list'=>$list,'count'=>$count);
}

function get_message_list_shop($limit,$where='')
{
				
	$sql = "select * from ".DB_PREFIX."message where pid = 0 ";
	$sql_count = "select count(*) from ".DB_PREFIX."message where pid = 0 ";
	if($where!='')
	{
		$sql .= " and ".$where;
		$sql_count .=  " and ".$where;
	}
	
	$sql.=" order by create_time desc ";
	$sql.=" limit ".$limit;
	
	$list = $GLOBALS['db']->getAll($sql);
	foreach($list as $k=>$v)
	{
		if($v['rel_table']=='deal'&&$v['rel_id']>0)
		$list[$k]['rel_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX.$v['rel_table']." where id = ".$v['rel_id']);
	}
	$count = $GLOBALS['db']->getOne($sql_count);
	
	return array('list'=>$list,'count'=>$count);
}
?>