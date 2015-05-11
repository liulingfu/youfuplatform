<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_consigneeModule extends ShopBaseModule
{
	public function index()
	{
		 
		//输出配送方式
		$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']);
		
		if($consignee_id>0)
		{
			$consignee_data = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));			
			$consignee_info = $consignee_data['consignee_info'];
			$region_lv1 = $consignee_data['region_lv1'];
			$region_lv2 = $consignee_data['region_lv2'];
			$region_lv3 = $consignee_data['region_lv3'];
			$region_lv4 = $consignee_data['region_lv4'];			
			$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);			
			$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);			
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);			
			$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);			
			
			$GLOBALS['tmpl']->assign("consignee_info",$consignee_info);
		}
		else
		{
			$region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
			$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		}
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CONSIGNEE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_consignee_index.html");
		$GLOBALS['tmpl']->display("uc.html");	
	}
	
	public function save()
	{
		if(trim($_REQUEST['consignee'])=='')
		{
			showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE']);
		}
		if(trim($_REQUEST['address'])=='')
		{
			showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS']);
		}
		if(trim($_REQUEST['zip'])=='')
		{
			showErr($GLOBALS['lang']['FILL_CORRECT_ZIP']);
		}
		if(trim($_REQUEST['mobile'])=='')
		{
			showErr($GLOBALS['lang']['FILL_MOBILE_PHONE']);
		}
		if(!check_mobile($_REQUEST['mobile']))
		{
			showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE']);
		}
		
		$consignee_data['user_id'] = $GLOBALS['user_info']['id'];
		$consignee_data['region_lv1'] = intval($_REQUEST['region_lv1']);
		$consignee_data['region_lv2'] = intval($_REQUEST['region_lv2']);
		$consignee_data['region_lv3'] = intval($_REQUEST['region_lv3']);
		$consignee_data['region_lv4'] = intval($_REQUEST['region_lv4']);
		$consignee_data['address'] = addslashes(trim(htmlspecialchars($_REQUEST['address'])));
		$consignee_data['mobile'] = addslashes(trim(htmlspecialchars($_REQUEST['mobile'])));
		$consignee_data['consignee'] = addslashes(trim(htmlspecialchars($_REQUEST['consignee'])));
		$consignee_data['zip'] = addslashes(trim(htmlspecialchars($_REQUEST['zip'])));
		
		$consignee_id = intval($_REQUEST['id']);
		if($consignee_id == 0)
		{
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$consignee_data);
		}
		else
		{
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$consignee_data,"UPDATE","id=".$consignee_id);
		}
		rm_auto_cache("consignee_info",array("consignee_id"=>intval($consignee_id)));
		showSuccess($GLOBALS['lang']['UPDATE_SUCCESS']);
	}
}
?>