<?php
//获取指定的分类列表
function get_yh_cate_tree($pid = 0 ,$extwhere)
{
	return load_auto_cache("cache_youhui_cate_tree",array("pid"=>$pid,"extwhere"=>$extwhere));
}

/**
 * 获取免费优惠券列表
 */
function get_free_youhui_list($limit, $cate_id=0, $where='',$orderby = '')
{		
			$time = get_gmtime();
			$time_condition = '  and (end_time = 0 or end_time > '.$time.' ) ';
	
			$count_sql = "select count(*) from ".DB_PREFIX."youhui where is_effect = 1 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."youhui where is_effect = 1 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and deal_cate_id in (".implode(",",$ids).")";
				$count_sql .= " and deal_cate_id in (".implode(",",$ids).")";
			}
				
			$city_id = intval($GLOBALS['deal_city']['id']);

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
			$sql.=" order by sort desc ";
			else
			$sql.=" order by ".$orderby;
	
			if($limit>0)
			$sql.=" limit ".$limit." ";
	
			$youhuis = $GLOBALS['db']->getAll($sql);		
			$youhuis_count = $GLOBALS['db']->getOne($count_sql);
			
	 		if($youhuis)
			{
				foreach($youhuis as $k=>$youhui)
				{					
					$durl = url("youhui","fdetail",array("id"=>$youhui['id']));					
					$youhui['url'] = $durl;			
					$youhui['print_url'] = url("youhui","fdetail#fprint",array("id"=>$youhui['id']));						
					$youhuis[$k] = $youhui;
				}
			}	
			$res = array('list'=>$youhuis,'count'=>$youhuis_count);	
	
		return $res;
}



/**
 * 获取商家店面列表
 */
function get_store_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true,$count=true)
{		
		$key = md5("STORE_LIST_".$limit.$cate_id.$where.$orderby);	
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
	
			if(strpos($orderby,"is_recommend")&&strpos($orderby,"is_verify")&&strpos($orderby,"dp_count"))
			{
				$key_sort = "search_idx5,sort_default";
			}else
			{			
				$key_sort = explode(" ",trim($orderby));
				$key_sort = trim($key_sort[0]);
				if($key_sort!='')
				$key_sort = "$key_sort";
			}
			static $supplier_total;
			if(empty($supplier_total))
				$supplier_total = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."supplier_location");
			if($supplier_total>50000)
			{
				$count_sql = "select count(*) from ".DB_PREFIX."supplier_location use index(search_idx5) where is_effect = 1 ";
				$sql = "select * from ".DB_PREFIX."supplier_location use index($key_sort) where is_effect = 1 ";
			}
			else
			{
				$count_sql = "select count(*) from ".DB_PREFIX."supplier_location where is_effect = 1 ";
				$sql = "select * from ".DB_PREFIX."supplier_location where is_effect = 1 ";
			}
			
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and deal_cate_id in (".implode(",",$ids).")";
				$count_sql .= " and deal_cate_id in (".implode(",",$ids).")";
			}
				

			//$city = get_current_deal_city();
			$city_id = $GLOBALS['deal_city']['id'];

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
			$sql.="  limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;


	
			$stores = $GLOBALS['db']->getAll($sql);		
			if($count)
			$stores_count = $GLOBALS['db']->getOne($count_sql);
			
	 		if($stores)
			{
				foreach($stores as $k=>$store)
				{			
					if($store['dp_group_point']=="")
					{						
						$store =	cache_store_point($store['id']);
					}
					
					if($store['tuan_youhui_cache']=="")
					{
						$store = recount_supplier_data_count($store['id'],"tuan");
						$store = recount_supplier_data_count($store['id'],"youhui");
					}
					$tuan_youhui_cache = unserialize($store['tuan_youhui_cache']);
					if($store['tuan_count']>0)
						$store['tuan'] = $tuan_youhui_cache['tuan'];
					
					if($store['youhui_count']>0)
						$store['youhui'] = $tuan_youhui_cache['youhui'];
					
					$durl = url("youhui","store#view",array("id"=>$store['id']));					
					$store['url'] = $durl;	
					$tags = array();
					if($store['tags']){
						$ttags = explode(" ",$store['tags']);
						foreach($ttags as $tv){
							$tags[$tv]['name'] =  $tv;
							$tags[$tv]['code'] = $tv;
						}
					}
					$store['tags_list'] = $tags;
					$stores[$k] = $store;
					$group_point = unserialize($store['dp_group_point']);
					$stores[$k]['group_point'] = $group_point;
				}
			}	
			$res = array('list'=>$stores,'count'=>$stores_count);	
			if($cached)
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}


/**
 * 获取购买优惠券列表
 */
function get_youhui_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true)
{		
		$key = md5("YOUHUI_".$limit.$cate_id.$where.$orderby);	
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
			$time_condition = '  and (end_time = 0 or end_time > '.$time.') ';
	
			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 2 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 2 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_cate_ids",array("cate_id"=>$cate_id));

				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}
				

			$city = get_current_deal_city();
			$city_id = $city['id'];

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
					$module = "ydetail";
					
					if($deal['uname']!='')
					$durl = url("youhui",$module,array("id"=>$deal['uname']));
					else
					$durl = url("youhui",$module,array("id"=>$deal['id']));
					
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
 * 搜索优惠列表
 */
function search_youhui_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true, $join_str = '',$city_id=0)
{		
	
		if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
		$key = md5("YOUHUI_".$limit.$cate_id.$where.$orderby.$join_str.$city_id);	
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
			$time_condition = '  and (end_time = 0 or end_time > '.$time.') ';
	
			$count_sql .= " where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 2 ".$time_condition;
			$sql .= " where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 2 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and d.cate_id in (".implode(",",$ids).")";
				$count_sql .= " and d.cate_id in (".implode(",",$ids).")";
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
			$sql.=" order by d.sort desc ";
			else
			$sql.=" order by ".$orderby;
			
			if($limit>0)
			$sql.=" limit ".$limit." ";
			
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
					$durl = url("youhui","ydetail",array("id"=>$deal['uname']));
					else
					$durl = url("youhui","ydetail",array("id"=>$deal['id']));
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
 * 获取指定的优惠券
 */
function get_youhui($id=0,$preview=0)
{		
		$time = get_gmtime();
		if($id>0)
		{
			syn_deal_status($id);
			if($preview==0)
			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_effect = 1 and is_delete = 0 and is_shop = 2 ");
			else 
			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_delete = 0 and is_shop = 2 ");
			
		}		
		if($deal)
		{
			
			//格式化数据
			$deal['origin_price_format'] = format_price($deal['origin_price']);
			$deal['current_price_format'] = format_price($deal['current_price']);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
			$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
			else
			$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0)
			$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);

			$deal['discount'] = round($deal['discount'],2);
			
			$deal['save_price_format'] = format_price($deal['save_price']);
				
			//团购图片集
			$img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
			foreach($img_list as $k=>$v)
			{
				$img_list[$k]['origin_img'] = preg_replace("/\/big\//","/origin/",$v['img']); 
			}
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
			$gurl = url("shop","goods",array("id"=>$deal['uname']));
			else
			$gurl = url("shop","goods",array("id"=>$deal['id']));
			
			$deal['share_url'] = $gurl;
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
 * 搜索活动列表
 */
function search_event_list($limit, $cate_id=0, $city_id=0, $where='',$orderby = '',$cached = false)
{		
		$key = md5("EVENT_".$limit.$cate_id.$city_id.$where.$orderby);	
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
			
			$count_sql = "select count(*) from ".DB_PREFIX."event " ;
			$sql = "select * from ".DB_PREFIX."event ";

	
			$count_sql .= " where is_effect = 1 ";
			$sql .= " where is_effect = 1  ";
			
			if($cate_id>0)
			{
				
				$sql .= " and cate_id = ".$cate_id." ";
				$count_sql .= " and cate_id = ".$cate_id." ";
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
			$sql.=" order by is_recommend desc,sort desc  ";
			else
			$sql.=" order by is_recommend desc,".$orderby."  ";
			
			if($limit != "")
			{
				$sql.=" limit ".$limit." ";
			}
						
			$events = $GLOBALS['db']->getAll($sql);				
			$events_count = $GLOBALS['db']->getOne($count_sql);
			
			$res = array('list'=>$events,'count'=>$events_count);	
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}


?>