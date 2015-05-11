<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class ydetailModule extends YouhuiBaseModule
{
	public function index()
	{			
		$preview = intval($_REQUEST['preview']);
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id'].$preview);		
		if (!$GLOBALS['tmpl']->is_cached('youhui_ydetail.html', $cache_id))	
		{		
			//获取当前页的团购商品
			$id = intval($_REQUEST['id']);
			$uname = addslashes(trim($_REQUEST['id']));
			
			if($id==0&&$uname=='')
			{
				app_redirect(url("shop","index"));
			}
			elseif($id==0&&$uname!='')
			{
				$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
			}

			
			if($preview>0)
			{
				$youhui = get_youhui($id,$preview);				
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
						$deal_test = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".intval($youhui['id'])." and d.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
						if(!$deal_test)
						{
							showErr("产品不存在或者没有预览该产品的权限",0,APP_ROOT."/admin.php?m=Public&a=login");
						}
					}
					else
					{
						showErr("您不是系统管理员或者商家会员，无法预览",0,APP_ROOT."/");
					}
				}		
			}
			else
			$youhui = get_youhui($id);
			jump_deal($youhui,MODULE_NAME);
			if(!$youhui)
			{
				app_redirect(url("youhui","index"));
			}
										
			$GLOBALS['tmpl']->assign("youhui",$youhui);
			
			//供应商的地址列表
			//定义location_id
			$locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."deal_location_link as b on a.id = b.location_id where a.is_effect = 1 and b.deal_id = ".intval($youhui['id']));
			
			$json_location = array();
			$location_ids = array(0);
			foreach($locations as $litem)
			{
				$location_ids[] = $litem['id'];
				$arr = array();
				$arr['title'] = $litem['name'];
				$arr['address'] = $litem['address'];
				$arr['tel'] = $litem['tel'];
				$arr['lng'] = $litem['xpoint'];
				$arr['lat'] = $litem['ypoint'];
				$json_location[] = $arr;
			}
			
			$GLOBALS['tmpl']->assign("json_location",json_encode($json_location));
			$GLOBALS['tmpl']->assign("locations",$locations);
			
			
			//开始输出当前的site_nav
			$cates = array();
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where id = ".$youhui['cate_id']);
			do
			{
				$cates[] = $cate;
				$pid = intval($cate['pid']);
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where is_effect =1 and is_delete =0 and id = ".$pid);			
				
			}while($pid!=0);
	
			krsort($cates);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			
			if($cates)
			{
				foreach($cates as $cate_row)
				{
					if($cate_row['uname']!="")
					$curl  = url("youhui","ycate#index",array("cid"=>$cate_row['uname']));
					else
					$curl  = url("youhui","ycate#index",array("cid"=>$cate_row['id']));
					$site_nav[] = array('name'=>$cate_row['name'],'url'=>$curl);
				}
			}	
			if($youhui['uname']!="")
					$gurl  = url("youhui","ydetail#index",array("id"=>$youhui['uname']));
					else
					$gurl  = url("youhui","ydetail#index",array("id"=>$youhui['id']));	
			$site_nav[] = array('name'=>$youhui['name'],'url'=>$gurl);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			
			$seo_title = $youhui['seo_title']!=''?$youhui['seo_title']:$youhui['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $youhui['seo_keyword']!=''?$youhui['seo_keyword']:$youhui['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $youhui['seo_description']!=''?$youhui['seo_description']:$youhui['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
			//周边热卖
			$areas = $GLOBALS['db']->getAll("select a.name from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id in (".implode(",",$location_ids).")");
			$condition_arr=array();
			foreach($areas as $area)
			{
				$condition_arr[] = str_to_unicode_string($area['name']);
			}	
			
			$condition =" (match(d.locate_match) against('".implode(" ",$condition_arr)."' IN BOOLEAN MODE)) and d.id <> ".$youhui['id'];	
				
			$near_youhui = search_youhui_list(4,0,$condition,"",false,"");
			$GLOBALS['tmpl']->assign("near_youhui_list",$near_youhui['list']);
		}	
		$GLOBALS['tmpl']->display("youhui_ydetail.html",$cache_id);
			
	}
	
	function load_youhui_time_status()
	{
			$id = intval($_REQUEST['id']);
			$uname = addslashes(trim($_REQUEST['id']));
			
			if($id==0&&$uname=='')
			{
				app_redirect(url("shop","index"));
			}
			elseif($id==0&&$uname!='')
			{
				$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
			}
		$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$id);
		$GLOBALS['tmpl']->assign("youhui",$youhui);
		return $GLOBALS['tmpl']->fetch("inc/youhui_detail_time_status.html");
	}
}
?>