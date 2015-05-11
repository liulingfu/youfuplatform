<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_centerModule extends ShopBaseModule
{
	public function init_main()
	{
//		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
//		require_once APP_ROOT_PATH."system/extend/ip.php";		
//		$iplocation = new iplocate();
//		$address=$iplocation->getaddress($user_info['login_ip']);
//		$user_info['from'] = $address['area1'].$address['area2'];
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		set_uc_right();
			
	}
	
	public function index()
	{		 
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{
			$this->init_main();			
			$is_merchant = intval($GLOBALS['user_info']['is_merchant']);
			$GLOBALS['tmpl']->assign('is_merchant',$is_merchant);			
		}
		$user_id = intval($GLOBALS['user_info']['id']);	
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
					
		//开始输出相关的用户日志
		$uids = $GLOBALS['db']->getOne("select group_concat(focused_user_id) from ".DB_PREFIX."user_focus where focus_user_id = ".$GLOBALS['user_info']['id']);

		if($uids)
		{
			$uids = trim($uids,",");
			$uids.=",".$GLOBALS['user_info']['id'];		
			$result = get_topic_list($limit," user_id in (".$uids.") ");
		}
		else
		$result = get_topic_list($limit);
		$result['list'] = div_to_col($result['list']);
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
			
		if($ajax==0)
		{
			$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
		}
			
		
	}
	
	public function mytopic()
	{		
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();			
			$is_merchant = intval($GLOBALS['user_info']['is_merchant']);	
			$GLOBALS['tmpl']->assign('is_merchant',$is_merchant);
		}
		
		$user_id = intval($GLOBALS['user_info']['id']);	
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_topic_list($limit," user_id = ".$user_id);
			
		$result['list'] = div_to_col($result['list']);
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		if($ajax==0)
		{	
			$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_MYTOPIC']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_MYTOPIC']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
		}
	}
	
	
	
	public function myfav()
	{		
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();				
			$is_merchant = intval($GLOBALS['user_info']['is_merchant']);	
			$GLOBALS['tmpl']->assign('is_merchant',$is_merchant);
		}
		$user_id = intval($GLOBALS['user_info']['id']);
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_topic_list($limit," user_id = ".$user_id." and fav_id <> 0");
			
		$result['list'] = div_to_col($result['list']);
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		if($ajax==0)
		{	
			$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_MYFAV']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_MYFAV']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
		}
	}
	
	
	public function atme()
	{		
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();			
			$is_merchant = intval($GLOBALS['user_info']['is_merchant']);	
			$GLOBALS['tmpl']->assign('is_merchant',$is_merchant);
		}
		$user_id = intval($GLOBALS['user_info']['id']);	
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$user_name = $GLOBALS['user_info']['user_name'];
		$user_name_unicode = str_to_unicode_string($user_name);
		$condition = " match(user_name_match) against('".$user_name_unicode."' IN BOOLEAN MODE) ";
		
		$result = get_topic_list($limit,$condition);
			
		$result['list'] = div_to_col($result['list']);
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		if($ajax==0)
		{	
			$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_ATME']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_ATME']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
		}
	}
	
	
	public function mycomment()
	{		
		$ajax =intval($_REQUEST['ajax']);
		if($ajax==0)
		{ 
			$this->init_main();			
			$is_merchant = intval($GLOBALS['user_info']['is_merchant']);	
			$GLOBALS['tmpl']->assign('is_merchant',$is_merchant);
		}
		
		$user_id = intval($GLOBALS['user_info']['id']);	
		//输出发言列表
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		//输出回复
		$sql = "select r.* from ".DB_PREFIX."topic_reply as r left join ".DB_PREFIX."topic as t on r.topic_id = t.id 
				where (t.user_id = ".$user_id." or r.user_id = ".$user_id.") and r.is_effect = 1 and r.is_delete = 0 
				order by r.create_time desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."topic_reply as r left join ".DB_PREFIX."topic as t on r.topic_id = t.id 
				where (t.user_id = ".$user_id." or r.user_id = ".$user_id.") and r.is_effect = 1 and r.is_delete = 0";
		
		$list = $GLOBALS['db']->getAll($sql);		
		$count = $GLOBALS['db']->getOne($sql_count);
			
		$tmp_topic_list = array();
		foreach($list as $k=>$v)
		{			
			if(isset($tmp_topic_list[$v['topic_id']]))
			{
				$list[$k]['topic'] = $tmp_topic_list[$v['topic_id']];
			}
			else
			{
				$topic = $GLOBALS['db']->getRow("select id,title,content from ".DB_PREFIX."topic where id = ".$v['topic_id']);
				if($topic)
				{
					$content_link = decode_topic_without_allmedia($topic['content']);
					if(!$content_link)$content_link = $GLOBALS['lang']['THIS_TOPIC'];
					$content_link = " [<a href='".url("shop","topic#index",array("id"=>$topic['id']))."'>".msubstr($content_link,0,50)."</a>] ";
				}
				else
				{
					$content_link = " [<span style='text-decoration:line-through;'>".$GLOBALS['lang']['ORIGIN_DELETE']."</span>] ";
				}
				$list[$k]['topic'] = $tmp_topic_list[$v['topic_id']] = $content_link;
			}
		}

		$GLOBALS['tmpl']->assign("reply_list",$list);
		$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		if($ajax==0)
		{	
			$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_reply_list.html"));
			$GLOBALS['tmpl']->assign("list_html",$list_html);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_MYCOMMENT']);
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_MYCOMMENT']);			
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_index.html");
			$GLOBALS['tmpl']->display("uc.html");	
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_reply_list.html"));
		}
	}
	
	
	
	public function mayfocus()
	{
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['YOU_MAY_FOCUS']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_mayfocus.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
	public function fans()
	{
		$user_info =$GLOBALS['user_info'];
				
		$page_size = 24;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($GLOBALS['user_info']['id']);
		
		//输出粉丝
		$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id." order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id);
		
		
		$ids = array(0);
		foreach($fans_list as $k=>$v)
		{
			$ids[] = $v['id'];
		}
		$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id in (".implode(",", $ids).")");
		foreach($fans_list as $k=>$v)
		{
			foreach($focus_data as $kk=>$vv)
			{
				if($vv['focused_user_id']==$v['id'])
				{
					$fans_list[$k]['focused'] = 1;
					break;
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("fans_list",$fans_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['MY_FANS']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_fans.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	
	public function focus()
	{
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
				
		$page_size = 24;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($GLOBALS['user_info']['id']);
		
		//输出粉丝
		$focus_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id);
		
		foreach($focus_list as $k=>$v)
		{			
			$focus_list[$k]['focused'] = 1;
		}
		$GLOBALS['tmpl']->assign("focus_list",$focus_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['MY_FOCUS']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_focus.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	
	public function setweibo()
	{
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
				
		$apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login where is_weibo = 1");
		
		foreach($apis as $k=>$v)
		{
			if($user_info[strtolower($v['class_name'])."_id"])
			{
				$apis[$k]['is_bind'] = 1;
				if($user_info["is_syn_".strtolower($v['class_name'])]==1)
				{
					$apis[$k]['is_syn'] = 1;
				}
				else
				{
					$apis[$k]['is_syn'] = 0;
				}
			}
			else
			{
				$apis[$k]['is_bind'] = 0;
			}
			
//			if(file_exists(APP_ROOT_PATH."system/api_login/".$v['class_name']."_api.php"))
//			{
//				require_once APP_ROOT_PATH."system/api_login/".$v['class_name']."_api.php";
//				$api_class = $v['class_name']."_api";
//				$api_obj = new $api_class($v);
//				$url = $api_obj->get_bind_api_url();
//				$apis[$k]['url'] = $url;
//			}
		}		
		$GLOBALS['tmpl']->assign("apis",$apis);
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SETWEIBO']);		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_center_setweibo.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
}
?>