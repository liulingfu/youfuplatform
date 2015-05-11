<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class ShopBaseModule{
	public function __construct()
	{
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
		$GLOBALS['dynamic_cache'] = $GLOBALS['fcache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['fcache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存
		//输出导航菜单
		$nav_list = get_shop_nav_list();
		$nav_list= init_nav_list($nav_list);
		$GLOBALS['tmpl']->assign("nav_list",$nav_list);
		
		
		
		//输出在线客服与时间
		$qq = explode("|",app_conf("ONLINE_QQ"));
		$msn = explode("|",app_conf("ONLINE_MSN"));
		$GLOBALS['tmpl']->assign("online_qq",$qq);
		$GLOBALS['tmpl']->assign("online_msn",$msn);
		
		//输出页面的标题关键词与描述
		$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
		
		//输出系统文章
		$system_article = get_article_list(8,0,"ac.type_id = 3","",true);
		$GLOBALS['tmpl']->assign("system_article",$system_article['list']);
		
		//输出帮助
		$deal_help = get_help();
		$GLOBALS['tmpl']->assign("deal_help",$deal_help);
		
		
		
		//输出热门关键词
		$hot_kw = app_conf("SHOP_SEARCH_KEYWORD");
		$hot_kw = preg_split("/[ ,]/i",$hot_kw);
		$GLOBALS['tmpl']->assign("hot_kw",$hot_kw);
		
		if(
		MODULE_NAME=="index"&&ACTION_NAME=="index"||
		MODULE_NAME=="acate"&&ACTION_NAME=="index"||
		MODULE_NAME=="article"&&ACTION_NAME=="index"||
		MODULE_NAME=="brand"&&ACTION_NAME=="index"||
		MODULE_NAME=="cate"&&ACTION_NAME=="index"||
		MODULE_NAME=="city"&&ACTION_NAME=="index"||
		MODULE_NAME=="comment"&&ACTION_NAME=="index"||
		MODULE_NAME=="daren"&&ACTION_NAME=="index"||
		MODULE_NAME=="daren"&&ACTION_NAME=="submit"||
		MODULE_NAME=="discount"&&ACTION_NAME=="index"||
		MODULE_NAME=="discover"&&ACTION_NAME=="index"||
		MODULE_NAME=="exchange"&&ACTION_NAME=="index"||
		MODULE_NAME=="goods"&&ACTION_NAME=="index"||
		MODULE_NAME=="group"&&ACTION_NAME=="index"||
		MODULE_NAME=="group"&&ACTION_NAME=="forum"||
		MODULE_NAME=="group"&&ACTION_NAME=="create"||
		MODULE_NAME=="help"&&ACTION_NAME=="index"||
		MODULE_NAME=="link"&&ACTION_NAME=="index"||
		MODULE_NAME=="mall"&&ACTION_NAME=="index"||
		MODULE_NAME=="mobile"&&ACTION_NAME=="index"||
		MODULE_NAME=="msg"&&ACTION_NAME=="index"||
		MODULE_NAME=="notice"&&ACTION_NAME=="index"||
		MODULE_NAME=="notice"&&ACTION_NAME=="list_notice"||
		MODULE_NAME=="rec"&&ACTION_NAME=="rhot"||
		MODULE_NAME=="rec"&&ACTION_NAME=="rnew"||
		MODULE_NAME=="rec"&&ACTION_NAME=="rbest"||
		MODULE_NAME=="rec"&&ACTION_NAME=="rsale"||
		MODULE_NAME=="score"&&ACTION_NAME=="index"||
		MODULE_NAME=="space"&&ACTION_NAME=="index"||
		MODULE_NAME=="space"&&ACTION_NAME=="fav"||
		MODULE_NAME=="space"&&ACTION_NAME=="fans"||
		MODULE_NAME=="space"&&ACTION_NAME=="focus"||
		MODULE_NAME=="msg"&&ACTION_NAME=="index"||
		MODULE_NAME=="ss"&&ACTION_NAME=="index"||
		MODULE_NAME=="ss"&&ACTION_NAME=="pick"||
		MODULE_NAME=="sys"&&ACTION_NAME=="index"||
		MODULE_NAME=="sys"&&ACTION_NAME=="list_notice"||
		MODULE_NAME=="topic"&&ACTION_NAME=="index"||
		MODULE_NAME=="vote"&&ACTION_NAME=="index")
		{
			set_gopreview();
		}

		
	}

	public function index()
	{
		showErr("invalid access");
	}
	public function __destruct()
	{
		if(isset($GLOBALS['fcache']))
		{
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
			$GLOBALS['fcache']->set("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);
			if(count($GLOBALS['dynamic_avatar_cache'])<=500)
			{
				$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
				$GLOBALS['fcache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
			}
		}
		unset($this);
	}
}
?>