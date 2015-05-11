<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
require_once APP_ROOT_PATH.'app/Lib/page.php';
class tuanModule extends YouhuiBaseModule
{
	public function index()
	{		
		convert_req($_REQUEST);	
		$_REQUEST['cid'] = intval($_REQUEST['cid']);
		$keyword = addslashes(htmlspecialchars(trim($_REQUEST['keyword'])));
		$GLOBALS['tmpl']->assign("keyword",$keyword);	
		
		$url_param = array(
				"cid"	=> $_REQUEST['cid'],
				"aid"	=>	intval($_REQUEST['aid']),
				"tid"	=>	intval($_REQUEST['tid']),
				"qid"	=>	intval($_REQUEST['qid']),
				"min_price" => doubleval($_REQUEST['min_price']),
				"max_price"	=> doubleval($_REQUEST['max_price']),
				"keyword"	=> $keyword
			);			
		if(intval($_REQUEST['is_redirect'])==1)
		{
			app_redirect(url("youhui","tuan",$url_param));
		}

					
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['TUAN'],'url'=>url("youhui","tuan#index"));
							
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		//输出当前的site_nav
				
		//输出热卖
		$res =load_auto_cache("recommend_hot_sale_list");
		$GLOBALS['tmpl']->assign("hot_sale_list",$res['list']);
		
		$seo_title = $GLOBALS['lang']['TUAN'];
		$seo_keyword = $GLOBALS['lang']['TUAN'];
		$seo_description = $GLOBALS['lang']['TUAN'];
		
		//		
			$city_id = intval($GLOBALS['deal_city']['id']);
			//分页
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");	
				
			
			$id = intval($_REQUEST['cid']);
			if($id==0)
			$uname = addslashes(trim($_REQUEST['id']));
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where id = ".$id." or (uname = '".$uname."' and uname <> '')");
					
			$condition = " is_shop = 0 ";  //条件
					
			$tp_url_param = $url_param;
			unset($tp_url_param['min_price']);
			unset($tp_url_param['max_price']);
			unset($tp_url_param['keyword']);
			
			$sub_nav[] = array(
				"name"	=>	$GLOBALS['lang']['FREE_YOUHUI'],	
				"url"	=> url("youhui","fcate",$tp_url_param),
				"current"	=>	0,
			);
			$sub_nav[] = array(
				"name"	=>	$GLOBALS['lang']['NEED_BUY_YOUHUI'],	
				"url"	=> url("youhui","ycate",$tp_url_param),
				"current"	=>	0,
			);
			$sub_nav[] = array(
				"name"	=>	$GLOBALS['lang']['TUAN'],	
				"url"	=> url("youhui","tuan",$tp_url_param),
				"current"	=>	1,
			);
			$GLOBALS['tmpl']->assign("sub_nav",$sub_nav); 
			
			$GLOBALS['tmpl']->assign("url_param",$tp_url_param); //将变量输出到模板
			
			$ids = load_auto_cache("deal_sub_cate_ids",array("cate_id"=>intval($cate_item['id'])));
			
			$cache_param = array("city_id"=>$city_id,"cid"=>$cate_item['id'],"tid"=>$url_param['tid'],"aid"=>$url_param['aid'],"qid"=>$url_param['qid']);
			$result = load_auto_cache("ytuan_filter_nav_cache",$cache_param);
			
			$append_seo = "";
			
			//输出大区
			$area_id = intval($_REQUEST['aid']);	
			$quan_id = intval($_REQUEST['qid']);
			if($area_id>0)
			{
				$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area_id);	
				$append_seo = $area_name;
				if($quan_id>0)
				{
					
					$kw_unicode = str_to_unicode_string($area_name);
					//有筛选
					$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";				

				}
				else
				{
					$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
					$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
					$unicode_quans = array();
					foreach($quan_list as $k=>$v){
						$unicode_quans[] = str_to_unicode_string($v['name']);
					}
					$kw_unicode = implode(" ", $unicode_quans);
					$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
				}	
			}
			
			$GLOBALS['tmpl']->assign("area_list",$result['area_list']);
			
			if($area_id>0)
			{
				//输出商圈
				if($quan_id>0)
				{
						$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);				
						$kw_unicode = str_to_unicode_string($area_name);
						//有筛选
						$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";	
						$append_seo = $append_seo.$area_name;			
				}
				
				$GLOBALS['tmpl']->assign("quan_list",$result['quan_list']);
			}
			
			//输出分类
			$cate_id = $cate_item['id'];	
				
			$GLOBALS['tmpl']->assign("cate_list",$result['cate_list']);	
		
			
			//输出小分类
			$deal_type_id = intval($_REQUEST['tid']);	
			$deal_cate_id = $cate_item['id'];
			$deal_quan_id = $area_id;
			if($deal_cate_id>0)
			{
				$GLOBALS['tmpl']->assign("scate_list",$result['scate_list']);
				if($append_seo!="")
				$append_seo.=" - ";
				$append_seo.=$cate_item['name'];
			}
			
			
			
			//输出价格区间
			$min_price = doubleval($_REQUEST['min_price']);
			$max_price = doubleval($_REQUEST['max_price']);
			
			$GLOBALS['tmpl']->assign("min_price",$min_price);
			$GLOBALS['tmpl']->assign("max_price",$max_price);
			if($min_price>0)
			{
				$condition.=" and current_price >= ".$min_price;
			}
			if($max_price>0)
			{
				$condition.=" and current_price <= ".$max_price;
			}
			
			$sort_field = es_cookie::get("shop_sort_field")?es_cookie::get("shop_sort_field"):"sort";
			$sort_type = es_cookie::get("shop_sort_type")?es_cookie::get("shop_sort_type"):"desc";
			if($sort_field!="update_time"&&$sort_field!="current_price"&&$sort_field!="buy_count"&&$sort_field!="avg_point"&&$sort_field!="sort")
			{
				$sort_field = "sort";
			}
			if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
			$GLOBALS['tmpl']->assign('sort_field',$sort_field);
			$GLOBALS['tmpl']->assign('sort_type',$sort_type);
			
			if($deal_type_id>0)
			{
				$deal_type_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate_type where id = ".$deal_type_id);
				$deal_type_name_unicode = str_to_unicode_string($deal_type_name);
				$condition .= " and (match(deal_cate_match) against('".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
				$append_seo.=$deal_type_name;
			}
			
			$seo_title = $append_seo.$seo_title;
		    $seo_keyword = $append_seo.$seo_keyword;
		    $seo_description = $append_seo.$seo_keyword;
			if($keyword)
			{				
					$kws_div = div_str($keyword);
					foreach($kws_div as $k=>$item)
					{
						$kw[$k] = str_to_unicode_string($item);
					}
					$ukeyword = implode(" ",$kw);
					$condition.=" and (match(name_match) against('".$ukeyword."'  IN BOOLEAN MODE)  or name like '%".$keyword."%') ";
					$seo_title = $keyword." - ".$seo_title;
			}
			
			
			$result = get_deal_list($limit,intval($cate_item['id']),$city_id,array(DEAL_ONLINE,DEAL_NOTICE),$condition,$sort_field." ".$sort_type);
			
	
			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			$GLOBALS['tmpl']->assign("cate_id",$cate_item['id']);
		
		//
				
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword);
		$GLOBALS['tmpl']->assign("page_description",$seo_description);		

		$GLOBALS['tmpl']->display("youhui_tuan.html");
			
	}	
}
?>