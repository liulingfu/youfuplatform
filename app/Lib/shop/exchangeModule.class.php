<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class exchangeModule extends ShopBaseModule
{
	public function index()
	{		
		
		$preview = intval($_REQUEST['preview']);
		$id = intval($_REQUEST['id']);
		if($preview>0)
		{
				$goods = get_goods($id,$preview);	
				
				if($goods['buy_type']==0)
				{
					app_redirect(url("shop","goods",array("id"=>$goods['id'],"preview"=>$preview)));
				}
							
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
						$deal_test = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".intval($goods['id'])." and d.is_shop = 1 and d.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
						if(!$deal_test)
						{
							showErr("产品不存在或者没有预览该产品的权限",0,APP_ROOT."/");
						}
					}
					else
					{
						showErr("您不是系统管理员或者商家会员，无法预览",0,APP_ROOT."/");
					}
				}		
		}
		
		
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('score_info.html', $cache_id))	
		{		
			
			
			//获取当前页的团购商品
			$id = intval($_REQUEST['id']);
			$uname = addslashes(trim($_REQUEST['id']));
			
			if($id==0&&$uname=='')
			{
				app_redirect(APP_ROOT."/");
			}
			elseif($id==0&&$uname!='')
			{
				$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
			}
			//获取当前页的团购商品
			
			if($preview>0)
			{
				$goods = get_goods($id,$preview);				
			}
			else
			$goods = get_goods($id);
			
			//输出商城分类
			$cate_tree = get_cate_tree($goods['shop_cate_id']);
			$GLOBALS['tmpl']->assign("cate_id",$goods['shop_cate_id']);
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);
			jump_deal($goods,MODULE_NAME);
			if(!$goods||$goods['buy_type']!=1)
			{
				app_redirect(APP_ROOT."/");
			}
						
			
			$GLOBALS['tmpl']->assign("goods",$goods);
			
			//开始输出当前的site_nav
			$cates = array();
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where id = ".$goods['shop_cate_id']);
			do
			{
				$cates[] = $cate;
				$pid = intval($cate['pid']);
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where is_effect =1 and is_delete =0 and id = ".$pid);			
				
			}while($pid!=0);
	
			$page_title = substr($page_title,0,-3);
			krsort($cates);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			
			if($cates)
			{
				foreach($cates as $cate_row)
				{
					if($cate_row['uname']!="")
					$curl = url("shop","score#index",array("id"=>$cate_row['uname']));
					else
					$curl = url("shop","score#index",array("id"=>$cate_row['id']));
					$site_nav[] = array('name'=>$cate_row['name'],'url'=>$curl);
				}
			}	

			if($goods['uname']!="")
					$gurl = url("shop","exchange#index",array("id"=>$goods['uname']));
					else
					$gurl = url("shop","exchange#index",array("id"=>$goods['id']));
			$site_nav[] = array('name'=>$goods['name'],'url'=>$gurl);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			
			$seo_title = $goods['seo_title']!=''?$goods['seo_title']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $goods['seo_keyword']!=''?$goods['seo_keyword']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $goods['seo_description']!=''?$goods['seo_description']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
			if(!$GLOBALS['user_info'])
			{
				$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
			}
		}
		$GLOBALS['tmpl']->display("score_info.html",$cache_id);
	}
}
?>