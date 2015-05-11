<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';

class scoreModule extends ShopBaseModule
{
	public function index()
	{	
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).intval($_REQUEST['p']).$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('score_list.html', $cache_id))		
		{				
			
			
			//获取当前页的团购商品列表
			//分页
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
					
			$id = intval($_REQUEST['id']);
			if($id==0)
			$uname = addslashes(trim($_REQUEST['id']));
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where id = ".$id." or (uname = '".$uname."' and uname <> '')");
						
			//输出商城分类
			$cate_tree = get_cate_tree($cate_item['id']);
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);
			
			$result = get_goods_list($limit,intval($cate_item['id']),'buy_type = 1','');
			
			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			//开始输出当前的site_nav
			$cates = array();
			$cate = $cate_item;
			do
			{
				$cates[] = $cate;
				$pid = intval($cate['pid']);
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where is_effect =1 and is_delete =0 and id = ".$pid);			
				
			}while($pid!=0);
			
			foreach($cates as $cate_row)
			{
				$page_title .= $cate_row['name']." - "; 
				$page_kd .= $cate_row['name'].",";
			}
			$page_title = substr($page_title,0,-3);
			krsort($cates);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			if($cate_item)
			{
				foreach($cates as $cate_row)
				{
					if($cate_row['uname']!='')
					$curl = url("shop","score#index",array("id"=>$cate_row['uname']));
					else
					$curl = url("shop","score#index",array("id"=>$cate_row['id']));
					$site_nav[] = array('name'=>$cate_row['name'],'url'=>$curl);
				}
			}		
			else
			{
				$site_nav[] = array('name'=>$GLOBALS['lang']['SCORE_LIST'],'url'=>url("shop","score"));
			}
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			
			$GLOBALS['tmpl']->assign("page_title",$page_title.$GLOBALS['lang']['SCORE_LIST']);
			$GLOBALS['tmpl']->assign("page_keyword",$page_kd.$GLOBALS['lang']['SCORE_LIST'].",");
			$GLOBALS['tmpl']->assign("page_description",$page_kd.$GLOBALS['lang']['SCORE_LIST'].",");
			if(!$result['list']&&intval($_REQUEST['p'])>0)	
			{
				$GLOBALS['tmpl']->display("score_list.html");
				exit;
			}		
		}
		$GLOBALS['tmpl']->display("score_list.html",$cache_id);
	}
}
?>