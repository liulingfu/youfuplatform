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

class dealsModule extends TuanBaseModule
{
	public function index()
	{
		global $tmpl;
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		//分类
		
		$cate_id = intval($_REQUEST['id']);
		$uname = addslashes(trim($_REQUEST['id']));
		if($cate_id==0&&$uname!='')
		{
			$cate_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_cate where uname = '".$uname."'"); 
		}
		
		$sort_field = es_cookie::get("sort_field")?es_cookie::get("sort_field"):"sort";
		$sort_type = es_cookie::get("sort_type")?es_cookie::get("sort_type"):"desc";
		$sort_type = es_cookie::get("shop_sort_type")?es_cookie::get("shop_sort_type"):"desc";
		if($sort_field!="end_time"&&$sort_field!="current_price"&&$sort_field!="buy_count"&&$sort_field!="sort")
		{
			$sort_field = "sort";
		}
		if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
		
		$act = trim($_REQUEST['act']);
		//显示的类型
		if($act == 'history')
		{
			$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_HISTORY_LIST']);
			$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_HISTORY_LIST']);
			$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_HISTORY_LIST']);
			$type = array(DEAL_HISTORY);
		}
		elseif($act == 'notice')
		{
			$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_NOTICE_LIST']);
			$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_NOTICE_LIST']);
			$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_NOTICE_LIST']);
			$type = array(DEAL_NOTICE);
		}
		elseif($act == 'comment')
		{
			$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_COMMENT']);
			$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_COMMENT']);
			$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_COMMENT']);
			$type = array(DEAL_NOTICE,DEAL_ONLINE,DEAL_HISTORY);
		}
		else
		{
			$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['DEAL_LIST']);
			$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['DEAL_LIST']);
			$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['DEAL_LIST']);
			$type = array(DEAL_ONLINE,DEAL_HISTORY);
		}
		
		if(app_conf("SHOW_DEAL_CATE")==1)
		{
			//输出分类
			$deal_cates_db = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
			$deal_cates = array();
			if($act=='history'||$act=='notice')
				$url = url("tuan","deals#".$act);
				else 
				$url = url("tuan","deals");
			$deal_cates[] = array('id'=>0,'name'=>$GLOBALS['lang']['ALL'],'current'=>$cate_id==0?1:0,'url'=>$url);	
			foreach($deal_cates_db as $k=>$v)
			{		
				if($cate_id==$v['id'])
				$v['current'] = 1;
				if($act=='history'||$act=='notice')
				$v['url'] = url("tuan","deals#".$act,array("id"=>$v['id']));
				else 
				$v['url'] = url("tuan","deals",array("id"=>$v['id']));
				$deal_cates[] = $v;
			}
		
			$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
		}
		
		//获取搜索字段
		$no_deal_page = "no_deal.html";
		$condition=' buy_type<>1 ';
		if($_REQUEST['search'])
		{
			$search_code = unserialize(base64_decode($_REQUEST['search']));
			if($search_code['se_name']!='')
			{
				$condition.=" and name like '%".$search_code['se_name']."%' ";
				$no_deal_page = 'deals.html';
				$GLOBALS['tmpl']->assign("se_name",$search_code['se_name']);
			}
			if($search_code['se_begin']!=0)
			{
				$condition.=" and (begin_time > ".intval($search_code['se_begin'])." or begin_time = 0) ";
				$no_deal_page = 'deals.html';
				$GLOBALS['tmpl']->assign("se_begin",to_date($search_code['se_begin'],'Y-m-d'));
			}
			if($search_code['se_end']!=0)
			{
				$condition.=" and (end_time < ".intval($search_code['se_end'])." or end_time = 0) ";
				$no_deal_page = 'deals.html';
				$GLOBALS['tmpl']->assign("se_end",to_date($search_code['se_end'],'Y-m-d'));
			}
		}
		$deals = get_deal_list_show($limit,$cate_id,0,$type,$condition,$sort_field." ".$sort_type);
		
		if($act == 'comment')
		{
			foreach($deals['list'] as $k=>$v)
			{
				$deals['list'][$k]['before_buy'] =  get_message_list(2,"rel_table='deal' and rel_id=".$v['id']." and is_buy = 0");
				$deals['list'][$k]['after_buy'] =  get_message_list(2,"rel_table='deal' and rel_id=".$v['id']." and is_buy = 1");
			}
		}
		
		$GLOBALS['tmpl']->assign("deals",$deals['list']);
		
		
		$page = new Page($deals['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		require './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后
		
		
		$GLOBALS['tmpl']->assign('sort_field',$sort_field);
		$GLOBALS['tmpl']->assign('sort_type',$sort_type);
		
		if(es_cookie::get("list_type")===null)
			$list_type = app_conf("LIST_TYPE");
		else
			$list_type = intval(es_cookie::get("list_type"));
		
		$GLOBALS['tmpl']->assign("list_type",$list_type);
			
		if($deals['list'])
		{
			if($act == 'comment')
			{
				$GLOBALS['tmpl']->display("deals_comment.html");
			}
			else
			{
				if($list_type== 1)
				$GLOBALS['tmpl']->display("deals_grid.html");
				else
				$GLOBALS['tmpl']->display("deals.html");
			}
		}
		else
		{
			if($act == 'comment')
			{
				$GLOBALS['tmpl']->display("deals_comment.html");
			}
			else
			$GLOBALS['tmpl']->display($no_deal_page);
		}
	}
}
?>