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

class dealModule extends TuanBaseModule
{
	public function index()
	{
		global $tmpl;
		//获取当前页的团购商品
		$id = addslashes(trim($_REQUEST['id']));
		$uname = addslashes(trim($_REQUEST['id']));
		$preview = intval($_REQUEST['preview']);
		if($id==0&&$uname=='')
		{
			app_redirect(url("tuan","index"));
		}

		//获取当前页的团购商品
		
		if($preview>0)
		{
			$deal = get_deal_show($id,0,0,$preview);
						
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
					$deal_test = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".intval($deal['id'])." and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
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
		else		
		$deal = get_deal_show($id);
		
		jump_deal($deal,MODULE_NAME);
			
		if($deal['buy_type']==1) 
		{
			app_redirect(url("tuan","index"));
		}	
		if($deal['is_effect']==0&&$preview==0&&$adm_id==0)
		{
			app_redirect(url("tuan","index"));
		}
		
		$GLOBALS['tmpl']->assign("deal",$deal);
		
		//供应商的地址列表
		
		
		$GLOBALS['tmpl']->assign("json_location",$deal['json_location']);
		$GLOBALS['tmpl']->assign("locations",$deal['locations']);
		
		require_once './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后
		
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where deal_id = ".intval($deal['id'])." and is_new = 0 and is_valid = 1 and user_id = ".intval($GLOBALS['user_info']['id']));
		$GLOBALS['tmpl']->assign("coupon_data",$coupon_data);
		
		if(app_conf("SHOW_DEAL_CATE")==1)
		{
			$deal_cate_id = intval($deal['cate_id']);				
			$GLOBALS['tmpl']->assign("is_index",1);
			$GLOBALS['tmpl']->assign("hide_sort",1);
			
			
			$cache_param = array("id"=>$deal_cate_id,"tid"=>0,"qid"=>0,"city_id"=>intval($GLOBALS['deal_city']['id']));
			$filter_nav_data = load_auto_cache("tuan_filter_nav_cache",$cache_param);
			$GLOBALS['tmpl']->assign('bquan_list',$filter_nav_data['bquan_list']);
			//开始输出分类
			
			$GLOBALS['tmpl']->assign("bcate_list",$filter_nav_data['bcate_list']);
		}
		
		
		//输出团购的留言
		$rel_table = "deal";
		$condition = '';	
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;	
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else 
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}		
		$before_sale_condition = $condition." and is_buy = 0";	
		$after_sale_condition = $condition." and is_buy = 1";	
			
		$limit = "15";	
		$before_sale_message = get_message_list($limit,$before_sale_condition);
		$after_sale_message = get_message_list($limit,$after_sale_condition);
		
		$GLOBALS['tmpl']->assign("user_auth",get_user_auth());
		$GLOBALS['tmpl']->assign("message_list",$before_sale_message['list']);
		$before_message_html = load_message_list();
		$GLOBALS['tmpl']->assign("message_list",$after_sale_message['list']);
		$after_message_html = load_message_list();
		
		$GLOBALS['tmpl']->assign("before_message_html",$before_message_html);
		$GLOBALS['tmpl']->assign("after_message_html",$after_message_html);
		//end 留言
		
		
		if($deal)
		{
			$GLOBALS['tmpl']->assign("page_title", $deal['seo_title']!=''?$deal['seo_title']:$deal['name']);		
			$GLOBALS['tmpl']->assign("page_keyword",$deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['name']);
			$GLOBALS['tmpl']->assign("page_description",$deal['seo_description']!=''?$deal['seo_description']:$deal['name']);
			$GLOBALS['tmpl']->display("deal.html");
		}
		else
		{
			$GLOBALS['tmpl']->assign("page_title", "没有相关的团购");		
			$GLOBALS['tmpl']->assign("page_keyword", "没有相关的团购");
			$GLOBALS['tmpl']->assign("page_description", "没有相关的团购");
			$GLOBALS['tmpl']->display("no_deal.html");		
		}
	}
}
?>