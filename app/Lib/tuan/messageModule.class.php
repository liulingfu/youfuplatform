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
require APP_ROOT_PATH.'app/Lib/page.php';
class messageModule extends TuanBaseModule
{
	public function index()
	{				
		global $tmpl;
		$rel_table = addslashes(trim($_REQUEST['act']));
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."' and type_name <> 'supplier'");
		if(!$message_type||$message_type['is_fix']==0)
		{
			app_redirect(APP_ROOT."/");
		}
		$rel_table = $message_type['type_name'];
		$condition = '';	
		$id = intval($_REQUEST['id']);
		if($rel_table == 'deal')
		{
			$deal = get_deal($id);
			if($deal['buy_type']!=1)
			$GLOBALS['tmpl']->assign("deal",$deal);
			$id = $deal['id'];
		}
		require APP_ROOT_PATH.'app/Lib/side.php'; 
		if($id>0)
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
		else
		$condition = "rel_table = '".$rel_table."'";
	
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
		
		$condition.=" and is_buy = ".intval($_REQUEST['is_buy']);
		//message_form 变量输出
		$GLOBALS['tmpl']->assign("post_title",$message_type['show_name']);
		$GLOBALS['tmpl']->assign("page_title",$message_type['show_name']);
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',$rel_table);
		$GLOBALS['tmpl']->assign('is_buy',intval($_REQUEST['is_buy']));
		
		if(intval($_REQUEST['is_buy'])==1)
		{
			$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['AFTER_BUY']);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['AFTER_BUY']);		
		}
		
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	
		$message = get_message_list($limit,$condition);
		
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		$GLOBALS['tmpl']->assign("user_auth",get_user_auth());
		$GLOBALS['tmpl']->display("message.html");
	}
	
	public function add()
	{
		global $user_info;
		$ajax = intval($_REQUEST['ajax']);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		
		$rel_table = addslashes(trim($_REQUEST['rel_table']));
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."' and type_name <> 'supplier'");
		if(!$message_type)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE'],$ajax);
		}
		
		$message_group = addslashes(trim($_REQUEST['message_group']));
	
		//添加留言
		$message['title'] = $_REQUEST['title']?htmlspecialchars(addslashes(valid_str($_REQUEST['title']))):htmlspecialchars(addslashes(valid_str($_REQUEST['content'])));
		$message['content'] = htmlspecialchars(addslashes(valid_str($_REQUEST['content'])));
		$message['title'] = valid_str($message['title']);
		if($message_group)
		{
			$message['title']="[".$message_group."]:".$message['title'];
			$message['content']="[".$message_group."]:".$message['content'];
		}
		
		$message['create_time'] = get_gmtime();
		$message['rel_table'] = $rel_table;
		$rel_id = $message['rel_id'] = addslashes(trim($_REQUEST['rel_id']));
		$message['user_id'] = intval($GLOBALS['user_info']['id']);
		if(intval($_REQUEST['city_id'])==0)
		$message['city_id'] = $GLOBALS['deal_city']['id'];
		else
		$message['city_id'] = intval($_REQUEST['city_id']);
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$message_effect = 0;
		}
		else
		{
			$message_effect = $message_type['is_effect'];
		}
		$message['is_effect'] = $message_effect;
		
		$message['is_buy'] = intval($_REQUEST['is_buy']);
		$message['contact'] = $_REQUEST['contact']?htmlspecialchars(addslashes($_REQUEST['contact'])):'';
		$message['contact_name'] = $_REQUEST['contact_name']?htmlspecialchars(addslashes($_REQUEST['contact_name'])):'';
		if($message['is_buy']==1)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".intval($message['rel_id'])." and do.user_id = ".intval($message['user_id'])." and do.pay_status = 2")==0)
			{
				showErr($GLOBALS['lang']['AFTER_BUY_MESSAGE_TIP'],$ajax);
			}
		}
		$message['point'] = intval($_REQUEST['point']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
		$message_id = intval($GLOBALS['db']->insert_id());
		if($message['is_buy']==1)
		{			
				$message_id = $GLOBALS['db']->insert_id();
				$attach_list=get_topic_attach_list(); 
				
				$deal_info = $GLOBALS['db']->getRow("select id,is_shop,name,sub_name from ".DB_PREFIX."deal where id = ".$rel_id);
				if($deal_info['is_shop']==0)
				{
					$url_route = array(
						'rel_app_index'	=>	'tuan',
						'rel_route'	=>	'deal',
						'rel_param' => 'id='.$deal_info['id']
					);
					$type = "tuancomment";
					
					$locations = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_location_link where deal_id = ".$deal_info['id']);
					$dp_title = "对".$deal_info['sub_name']."的消费点评";
					foreach($locations as $location)
					{				
						insert_dp($dp_title,$message['content'],$location['location_id'],$message['point'],$is_buy=1,$from="tuan",$url_route,$message_id);
					}
				}
				if($deal_info['is_shop']==1)
				{
					$url_route = array(
						'rel_app_index'	=>	'shop',
						'rel_route'	=>	'goods',
						'rel_param' => 'id='.$deal_info['id']
					);
					$type="shopcomment";
				}
				if($deal_info['is_shop']==2)
				{
					$url_route = array(
						'rel_app_index'	=>	'youhui',
						'rel_route'	=>	'ydetail',
						'rel_param' => 'id='.$deal_info['id']
					);
					$type="youhuicomment";
				}
				increase_user_active(intval($GLOBALS['user_info']['id']),"点评了一个团购");
				$title = "对".$deal_info['sub_name']."发表了点评";
				$tid = insert_topic($message['content'],$title,$type,"share",$relay_id = 0,$fav_id = 0,$group_data="",$attach_list=array(),$url_route);
				if($tid)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
				}

		}
			
		showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS'],$ajax);
	}
}
?>