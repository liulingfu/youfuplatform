<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class publishModule extends BizBaseModule
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
			app_redirect(url("biz","publish#images",array("id"=>$location_ids[1])));	
			exit();
		}
		$sql = " SELECT * FROM ".DB_PREFIX."supplier_location WHERE id IN (".implode(",",$location_ids).") ";
		$list = $GLOBALS['db']->getAll($sql);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("biz/biz_publish_list.html");
	}
	
	public function images(){
		
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];
		
		$id = $_REQUEST['id'];
		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location WHERE id = ".intval($id)." and id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$info){
			showErr("门店不存在或者没有编辑该门店的权限");
		}

		require_once APP_ROOT_PATH."app/Lib/page.php";		
		
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
			
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location_images WHERE supplier_location_id=".$id." ORDER BY `sort` desc LIMIT ".$limit);
		foreach($list as $k=>$v){
			$list[$k]['create_time_format'] = to_date($v['create_time'],"Y-m-d H:i") ;
		}
		
		$total = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."supplier_location_images WHERE supplier_location_id=".$id);
		$page = new Page($total,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		if($_REQUEST['f']=="profile")
		{
			$history_back = url("biz","profile#modify",array("id"=>$id));
		}
		elseif(count($location_ids) == 2){
			$history_back = url("biz");
		}
		else{
			$history_back = url("biz","publish");
		}
		
		$GLOBALS['tmpl']->assign("history_back",$history_back);
		$GLOBALS['tmpl']->assign("info",$info);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("biz/biz_publish_images_list.html");
	}
	
	public function add(){
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];
		
		$id = $_REQUEST['id'];
		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location WHERE id = ".intval($id)." and id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$info){
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		$images_group = publishModule::Get_Images_Group_List($_REQUEST['id']);
		
		$history_back = url("biz","publish#images",array("id"=>$_REQUEST['id']));
		$GLOBALS['tmpl']->assign("info",$info);
		$GLOBALS['tmpl']->assign("history_back",$history_back);
		$GLOBALS['tmpl']->assign('images_group', $images_group);
		$GLOBALS['tmpl']->display("biz/biz_publish_images_add.html");
	}
	
	public function insert(){

		
		if(intval($_POST['id'])==0){
			app_redirect(url("biz"));
			exit();
		}
		
		$s_account_info = es_session::get("account_info");
		if(!in_array(intval($_POST['id']),$s_account_info['location_ids']))
		{
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		$data['supplier_location_id'] = $_POST['id'] ;
		$data['images_group_id'] = intval($_POST['images_group_id']);
		$data['sort'] = intval($_POST['sort']);
		$data['create_time'] = get_gmtime();
		$data['status'] = 1;
		$s_account_info = es_session::get("account_info");
		$data['user_id'] = intval($s_account_info["id"]);
		
		for($i=0;$i<=7;$i++){
		{
			$data['image'] =  replace_public(addslashes(htmlspecialchars(trim($_POST['image_'.$i]))));			
		}				

			
			$data['brief'] = addslashes(htmlspecialchars(trim($_POST['brief'][$i])));
			if($data['image'])
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_images",$data,"INSERT");
		}
		$supplier_info['id'] = $_POST['id'];
		syn_supplier_locationcount($supplier_info);
		$cache_id  = md5("store"."view".$_POST['id']);
		$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
		showSuccess($GLOBALS['lang']['ADD_SUCCESS'],0,url("biz", "publish#images",array("id"=>intval($_POST['id']))));

	}
	
	public function modify(){
		$id = $_REQUEST['id'];
		$pid = $_REQUEST['pid'];
		
		
		
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];

		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location_images WHERE id = ".intval($pid)." and supplier_location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$info){
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		$images_group = publishModule::Get_Images_Group_List($info['supplier_location_id']);
		$GLOBALS['tmpl']->assign('images_group', $images_group);
		
		$history_back = url("biz","publish#images",array("id"=>$id));
		
		$GLOBALS['tmpl']->assign("history_back",$history_back);
		$GLOBALS['tmpl']->assign("info",$info);
		$GLOBALS['tmpl']->display("biz/biz_publish_images_modify.html");
	}
	
	public function update(){
		if(intval($_POST['pid'])==0){
			showErr($GLOBALS['lang']['ERROR_TITLE'],0,url("biz", "publish"));
			exit();
		}
		$pid = intval($_POST['pid']);
		$id = intval($_POST['id']);
		$s_account_info = es_session::get("account_info");
		$location_ids = $s_account_info['location_ids'];
		
		$info = $GLOBALS['db']->getRow(" SELECT * FROM ".DB_PREFIX."supplier_location_images WHERE id = ".intval($pid)." and supplier_location_id in (".implode(",",$s_account_info['location_ids']).")");
		
		if(!$info)
		{
			showErr("门店不存在或者没有编辑该门店的权限");
		}
		
		$data['brief'] = addslashes(htmlspecialchars(trim($_POST['brief'])));
		$data['image'] =  replace_public($_POST['image']);

			
		$data['images_group_id'] = intval($_POST['images_group_id']);
		$data['sort'] = intval($_POST['sort']);
		$data['create_time'] = get_gmtime();
		
		$rs = $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_images",$data,"UPDATE"," id = ".intval($_POST['pid']));
		if($rs){
			//更新统计
			$supplier_info['id'] = $_POST['id'];
			syn_supplier_locationcount($supplier_info);
			$cache_id  = md5("store"."view".$_POST['id']);		
			$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);

			
			showSuccess($GLOBALS['lang']['MODIFY_SUCCESS'],0,url("biz", "publish#modify",array("id"=>intval($_POST['id']),"pid"=>intval($_POST['pid']))));	
		}
	}
	
	public function delete(){
		if($_REQUEST['pid'] > 0 && intval($_REQUEST['id']) > 0)
		{
			$s_account_info = es_session::get("account_info");
			if(!in_array(intval($_POST['pid']),$s_account_info['location_ids']))
			{
				showErr("门店不存在或者没有编辑该门店的权限");
			}
			
			$image = $GLOBALS['db']->getOne(" SELECT `image` FROM ".DB_PREFIX."supplier_location_images WHERE id = ".intval($_REQUEST['pid']));
			@unlink(APP_ROOT_PATH . $image);
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."supplier_location_images WHERE id = ".intval($_REQUEST['pid']));
			
			//更新统计
			$supplier_info['id'] = $_REQUEST['id'];
			syn_supplier_locationcount($supplier_info);
			$cache_id  = md5("store"."view".$_REQUEST['id']);		
			$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
			showSuccess($GLOBALS['lang']['DELETE_SUCCESS'],0,url("biz", "publish#images",array("id"=>intval($_REQUEST['id']))));
		}
		else{
			app_redirect(url("biz"));
		}
			
	}
	
	function Get_Images_Group_List($supplier_location_id) {
		if (intval($supplier_location_id) > 0) {
			$deal_cate_id = $GLOBALS['db']->getOne("SELECT deal_cate_id FROM ".DB_PREFIX."supplier_location where id=" . intval($supplier_location_id));
			$list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "images_group WHERE id in(SELECT images_group_id FROM " . DB_PREFIX . "images_group_link WHERE category_id=" . $deal_cate_id . ")");
		}
		return $list;
	}
}
?>