<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


//获取指定的分类列表
function get_cate_tree($pid = 0,$is_all = 0)
{
	return load_auto_cache("cache_shop_cate_tree",array("pid"=>$pid,"is_all"=>$is_all));
}

//获取指定的文章分类列表
function get_acate_tree($pid = 0)
{
	return load_auto_cache("cache_shop_acate_tree",array("pid"=>$pid));
}

define("DEAL_ONLINE",1); //进行中
define("DEAL_HISTORY",2); //过期
define("DEAL_NOTICE",3); //未上线


/**
 * 获取指定的产品
 */
function get_goods($id=0,$preview=0)
{		

		static $deal;
		if($deal)return $deal;
		$deal = syn_deal_status($id);
		if($preview)
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_delete = 0 ");	
		
		if($deal)
		{			
			$static_deal = load_auto_cache("static_goods_info",array("id"=>$deal['id']));
			foreach ($static_deal as $k=>$v)
			{
				$deal[$k] = $v;
			}
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
		
			//开始获取处理库存
			$deal['stock'] = $deal['max_bought'] - $deal['buy_count'];
		}
		
		return $deal;
}


/**
 * 获取产品列表
 */
function get_goods_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true,$city_id=0)
{		
		if($city_id==0)
		{
				$city = get_current_deal_city();
				$city_id = $city['id'];
		}
		$key = md5($limit.$cate_id.$where.$orderby.$city_id);	
		if($cached)
		{				
			$res = $GLOBALS['cache']->get($key);
		}
		else
		{
			$res = false;
		}
		if($res===false)
		{
			$time = get_gmtime();
			$time_condition = '  and (is_shop=1  or shop_cate_id <> 0 ) ';
	
			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids = load_auto_cache("shop_sub_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and shop_cate_id in (".implode(",",$ids).")";
				$count_sql .= " and shop_cate_id in (".implode(",",$ids).")";
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
			$sql.=" order by sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;
	
			$deals = $GLOBALS['db']->getAll($sql);		
			$deals_count = $GLOBALS['db']->getOne($count_sql);
			
	 		if($deals)
			{
				foreach($deals as $k=>$deal)
				{					
					if($deal['buy_type']==1)
					$module = "exchange";
					else
					$module = "goods";
					
					if($deal['uname']!='')
					$durl = url("shop",$module,array("id"=>$deal['uname']));
					else
					$durl = url("shop",$module,array("id"=>$deal['id']));
					
					$deal['url'] = $durl;					
					$deals[$k] = $deal;
				}
			}	
			$res = array('list'=>$deals,'count'=>$deals_count);	
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}



/**
 * 获取产品列表
 */
function search_goods_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true, $join_str = '')
{		
		$key = md5($limit.$cate_id.$where.$orderby.$join_str);	
		if($cached)
		{				
			$res = $GLOBALS['cache']->get($key);
		}
		else
		{
			$res = false;
		}
		if($res===false)
		{
			
			$count_sql = "select count(*) from ".DB_PREFIX."deal as d" ;
			$sql = "select d.* from ".DB_PREFIX."deal as d ";
			
			if($join_str!='')
			{
				$count_sql.=$join_str;
				$sql.=$join_str;
			}
			
			$time = get_gmtime();
			$time_condition = '  and (d.is_shop=1  or d.shop_cate_id <> 0 ) ';
	
			$count_sql .= " where d.is_effect = 1 and d.is_delete = 0 ".$time_condition;
			$sql .= " where d.is_effect = 1 and d.is_delete = 0 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids = load_auto_cache("shop_sub_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and d.shop_cate_id in (".implode(",",$ids).")";
				$count_sql .= " and d.shop_cate_id in (".implode(",",$ids).")";
			}
				
			$city = get_current_deal_city();
			$city_id = $city['id'];

			if($city_id>0)
			{			
				$ids =  load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
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
			$sql.=" order by d.sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;
	
			
			$deals = $GLOBALS['db']->getAll($sql);				
			$deals_count = $GLOBALS['db']->getOne($count_sql);
			
	 		if($deals)
			{
				foreach($deals as $k=>$deal)
				{
				
					//格式化数据
					$deal['origin_price_format'] = format_price($deal['origin_price']);
					$deal['current_price_format'] = format_price($deal['current_price']);
	
					
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
					$durl = url("shop","goods",array("id"=>$deal['uname']));
					else
					$durl = url("shop","goods",array("id"=>$deal['id']));
					$deal['url'] = $durl;
					
					$deals[$k] = $deal;
				}
			}	
			$res = array('list'=>$deals,'count'=>$deals_count);	
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}

?>