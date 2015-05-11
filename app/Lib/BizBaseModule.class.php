<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class BizBaseModule{
	public function __construct()
	{
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
		$GLOBALS['dynamic_cache'] = $GLOBALS['fcache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['fcache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存
		
		foreach($GLOBALS['biz_nav'] as $k=>$v)
		{
			if($v['ctl']==MODULE_NAME)
			{
				$GLOBALS['biz_nav'][$k]['current'] = 1;
			}
		}
		$GLOBALS['tmpl']->assign("biz_nav",$GLOBALS['biz_nav']);
		//输出页面的标题关键词与描述
		$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
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
	
	protected function check_auth()
	{
		$s_account_info = es_session::get("account_info");
		if(intval($s_account_info['id'])==0)
		{
			app_redirect(url("biz","login"));
		}
		else
		{
			$account_data = $GLOBALS['db']->getRow("select s.name as name,a.account_name as account_name,a.login_ip as login_ip ,a.login_time as login_time ,a.update_time as create_time, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".intval($s_account_info['id']));
			if(!$account_data)
			{
				app_redirect(url("biz","login"));
			}
			$GLOBALS['tmpl']->assign("account_data",$account_data);	
		}	
	}
}
?>