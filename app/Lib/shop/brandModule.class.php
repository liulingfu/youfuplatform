<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class brandModule extends ShopBaseModule
{
	public function index()
	{			
		
		$id = intval($_REQUEST['id']);
		if($id==0)
		{
			$GLOBALS['tmpl']->caching = true;
			$cache_id  = md5(MODULE_NAME."list".intval($_REQUEST['p']).$GLOBALS['deal_city']['id']);	
			if (!$GLOBALS['tmpl']->is_cached('brand_index.html', $cache_id))
			{	
				//分页
				$page = intval($_REQUEST['p']);
				if($page==0)
				$page = 1;
				$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
				
				$brand_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."brand order by sort desc limit ".$limit);
				$brand_total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."brand");
				foreach($brand_list as $k=>$v)
				{
					$brand_list[$k]['url'] = url("shop","brand#index",array("id"=>$v['id']));
				}
				
				$page = new Page($brand_total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
				$p  =  $page->show();
				$GLOBALS['tmpl']->assign('pages',$p);
					
				//开始输出当前的site_nav			
				$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
				$site_nav[] = array('name'=>$GLOBALS['lang']['BRAND_INFO'],'url'=>url("shop","brand#index"));
				
				$GLOBALS['tmpl']->assign("site_nav",$site_nav);
				
				//输出当前的site_nav				
				$GLOBALS['tmpl']->assign("brand_list",$brand_list);
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['BRAND_INFO']);
				$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['BRAND_INFO']);
				$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['BRAND_INFO']);
			}
			$GLOBALS['tmpl']->display("brand_index.html",$cache_id);
		}
		else
		{
			$GLOBALS['tmpl']->caching = true;
			$cache_id  = md5(MODULE_NAME."show".trim($_REQUEST['id']).$GLOBALS['deal_city']['id']);	
			if (!$GLOBALS['tmpl']->is_cached('brand_info.html', $cache_id))
			{
				$brand_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".$id);
				if($brand_info)
				{
									
					//开始输出当前的site_nav			
				
					$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
					$site_nav[] = array('name'=>$GLOBALS['lang']['BRAND_INFO'],'url'=>url("shop","brand#index"));
					$site_nav[] = array('name'=>$brand_info['name'],'url'=>url("shop","brand#index",array("id"=>$brand_info['id'])));
					$GLOBALS['tmpl']->assign("site_nav",$site_nav);
					//输出当前的site_nav
					
					$GLOBALS['tmpl']->assign("page_title",$brand_info['name']);
					$GLOBALS['tmpl']->assign("page_keyword",$brand_info['name']);
					$GLOBALS['tmpl']->assign("page_description",$brand_info['name']);
					
				}
				else
				{
					app_redirect(APP_ROOT."/");
				}
			}
			$GLOBALS['tmpl']->display("brand_info.html",$cache_id);
		}
	}
	//加载品牌商品列表
	function load_brand_goods_list()
	{
		$id = intval($_REQUEST['id']);
		$brand_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".$id);
		if($brand_info)
		{
						$sort_field = es_cookie::get("shop_sort_field")?es_cookie::get("shop_sort_field"):"sort";						
						$sort_type = es_cookie::get("shop_sort_type")?es_cookie::get("shop_sort_type"):"desc";
						if($sort_field!="update_time"&&$sort_field!="current_price"&&$sort_field!="buy_count"&&$sort_field!="sort"&&$sort_field!="avg_point")
						{
							$sort_field = "sort";
						}
						if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
						$GLOBALS['tmpl']->assign('sort_field',$sort_field);
						$GLOBALS['tmpl']->assign('sort_type',$sort_type);
						if(es_cookie::get("list_type")===null)
							$list_type = app_conf("LIST_TYPE");
						else
							$list_type = intval(es_cookie::get("list_type"));
						$GLOBALS['tmpl']->assign("list_type",$list_type);
						
						$GLOBALS['tmpl']->assign("brand_info",$brand_info);
						
						//分页
						$page = intval($_REQUEST['p']);
						if($page==0)
						$page = 1;
						$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
						
						$result = search_goods_list($limit,0,'d.brand_id = '.$brand_info['id'].' and buy_type <> 1 ',"d.".$sort_field." ".$sort_type,false);
						
						$GLOBALS['tmpl']->assign("list",$result['list']);
						$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
						$p  =  $page->show();
						$GLOBALS['tmpl']->assign('pages',$p);					
						$GLOBALS['tmpl']->assign("page_title",$brand_info['name']);
						return $GLOBALS['tmpl']->fetch("inc/insert/load_brand_goods_list.html");
						
			}
	}

}
?>