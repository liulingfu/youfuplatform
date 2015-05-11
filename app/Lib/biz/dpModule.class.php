<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class dpModule extends BizBaseModule
{
	public function __construct()
	{
		parent::__construct();
		$this->check_auth();
	}
	public function index()
	{				
		require_once APP_ROOT_PATH."app/Lib/page.php";		
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		
		$f = addslashes(htmlspecialchars(trim($_REQUEST['f'])));
		if($f==''||!in_array($f,array("is_buy","tuan","event","youhui","daijin","shop")))
		{
			$condition = " ";
		}
		elseif($f=='is_buy')
		{
			$condition = " and is_buy = 1 ";
		}
		else 
		{
			$condition = " and from_data  = '$f' ";
		}
		
		$GLOBALS['tmpl']->assign("f",$f);
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$dp_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_dp where status = 1 $condition and supplier_location_id in (".implode(",",$s_account_info['location_ids']).") order by create_time desc limit ".$limit);
		$dp_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1  $condition and supplier_location_id in (".implode(",",$s_account_info['location_ids']).")");
		
		$page = new Page($dp_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		
		$GLOBALS['tmpl']->assign("dp_list",$dp_list);
		$html = decode_topic_without_img($GLOBALS['tmpl']->fetch("biz/biz_dp_list_content.html"));
		$GLOBALS['tmpl']->assign("html",$html);
		$GLOBALS['tmpl']->assign("page_title","点评列表");
		$GLOBALS['tmpl']->display("biz/biz_dp.html");
	}
}
?>