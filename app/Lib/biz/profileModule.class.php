<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class profileModule extends BizBaseModule
{
	public function __construct()
	{
		parent::__construct();
		$this->check_auth();
	} 
	public function index()
	{
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];
		if(count($location_ids) == 2){
			app_redirect(url("biz","profile#modify",array("id"=>$location_ids[1])));	
			exit();
		}
		$sql = " SELECT * FROM ".DB_PREFIX."supplier_location WHERE id IN (".implode(",",$location_ids).") ";
		$list = $GLOBALS['db']->getAll($sql);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("biz/biz_location_list.html");
	}
	
	public function modify(){
		convert_req($_REQUEST);
		$id = $_REQUEST['id'];
		
		
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];

		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location WHERE id = ".intval($id)." and id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$info){
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		
		$info['city_name'] = get_deal_city_name(intval($info['city_id']));
		
		$area_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."area where city_id=".intval($info['city_id']));
		foreach($area_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."supplier_location_area_link where area_id=".$v['id']." and location_id = ".$id))
			{
				$area_list[$k]['checked'] = true;
			}	
		}
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];
		if(count($location_ids) == 2){
			$history_back = url("biz");
		}
		else{
			$history_back = url("biz","profile#modify");
		}
		$GLOBALS['tmpl']->assign("history_back",$history_back);
		$GLOBALS['tmpl']->assign("info",$info);
		$GLOBALS['tmpl']->assign("area_list",$area_list);
		$GLOBALS['tmpl']->display("biz/biz_location_modify.html");
	}
	
	public function update(){
		if(intval($_POST['id'])==0){
			showErr($GLOBALS['lang']['ERROR_TITLE'],0,url("biz", "profile"));
			exit();
		}
		$id = intval($_POST['id']);
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];

		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location WHERE id = ".intval($id)." and id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$info){
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		$data['preview'] =  str_replace(get_domain().APP_ROOT,".",addslashes(htmlspecialchars(trim($_POST['preview']))));
		$data['tags'] =  addslashes(htmlspecialchars(trim($_POST['tags'])));
		$data['address'] =  addslashes(htmlspecialchars(trim($_POST['address'])));
		$data['route'] =  addslashes(htmlspecialchars(trim($_POST['route'])));
		$data['tel'] =  addslashes(htmlspecialchars(trim($_POST['tel'])));
		$data['contact'] =  addslashes(htmlspecialchars(trim($_POST['contact'])));
		$data['open_time'] =  addslashes(htmlspecialchars(trim($_POST['open_time'])));
		$data['api_address'] =  addslashes(htmlspecialchars(trim($_POST['api_address'])));
		$data['xpoint'] =  $_POST['xpoint'];
		$data['ypoint'] =  $_POST['ypoint'];
		$data['sms_content'] =  addslashes(htmlspecialchars(trim($_POST['sms_content'])));
		$data['brief'] = addslashes(trim(replace_public($_POST['brief']))); 
		$data['brief'] = valid_tag($data['brief']);
		$data['id'] = intval($_POST['id']);
		$data['seo_title'] = addslashes(htmlspecialchars(trim($_POST['seo_title'])));
		$data['seo_keyword'] = addslashes(htmlspecialchars(trim($_POST['seo_keyword'])));
		$data['seo_description'] = addslashes(htmlspecialchars(trim($_POST['seo_description'])));
		$rs = $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$data,"UPDATE"," id = ".intval($_POST['id']));
		if($rs){
			//更新统计			
			syn_supplier_locationcount($data);
			syn_supplier_location_match($data['id']);
			$cache_id  = md5("store"."view".$data['id']);		
			$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
			showSuccess($GLOBALS['lang']['SUPPLIER_MODIFY_SUCCESS'],0,url("biz", "profile#modify",array("id"=>intval($_POST['id']))));	
		}
	}
	
	public function password()
	{
		$GLOBALS['tmpl']->assign("page_title","修改密码");
		$GLOBALS['tmpl']->display("biz/biz_password.html");
	}
	
	public function do_modify_password()
	{		
		$s_account_info = es_session::get("account_info");
		$new_pwd = htmlspecialchars(addslashes(trim($_REQUEST['account_new_password'])));
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set account_password = '".md5($new_pwd)."' where id = ".intval($s_account_info['id']));
		showSuccess($GLOBALS['lang']['PASSWORD_MODIFY_SUCCESS'],0,url("biz", "profile#password"));	
	}
}
?>