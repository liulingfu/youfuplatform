<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class youhuiModule extends BizBaseModule
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

		
		$GLOBALS['tmpl']->assign("page_title","优惠券列表");

		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		
		$youhui_list = $GLOBALS['db']->getAll("select distinct(y.id),y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and y.supplier_id = ".$supplier_id." order by y.id desc limit ".$limit);
		foreach($youhui_list as $k=>$v)
		{
			if($v['supplier_id']>0)
			$youhui_list[$k]['supplier_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = ".$v['supplier_id']);
			
			$sql = "select count(*) from ".DB_PREFIX."youhui_log where youhui_id = ".$v['id'];
			$youhui_list[$k]['sms_count'] = intval($GLOBALS['db']->getOne($sql));
			
			$sql = "select count(*) from ".DB_PREFIX."youhui_log where youhui_id = ".$v['id']." and confirm_id <> 0";
			$youhui_list[$k]['confirm_count'] = intval($GLOBALS['db']->getOne($sql));
		
		}
		$GLOBALS['tmpl']->assign('youhui_list',$youhui_list);
		
		$deal_count = $GLOBALS['db']->getOne("select count(distinct(y.id)) from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and y.supplier_id = ".$supplier_id);
		$page = new Page($deal_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_youhui.html");
	}
	
	public function youhui_list()
	{
		require_once APP_ROOT_PATH."app/Lib/page.php";	
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		
		$GLOBALS['tmpl']->assign("page_title","优惠券短信下载日志");
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		$youhui_id = intval($_REQUEST['id']);
		$sql = "select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where l.youhui_id = ".$youhui_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$youhui_info = $GLOBALS['db']->getRow($sql);
		if(!$youhui_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);	
		}
		
		$log_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."youhui_log where youhui_id = ".$youhui_info['id']." order by create_time desc limit ".$limit);
		$GLOBALS['tmpl']->assign('youhui_info',$youhui_info);
		$GLOBALS['tmpl']->assign('log_list',$log_list);
		
		$log_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where youhui_id = ".$youhui_info['id']);
		$page = new Page($log_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_youhui_list.html");
	}
	
	public function publish()
	{
		$s_account_info = es_session::get("account_info");
		foreach($s_account_info['location_ids'] as $id)
		{
			$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if($location)
			$locations[] = $location;
		}

		$GLOBALS['tmpl']->assign("page_title","发布优惠券");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_youhui_publish.html");
	}
	
	public function modify()
	{
		$s_account_info = es_session::get("account_info");
		foreach($s_account_info['location_ids'] as $id)
		{
			$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if($location)
			$locations[] = $location;
		}
		$id = intval($_REQUEST['id']);
		$youhui_info = $GLOBALS['db']->getRow("select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where y.id = ".$id." and y.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$youhui_info)
		{
			showErr("优惠不存在或者没有编辑该优惠的权限");
		}
		
		$youhui_info['begin_time'] = $youhui_info['begin_time']>0?to_date($youhui_info['begin_time'],"Y-m-d"):"";
		$youhui_info['end_time'] = $youhui_info['end_time']>0?to_date($youhui_info['end_time'],"Y-m-d"):"";
		
		$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);
		$GLOBALS['tmpl']->assign("page_title","编辑优惠券");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_youhui_modify.html");
	}	
	public function submit_publish()
	{
		
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['total_num'] = addslashes(htmlspecialchars(trim($_REQUEST['total_num'])));
		$data['send_type'] = intval($_REQUEST['send_type']);
		if($data['send_type']>0)
		$data['is_print'] = 0;
		else
		$data['is_print'] = 1;
		$data['is_sms'] = 1;
		
		$data['sms_content'] = addslashes(htmlspecialchars(trim($_REQUEST['sms_content'])));
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$data['deal_cate_id'] = intval($_REQUEST['cate_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['image'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['image']))));		
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));		
		$data['description'] = addslashes(trim(replace_public($_REQUEST['descript'])));
		$data['description'] = valid_tag($data['description']);
		$data['user_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);
		
		$location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".intval($_REQUEST['location_id'][0]));
		$data['xpoint'] = $location_info['xpoint'];
		$data['ypoint'] = $location_info['ypoint'];
		
		$data['return_money'] = doubleval(app_conf("USER_YOUHUI_DOWN_MONEY"));
		$data['return_score'] = intval(app_conf("USER_YOUHUI_DOWN_SCORE"));
		$data['return_point'] = intval(app_conf("USER_YOUHUI_DOWN_POINT"));
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui",$data);
		$youhui_id = intval($GLOBALS['db']->insert_id());
		if($youhui_id>0)
		{
					
			foreach($_REQUEST['deal_cate_type_id'] as $deal_cate_type_id)
			{
				if($deal_cate_type_id>0)
				{
				$deal_cate_type_link = array("youhui_id"=>$youhui_id,"deal_cate_type_id"=>intval($deal_cate_type_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cate_type_youhui_link",$deal_cate_type_link);
				}
			}
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				if($location_id>0)
				{
				$location_link = array("youhui_id"=>$youhui_id,"location_id"=>intval($location_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_location_link",$location_link);
				}
			}
			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
	
	
	public function submit_modify()
	{
		
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where y.id = ".$id." and y.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$data)
		{
			showErr("优惠不存在或者没有编辑该优惠的权限");
		}
		
		$data['id'] = intval($_REQUEST['id']);
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['total_num'] = addslashes(htmlspecialchars(trim($_REQUEST['total_num'])));
		$data['send_type'] = intval($_REQUEST['send_type']);
		if($data['send_type']>0)
		$data['is_print'] = 0;
		else
		$data['is_print'] = 1;
		$data['is_sms'] = 1;
		
		$data['sms_content'] = addslashes(htmlspecialchars(trim($_REQUEST['sms_content'])));
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$data['deal_cate_id'] = intval($_REQUEST['cate_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['image'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['image']))));		
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));		
		$data['description'] = addslashes(trim(replace_public($_REQUEST['descript'])));
		$data['description'] = valid_tag($data['description']);
		$data['user_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);
		
		foreach($s_account_info['location_ids'] as $id)
		{
			$location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if($location_info)
			{
				$data['xpoint'] = $location_info['xpoint'];
				$data['ypoint'] = $location_info['ypoint'];
				break;
			}
		}
		
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui",$data,"UPDATE","id=".$data['id']);
		$youhui_id = $data['id'];
		if($youhui_id>0)
		{

			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cate_type_youhui_link where youhui_id = ".$youhui_id);
			foreach($_REQUEST['deal_cate_type_id'] as $deal_cate_type_id)
			{
				if($deal_cate_type_id>0)
				{
				$deal_cate_type_link = array("youhui_id"=>$youhui_id,"deal_cate_type_id"=>intval($deal_cate_type_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cate_type_youhui_link",$deal_cate_type_link);
				}
			}

			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
	
}
?>