<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class edetailModule extends YouhuiBaseModule
{
	public function index()
	{			
	
			$preview = intval($_REQUEST['preview']);
			$event_id = intval($_REQUEST['id']);
			if($preview>0)
			{
				$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id);			
				$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));
				$adm_name = $adm_session['adm_name'];
				$adm_id = intval($adm_session['adm_id']);
				if($adm_id == 0)
				{
					//验证是否当前的商家(不是后台管理员)
					$s_account_info = es_session::get("account_info");
					if($s_account_info)
					{
						foreach($s_account_info['location_ids'] as $id)
						{
							$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
							if($location)
							$locations[] = $location;
						}
						$deal_test = $GLOBALS['db']->getRow("select e.* from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where e.id = ".intval($event['id'])." and e.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
						if(!$deal_test)
						{
							showErr("活动不存在或者没有预览该活动的权限",0,APP_ROOT."/admin.php?m=Public&a=login");
						}
					}
					else
					{
						showErr("您不是系统管理员或者商家会员，无法预览",0,APP_ROOT."/");
					}
				}		
			}
			else
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id." and is_effect = 1");
			if(!$event)
			{
				app_redirect(url("youhui","fcate"));
			}									
			$GLOBALS['tmpl']->assign("event",$event);			
			//开始输出当前的site_nav
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_cate where id = ".$event['cate_id']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));			
			if($cate)
			{
				$curl  = url("youhui","event#index",array("cid"=>$cate['id']));
				$site_nav[] = array('name'=>$cate['name'],'url'=>$curl);
			}	
			$gurl  = url("youhui","edetail#index",array("id"=>$event['id']));	
			$site_nav[] = array('name'=>$event['name'],'url'=>$gurl);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			
			$seo_title = $event['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $event['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $event['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		
			
		$submit_result = $GLOBALS['db']->getAll("select user_id from ".DB_PREFIX."event_submit where event_id = ".$event_id." limit 18");
		$GLOBALS['tmpl']->assign("submit_result",$submit_result);
		$GLOBALS['tmpl']->display("youhui_edetail.html");
			
	}
	

}
?>