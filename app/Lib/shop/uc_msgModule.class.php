<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_msgModule extends ShopBaseModule
{
	public function index()
	{		 
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MSG']);
		$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_MSG']);	
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$user_id = intval($GLOBALS['user_info']['id']);
		$sql = "select group_key,count(group_key) as total from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
				group by group_key 
				order by system_msg_id desc,is_notice desc,max(create_time) desc limit ".$limit;
		$sql_count = "select count(distinct(group_key)) from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))";
		
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
			$list[$k] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_box where group_key = '".$v['group_key']."' and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  order by create_time desc limit 1");
			$list[$k]['total'] = $v['total'];
			
		}
		$count = $GLOBALS['db']->getOne($sql_count);
		$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("msg_list",$list);
		
	
		//end订单留言
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_msg_index.html");
		$GLOBALS['tmpl']->display("uc.html");	
	}
	
	public function deal()
	{
		$group_key = addslashes(trim($_REQUEST['id']));
		$user_id = intval($GLOBALS['user_info']['id']);
		$sql = "select count(*) as count,max(system_msg_id) as system_msg_id,max(id) as id,max(is_notice) as is_notice from ".DB_PREFIX."msg_box  
				where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
				and group_key = '".$group_key."'";
		$row = $GLOBALS['db']->getRow($sql);
		if($row['count']==0)
		{
			//没有消息对象， 直接创建消息
			//查出fans列表
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);				
			$page_size = 24;			
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*$page_size).",".$page_size;
			
			//输出粉丝
			$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id." order by id desc limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id);
			

			$GLOBALS['tmpl']->assign("fans_list",$fans_list);	
	
			$page = new Page($total,$page_size);   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['WRITE_PM']);	
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_msg_deal_send.html");
			$GLOBALS['tmpl']->display("uc.html");	
			
		}//end count==0
		elseif($row['system_msg_id']>0||$row['is_notice']==1)
		{
			//系统消息，仅查看
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SYSTEM_PM']);
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_box where id = ".$row['id']." and is_delete = 0");
			$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set is_read = 1 where id = ".$row['id']);
			$GLOBALS['tmpl']->assign("pm",$data);	
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_msg_deal_system.html");
			$GLOBALS['tmpl']->display("uc.html");
		}
		else
		{
			//消息记录
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$user_id = intval($GLOBALS['user_info']['id']);
			$sql = "select * from ".DB_PREFIX."msg_box  
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
					and group_key = '".$group_key."' 
					order by create_time desc limit ".$limit;
			$sql_count = "select count(*) from ".DB_PREFIX."msg_box  
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1)) and group_key = '".$group_key."'";
		
			$upd_sql = "update ".DB_PREFIX."msg_box set is_read = 1 
					where is_delete = 0 and ((to_user_id = ".$user_id." and `type` = 0) or (from_user_id = ".$user_id." and `type` = 1))  
					and group_key = '".$group_key."' ";
					
			$GLOBALS['db']->query($upd_sql);
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v)
			{
				if($v['to_user_id']!=$user_id)
				{
					$dest_user_id = $v['to_user_id'];
					break;
				}
				if($v['from_user_id']!=$user_id)
				{
					$dest_user_id = $v['from_user_id'];
					break;
				}
			}
			
			$dest_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$dest_user_id);
			$count = $GLOBALS['db']->getOne($sql_count);
			$page = new Page($count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			$GLOBALS['tmpl']->assign("msg_list",$list);
			
			$GLOBALS['tmpl']->assign("count",$count);	
			$GLOBALS['tmpl']->assign("dest_user_name",$dest_user_name);	
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PM_LIST']);	
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_msg_deal_list.html");
			$GLOBALS['tmpl']->display("uc.html");
		
		}
		
	}
}
?>