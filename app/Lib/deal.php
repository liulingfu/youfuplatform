<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

define("DEAL_ONLINE",1); //进行中
define("DEAL_HISTORY",2); //过期
define("DEAL_NOTICE",3); //未上线


/**
 * 获取指定的团购产品
 */
function get_deal($id=0,$cate_id=0,$city_id=0)
{		

		$time = get_gmtime();
		if($id>0)
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_effect = 1 and is_delete = 0 and (".$time.">= begin_time or begin_time = 0 or notice = 1) ");
		if($id==0)  //有ID时不自动获取
		{			
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
			if($cate_id>0)
			{
				
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				$sql .= " and city_id in (".implode(",",$ids).")";
			}

			
			
			$sql.=" order by sort desc";
			$deal = $GLOBALS['db']->getRow($sql);
			
		}
		
		if($deal)
		{
			if($deal['time_status']==0 && $deal['begin_time']==0 || $deal['begin_time']<get_gmtime())
			{
				syn_deal_status($deal['id']);
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
			}
			
			//格式化数据
			$deal['begin_time_format'] = to_date($deal['begin_time']);
			$deal['end_time_format'] = to_date($deal['end_time']);
			$deal['origin_price_format'] = format_price($deal['origin_price']);
			$deal['current_price_format'] = format_price($deal['current_price']);
			$deal['fanli_price_format']=format_price($deal['origin_price']-$deal['current_price']);//2013-07-25@哥将添加
			$deal['success_time_format']  = to_date($deal['success_time']);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
			$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
			else
			$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0)
			$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);

			$deal['discount'] = round($deal['discount'],2);
			
			$deal['save_price_format'] = format_price($deal['save_price']);
	
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
			
			
			$deal['success_time_tip'] = sprintf($GLOBALS['lang']['SUCCESS_TIME_TIP'],$deal['success_time_format'],$deal['min_bought']);
			
			//团购图片集
			$img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
			$deal['image_list'] = $img_list;
			
			//商户信息
			$deal['supplier_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($deal['supplier_id']));
			$deal['supplier_address_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");

			//品牌信息
			$deal['brand_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".intval($deal['brand_id']));
			
			//属性列表
			$deal_attrs_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_attr where deal_id = ".intval($deal['id'])." order by id asc");
			if($deal_attrs_res)
			{
				foreach($deal_attrs_res as $k=>$v)
				{
					$deal_attr[$v['goods_type_attr_id']]['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."goods_type_attr where id = ".intval($v['goods_type_attr_id']));
					$deal_attr[$v['goods_type_attr_id']]['attrs'][] = $v;
				}
				$deal['deal_attr_list'] = $deal_attr;
			}
			
			if($deal['uname']!='')
			$durl = url("tuan","deal",array("id"=>$deal['uname']));
			else
			$durl = url("tuan","deal",array("id"=>$deal['id']));
			$deal['share_url'] = get_domain().$durl;
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
			
			//查询抽奖号
			$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
		}
		return $deal;
	
}


/**
 * 获取正在团购的产品列表
 */
function get_deal_list($limit,$cate_id=0,$city_id=0, $type=array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE), $where='',$orderby = '' , $quan_id=0)
{		
		
		$time = get_gmtime();
		$time_condition = ' and is_shop = 0 and ( 1<>1 ';
		if(in_array(DEAL_ONLINE,$type))
		{			
			//进行中的团购
			$time_condition .= " or ((".$time.">= begin_time or begin_time = 0) and (".$time."< end_time or end_time = 0) and buy_status <> 2) ";
		}
		
		if(in_array(DEAL_HISTORY,$type))
		{
			//往期团购
			$time_condition .= " or ((".$time.">=end_time and end_time <> 0) or buy_status = 2) ";
		}
		if(in_array(DEAL_NOTICE,$type))
		{			
			//预告
			$time_condition .= " or ((".$time." < begin_time and begin_time <> 0 and notice = 1)) ";
		}
		
		$time_condition .= ')';
		
			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
				}
			}
		
		if($quan_id > 0)
		{
			$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$quan_id));
			$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['deal_city']['id']));
			
			//$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
			$unicode_quans = array();
			foreach($ids as $k=>$v){
				$unicode_quans[] = str_to_unicode_string($area_result[$v]['name']);
			}
			$kw_unicode = implode(" ", $unicode_quans);
			$sql .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			$count_sql .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
		}
		
		if($where != '')
		{
			$sql.=" and ".$where;
			$count_sql.=" and ".$where;
		}
		
		if($orderby=='')
		$sql.=" order by sort desc limit ".$limit;
		else
		$sql.=" order by ".$orderby." limit ".$limit;
		

		$deals = $GLOBALS['db']->getAll($sql);		
		$deals_count = $GLOBALS['db']->getOne($count_sql);
//		echo $count_sql;
 		if($deals)
		{
			foreach($deals as $k=>$deal)
			{
				//团购图片集
				$img_list = array();
				$img_list[] = array('img'=>$deal['img']);
				$deal['image_list'] = $img_list;
			
				//格式化数据
				$deal['begin_time_format'] = to_date($deal['begin_time']);
				$deal['end_time_format'] = to_date($deal['end_time']);
				$deal['origin_price_format'] = format_price($deal['origin_price']);
				$deal['current_price_format'] = format_price($deal['current_price']);
				$deal['fanli_price_format']=format_price($deal['origin_price']-$deal['current_price']);//2013-07-25@哥将添加
				$deal['success_time_format']  = to_date($deal['success_time']);
				
				if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
				$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
				else
				$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
				{
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);					
				}
				
				$deal['discount'] = round($deal['discount'],2);
				
				if($deal['uname']!='')
				$durl = url("tuan","deal",array("id"=>$deal['uname']));
				else
				$durl = url("tuan","deal",array("id"=>$deal['id']));				
				$deal['share_url'] = get_domain().$durl;
				
				
				if($GLOBALS['user_info'])
					{
						if(app_conf("URL_MODEL")==0)
						{
							$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
						}
						else
						{
							$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
						}
				}	
			
				

				$deal['save_price_format'] = format_price($deal['save_price']);
				if($deal['uname']!='')
				$durl = url("tuan","deal",array("id"=>$deal['uname']));
				else
				$durl = url("tuan","deal",array("id"=>$deal['id']));
				$deal['url'] = $durl;
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				//查询抽奖号
				if($deal['is_lottery']==1)
				$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
				$deals[$k] = $deal;
			}
		}				
		return array('list'=>$deals,'count'=>$deals_count);	
}



/**
 * 获取指定的团购产品
 */
function get_deal_show($id=0,$cate_id=0,$city_id=0,$preview=0)
{		
		$time = get_gmtime();
		
		if($id==0)
		{			
			$sql = "select * from ".DB_PREFIX."deal where is_shop = 0 and is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
			if($cate_id>0)
			{
				
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			
			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));	
				if($ids)			
				$sql .= " and city_id in (".implode(",",$ids).")";
			}		
			
			
			$sql.=" order by sort desc";
			$deal = $GLOBALS['db']->getRow($sql);
			
		}
		
		$deal  = syn_deal_status($id);
		
		if($deal)
		{
			$static_deal = load_auto_cache("static_goods_info",array("id"=>$deal['id']));
			
			foreach($static_deal as $k=>$v)
			{
				$deal[$k] = $v;
			}			
			
			
			$deal['success_time_tip'] = sprintf($GLOBALS['lang']['SUCCESS_TIME_TIP'],$deal['success_time_format'],$deal['min_bought']);
			$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
			$deal['current_bought'] = $deal['buy_count'];
			if($deal['buy_status']==0) //未成功
			{
				$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
			}
			
			if($deal['uname']!='')
			$durl = url("tuan","deal",array("id"=>$deal['uname']));
			else
			$durl = url("tuan","deal",array("id"=>$deal['id']));
			$deal['share_url'] = get_domain().$durl;

			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
			
			//查询抽奖号
			$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
		}
		return $deal;
	
}


/**
 * 获取正在团购的产品列表
 */
function get_deal_list_show($limit,$cate_id=0,$city_id=0, $type=array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE), $where='',$orderby = '')
{		
		
		$time = get_gmtime();
		$time_condition = '  and is_shop = 0 and ( 1<>1 ';
		if(in_array(DEAL_ONLINE,$type))
		{
			
			//进行中的团购
			$time_condition .= " or ((".$time.">= begin_time or begin_time = 0) and (".$time."<end_time or end_time = 0) and buy_status <> 2) ";
		}
		if(in_array(DEAL_HISTORY,$type))
		{
			//往期团购
			$time_condition .= " or ((".$time.">=end_time and end_time <> 0) or buy_status = 2) ";
		}
		if(in_array(DEAL_NOTICE,$type))
		{			
			//预告
			$time_condition .= " or ((".$time." < begin_time and begin_time <> 0 and notice = 1)) ";
		}
		
		$time_condition .= ')';
		
			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			if($cate_id>0)
			{
				$ids = load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{				
				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
				}
			}
		
		if($where != '')
		{
			$sql.=" and ".$where;
			$count_sql.=" and ".$where;
		}
		
		if($orderby=='')
		$sql.=" order by sort desc ";
		else
		$sql.=" order by ".$orderby;
		
		if($limit>0)
		{
			$sql.=" limit ".$limit." ";
		}
		else
		{
			if(count(explode(",",$limit))>1)
			{
				$sql.=" limit ".$limit." ";
			}	
		}

		$deals = $GLOBALS['db']->getAll($sql);		
		$deals_count = $GLOBALS['db']->getOne($count_sql);
		
 		if($deals)
		{
			foreach($deals as $k=>$deal)
			{
			
				//格式化数据
				$deal['begin_time_format'] = to_date($deal['begin_time']);
				$deal['end_time_format'] = to_date($deal['end_time']);
				$deal['origin_price_format'] = format_price($deal['origin_price']);
				$deal['current_price_format'] = format_price($deal['current_price']);
				$deal['fanli_price_format']=format_price($deal['origin_price']-$deal['current_price']);//2013-07-25@哥将添加
				$deal['success_time_format']  = to_date($deal['success_time']);
				
				if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
				$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
				else
				$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
				{
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);					
				}
				
				$deal['discount'] = round($deal['discount'],2);



				$deal['save_price_format'] = format_price($deal['save_price']);
				
				if($deal['uname']!='')
				$durl = url("tuan","deal",array("id"=>$deal['uname']));
				else
				$durl = url("tuan","deal",array("id"=>$deal['id']));				
				$deal['url'] = $durl;
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
				$deals[$k] = $deal;
			}
		}				
		return array('list'=>$deals,'count'=>$deals_count);	
}



/**
 * 获取商城首页的今日团购产品
 */
function get_deal_show_shop()
{		
		$time = get_gmtime();
		$sql = "select * from ".DB_PREFIX."deal where is_shop = 0 and is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
		
		$city_name = $GLOBALS['deal_city']['name'];
		$city_name_unicode = str_to_unicode_string($city_name);
		$sql .=" and (match(locate_match) against('".$city_name_unicode."' IN BOOLEAN MODE))";
		
		$sql.=" order by sort desc";
		$deal = $GLOBALS['db']->getRow($sql);
				
		/* if($deal)
		{
			if($deal['time_status']==0 && $deal['begin_time']==0 || $deal['begin_time']<get_gmtime())
			{
				syn_deal_status($deal['id']);
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_shop = 0 and id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
			}			
			//格式化数据		
		} */
		return $deal;
	
}
?>