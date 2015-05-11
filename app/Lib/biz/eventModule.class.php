<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class eventModule extends BizBaseModule
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

		
		$GLOBALS['tmpl']->assign("page_title","活动列表");

		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		
		$event_list = $GLOBALS['db']->getAll("select distinct(e.id),e.* from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and e.supplier_id = ".$supplier_id." order by e.id desc limit ".$limit);

		$GLOBALS['tmpl']->assign('event_list',$event_list);
		
		$event_count = $GLOBALS['db']->getOne("select count(distinct(e.id)) from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and e.supplier_id = ".$supplier_id);
		$page = new Page($event_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_event.html");
	}
	
	public function submit_list()
	{
		require_once APP_ROOT_PATH."app/Lib/page.php";	
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		
		$GLOBALS['tmpl']->assign("page_title","活动报名名单");

		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		$event_id = intval($_REQUEST['id']);
		$sql = "select e.* from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where l.event_id = ".$event_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$event_info = $GLOBALS['db']->getRow($sql);
		if(!$event_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);	
		}
		
		$submit_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_submit where event_id = ".$event_info['id']." order by create_time desc limit ".$limit);
		foreach($submit_list as $k=>$v)
		{
			$submit_list[$k]['content'] = $this->get_submit_content($v['id']);
		}
		
		$GLOBALS['tmpl']->assign('event_info',$event_info);
		$GLOBALS['tmpl']->assign('submit_list',$submit_list);
		
		$log_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_submit where event_id = ".$event_info['id']);
		$page = new Page($log_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_event_submit_list.html");
	}
	
	private function get_submit_content($id)
	{
		$field_result  = $GLOBALS['db']->getAll("select f.field_show_name,r.result from ".DB_PREFIX."event_submit_field as r left join ".DB_PREFIX."event_field as f on f.id = r.field_id where r.submit_id = ".$id);
		$result = "";
		foreach($field_result as $k=>$v)
		{
			$result.=$v['field_show_name'].":".$v['result']." ";
		}
		return $result;
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
		$GLOBALS['tmpl']->assign("page_title","发布商家活动");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_cate where is_effect = 1 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_event_publish.html");
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
		$event_info = $GLOBALS['db']->getRow("select e.* from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where e.id = ".$id." and e.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$event_info)
		{
			showErr("活动不存在或者没有编辑该活动的权限");
		}
		
		$event_info['event_begin_time'] = $event_info['event_begin_time']>0?to_date($event_info['event_begin_time'],"Y-m-d"):"";
		$event_info['event_end_time'] = $event_info['event_end_time']>0?to_date($event_info['event_end_time'],"Y-m-d"):"";
		$event_info['submit_begin_time'] = $event_info['submit_begin_time']>0?to_date($event_info['submit_begin_time'],"Y-m-d"):"";
		$event_info['submit_end_time'] = $event_info['submit_end_time']>0?to_date($event_info['submit_end_time'],"Y-m-d"):"";

		$field_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_info['id']." order by sort asc");
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		$GLOBALS['tmpl']->assign("event_info",$event_info);
		$GLOBALS['tmpl']->assign("page_title","编辑商家活动");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_cate where is_effect = 1 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_event_modify.html");
	}
	
	public function add_submit_item()
	{
		$GLOBALS['tmpl']->display("biz/biz_event_submit_item.html");
	}
	
	
	public function submit_publish()
	{		
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['icon'] = addslashes(htmlspecialchars(replace_public(trim($_REQUEST['icon']))));
		
		$data['event_begin_time'] = trim($_REQUEST['event_begin_time'])==''?0:to_timespan($_REQUEST['event_begin_time']);
		$data['event_end_time'] = trim($_REQUEST['event_end_time'])==''?0:to_timespan($_REQUEST['event_end_time']);
		$data['submit_begin_time'] = trim($_REQUEST['submit_begin_time'])==''?0:to_timespan($_REQUEST['submit_begin_time']);
		$data['submit_end_time'] = trim($_REQUEST['submit_end_time'])==''?0:to_timespan($_REQUEST['submit_end_time']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		
		
		$data['address'] = addslashes(htmlspecialchars(trim($_REQUEST['address'])));		
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));		
		$data['content'] = addslashes(trim(replace_public($_REQUEST['content'])));
		$data['content'] = valid_tag($data['content']);
		$data['user_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);
		$data['xpoint'] = doubleval($_REQUEST['xpoint']);
		$data['ypoint'] = doubleval($_REQUEST['ypoint']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."event",$data);
		$event_id = intval($GLOBALS['db']->insert_id());
		if($event_id>0)
		{
			foreach($_REQUEST['location_id'] as $location_id)
			{
				if($location_id>0)
				{
					$location_link = array("event_id"=>$event_id,"location_id"=>intval($location_id));
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_location_link",$location_link);
				}
			}
			
			foreach($_REQUEST['field_id'] as $k=>$field_id)
			{
				$event_field = array();
				$event_field['event_id'] = $event_id;
				$event_field['field_show_name'] = addslashes(htmlspecialchars($_REQUEST['field_show_name'][$k]));
				$event_field['field_type'] = addslashes(htmlspecialchars($_REQUEST['field_type'][$k]));
				$event_field['value_scope'] = addslashes(htmlspecialchars($_REQUEST['value_scope'][$k]));
				$event_field['sort'] = $k;
				$GLOBALS['db']->autoExecute(DB_PREFIX."event_field",$event_field);
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
		$data = $GLOBALS['db']->getRow("select e.* from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on l.event_id = e.id where e.id = ".$id." and e.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$data)
		{
			showErr("活动不存在或者没有编辑该活动的权限");
		}
		
		$event_id = $data['id'] = $id;
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['icon'] = addslashes(htmlspecialchars(replace_public(trim($_REQUEST['icon']))));
		
		$data['event_begin_time'] = trim($_REQUEST['event_begin_time'])==''?0:to_timespan($_REQUEST['event_begin_time']);
		$data['event_end_time'] = trim($_REQUEST['event_end_time'])==''?0:to_timespan($_REQUEST['event_end_time']);
		$data['submit_begin_time'] = trim($_REQUEST['submit_begin_time'])==''?0:to_timespan($_REQUEST['submit_begin_time']);
		$data['submit_end_time'] = trim($_REQUEST['submit_end_time'])==''?0:to_timespan($_REQUEST['submit_end_time']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		
		
		$data['address'] = addslashes(htmlspecialchars(trim($_REQUEST['address'])));		
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));		
		$data['content'] = addslashes(trim(replace_public($_REQUEST['content'])));
		$data['content'] = valid_tag($data['content']);
		$data['user_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);
		$data['xpoint'] = doubleval($_REQUEST['xpoint']);
		$data['ypoint'] = doubleval($_REQUEST['ypoint']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."event",$data,"UPDATE","id=".$data['id']);
		
		if($event_id>0)
		{
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."event_field where event_id = ".$event_id);
			foreach($_REQUEST['field_id'] as $k=>$field_id)
			{
				$event_field = array();
				$event_field['event_id'] = $event_id;
				$event_field['field_show_name'] = addslashes(htmlspecialchars($_REQUEST['field_show_name'][$k]));
				$event_field['field_type'] = addslashes(htmlspecialchars($_REQUEST['field_type'][$k]));
				$event_field['value_scope'] = addslashes(htmlspecialchars($_REQUEST['value_scope'][$k]));
				$event_field['sort'] = $k;
				$GLOBALS['db']->autoExecute(DB_PREFIX."event_field",$event_field);
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