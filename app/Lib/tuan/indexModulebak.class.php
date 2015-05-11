<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/message.php';
require APP_ROOT_PATH.'app/Lib/page.php';
class indexModule extends TuanBaseModule
{
	public function index()
	{				
		global $tmpl;
		
		//多团风格
		$deal_cate_id = intval($_REQUEST['id']);
		$deal_quan_id = intval($_REQUEST['qid']);
		$deal_type_id = intval($_REQUEST['tid']);
		$uname = addslashes(trim($_REQUEST['id']));
		
		$url_param = array(
			'id'	=> addslashes(htmlspecialchars(trim($_REQUEST['id']))),
			'tid' 	=>	intval($_REQUEST['tid']),
			'qid'	=>	intval($_REQUEST['qid'])
		);
		
		if($deal_cate_id==0&&$uname!='')
		{
			$deal_cate_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_cate where uname = '".$uname."'")); 
		}

		$GLOBALS['tmpl']->assign("is_index",1);
		
		$seo_title = app_conf("TUAN_SHOP_TITLE");
		$seo_keyword =  app_conf("TUAN_SHOP_TITLE");
		$seo_description =  app_conf("TUAN_SHOP_TITLE");
		
		$append_seo = "";
		
		
		//当前城市的商圈
		//判断当前商圈是否是大类
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['deal_city']['id']));
		
		$qpid = intval($area_result[$deal_quan_id]['pid']);
		$bdeal_quan_id = $qpid == 0?$deal_quan_id:$qpid; //大分类ID
		$cache_param = array("id"=>$deal_cate_id,"tid"=>$deal_type_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['deal_city']['id']));
		$filter_nav_data = load_auto_cache("tuan_filter_nav_cache",$cache_param);
				
		$GLOBALS['tmpl']->assign('bquan_list',$filter_nav_data['bquan_list']);
		if($bdeal_quan_id>0)
		{
			//$append_seo.=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$bdeal_quan_id);
			$append_seo.=$area_result[$deal_quan_id]['name'];
		}
		
		
		$GLOBALS['tmpl']->assign('squan_list',$filter_nav_data['squan_list']);
		if($bdeal_quan_id>0&&$bdeal_quan_id!=$deal_quan_id)
		{
			//$append_seo.=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$deal_quan_id);
			$append_seo.=$area_result[$deal_quan_id]['name'];
		}
		
		
		//开始输出分类
		
		$GLOBALS['tmpl']->assign("bcate_list",$filter_nav_data['bcate_list']);
		
	
		$cate_list = load_auto_cache("cache_deal_cate");
		//输出小分类
		$GLOBALS['tmpl']->assign("scate_list",$filter_nav_data['scate_list']);
		if($append_seo!="")
		$append_seo.=" - ";
		//$append_seo .= $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$deal_cate_id);
		$append_seo .= $cate_list[$deal_cate_id]['name'];
		
		
		$where=" buy_type<>1 ";
		if($deal_type_id>0)
		{
			$type_list = load_auto_cache("cache_deal_cate_type",array("cate_id"=>$deal_cate_id));
			
			//$deal_type_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate_type where id = ".$deal_type_id);
			$deal_type_name = $type_list[$deal_type_id]['name'];
			$deal_type_name_unicode = str_to_unicode_string($deal_type_name);
			$where .= " and (match(deal_cate_match) against('".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
			$append_seo .= $deal_type_name;
		}
		
		$sort_field = es_cookie::get("sort_field_idx")?es_cookie::get("sort_field_idx"):"sort";
		if($sort_field!="begin_time"&&$sort_field!="current_price"&&$sort_field!="buy_count"&&$sort_field!="sort")
		{
			$sort_field = "sort";
		}
		$sort_type = es_cookie::get("sort_type_idx")?es_cookie::get("sort_type_idx"):"desc";
		if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
		$GLOBALS['tmpl']->assign('sort_field',$sort_field);
		$GLOBALS['tmpl']->assign('sort_type',$sort_type);
			
		if($GLOBALS['city_name'])
			$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE")." - ".$GLOBALS['deal_city']['name'].$GLOBALS['lang']['SITE']);
		else
			$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE"));
		
		$GLOBALS['tmpl']->assign("hide_end_title",true);
		
//		require_once './app/Lib/side.php';
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		
		$result = get_deal_list($limit,$deal_cate_id,$city_id=0,$type=array(DEAL_ONLINE,DEAL_NOTICE),$where,$sort_field." ".$sort_type,$deal_quan_id);
		$deal_list = $result['list'];
	
		$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$seo_title = $append_seo.$seo_title;
		$seo_keyword = $append_seo.$seo_keyword;
		$seo_description = $append_seo.$seo_description;
		
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword);
		$GLOBALS['tmpl']->assign("page_description",$seo_description);	
	
		
		if($deal_list)
		{
				$GLOBALS['tmpl']->assign("deal_list",$deal_list);
				$tmpl->display("deal_multi.html");
		}
		else
		$tmpl->display("no_deal.html");	
	}
}
?>