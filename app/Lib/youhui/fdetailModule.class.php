<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class fdetailModule extends YouhuiBaseModule
{
	public function index()
	{		
		if(check_ipop_limit(get_client_ip(),"view_youhui",intval(app_conf("SUBMIT_DELAY")),trim($_REQUEST['id'])))
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."youhui set view_count = view_count + 1 where id = ".intval($_REQUEST['id']));
		}
		$preview = intval($_REQUEST['preview']);
		$GLOBALS['tmpl']->caching = false;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id'].$preview);		
		if (!$GLOBALS['tmpl']->is_cached('youhui_fdetail.html', $cache_id))	
		{
			$youhui_id = intval($_REQUEST['id']);
			if($preview>0)
			{
				$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id =".$youhui_id);						
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
						$deal_test = $GLOBALS['db']->getRow("select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where y.id = ".intval($youhui_info['id'])." and y.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
						if(!$deal_test)
						{
							showErr("优惠不存在或者没有预览该优惠的权限",0,APP_ROOT."/admin.php?m=Public&a=login");
						}
					}
					else
					{
						showErr("您不是系统管理员或者商家会员，无法预览",0,APP_ROOT."/");
					}
				}		
			}
			else
			$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);
			if(!$youhui_info)
			{
				showErr("非法的优惠券ID");
			}
			$youhui_info['supplier_info'] =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($youhui_info['supplier_id'])."");
			//print_r($youhui_info);die();
			$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);
			
			
			//供应商的地址列表
			//定义location_id
			//$locations = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($youhui_info['supplier_id'])." order by is_main desc");
			$locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."youhui_location_link as b on a.id = b.location_id where a.is_effect = 1 and b.youhui_id = ".intval($youhui_info['id']));
			
			$json_location = array();
			$location_ids = array(0);
			foreach($locations as $litem)
			{
				$location_ids[] = $litem['id'];
				$arr = array();
				$arr['title'] = $litem['name'];
				$arr['address'] = $litem['address'];
				$arr['tel'] = $litem['tel'];
				$arr['lng'] = $litem['xpoint'];
				$arr['lat'] = $litem['ypoint'];
				$json_location[] = $arr;
			}
				
			$GLOBALS['tmpl']->assign("json_location",json_encode($json_location));
			$GLOBALS['tmpl']->assign("locations",$locations);
			
			//周边热卖
			$areas = $GLOBALS['db']->getAll("select a.name from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id in (".implode(",",$location_ids).")");
			$condition_arr=array();
			foreach($areas as $area)
			{
				$condition_arr[] = str_to_unicode_string($area['name']);
			}			
			$condition =" (match(locate_match) against('".implode(" ",$condition_arr)."' IN BOOLEAN MODE)) and id <> ".intval($youhui_info['id']);								
			$near_youhui = get_free_youhui_list(4,0,$condition,"");	
			$GLOBALS['tmpl']->assign("near_youhui_list",$near_youhui['list']);
			
			$rec_youhui = get_free_youhui_list(4,$youhui_info['deal_cate_id'],"is_recommend = 1","");
			$GLOBALS['tmpl']->assign("rec_youhui_list",$rec_youhui['list']);
			
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where id = ".$youhui_info['deal_cate_id']);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$cate_item['name'],'url'=>url("youhui","fcate#index",array("cid"=>$cate_item['id'])));
			$site_nav[] = array('name'=>$youhui_info['name'],'url'=>url("youhui","fdetail",array("id"=>$youhui_info['id'])));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$seo_title = $youhui_info['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $youhui_info['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $youhui_info['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		}
		$GLOBALS['tmpl']->display("youhui_fdetail.html",$cache_id);
	}
	
	public function load_sms()
	{		
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status']=0;
			$result['html']= $GLOBALS['tmpl']->fetch("inc/login_form.html");		
			ajax_return($result);
		}
		else
		{
			$user_id = intval($GLOBALS['user_info']['id']);
			$now = get_gmtime();
			$today_begin = to_timespan(to_date($now,"Y-m-d"));
			$today_end = $today_begin + 24*3600;
			//一天内已经发送的下载的短信条数
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where user_id = ".$user_id." and is_youhui = 1 and create_time between ".$today_begin." and ".$today_end);
			
			$youhui_id = intval($_REQUEST['id']);
			$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);
			
			if(!$youhui_info)
			{
				header("Content-Type:text/html; charset=utf-8");
				echo $GLOBALS['lang']['YOUHUI_NO_EXIST'];
				exit;
			}
			
			if($youhui_info['end_time']>0&&$youhui_info['end_time']<get_gmtime())
			{
				header("Content-Type:text/html; charset=utf-8");
				echo $GLOBALS['lang']['YOUHUI_END_TIME_OVER'];
				exit;
			}
			
			
			$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);
			$GLOBALS['tmpl']->assign("send_count",$count);
			$GLOBALS['tmpl']->assign("send_limit",app_conf("YOUHUI_SEND_LIMIT"));
			
			$result['status']=1;
			$result['html']= $GLOBALS['tmpl']->fetch("inc/youhui_sms_page.html");			
			
			ajax_return($result);
		}
	}
	
	public function send_sms()
	{
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status']=0;
			$result['html']= $GLOBALS['tmpl']->fetch("inc/login_form.html");		
			ajax_return($result);
		}
		else
		{
			$result['status'] = 1;
			$user_id = intval($GLOBALS['user_info']['id']);
			$mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));
			
			$now = get_gmtime();
			$today_begin = to_timespan(to_date($now,"Y-m-d"));
			$today_end = $today_begin + 24*3600;
		    //手机号每日下载限额
			$tel_send_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where dest = ".$mobile." and is_youhui = 1 and create_time between ".$today_begin." and ".$today_end);
			//用户每日下载限额
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where user_id = ".$user_id." and is_youhui = 1 and create_time between ".$today_begin." and ".$today_end);
			if(($count>=intval(app_conf("YOUHUI_SEND_LIMIT"))) || ($tel_send_count>=intval(app_conf("YOUHUI_SEND_TEL_LIMIT"))))
			{
				 $result['info'] = $GLOBALS['lang']['SMS_LIMIT_OVER'];
				 ajax_return($result);
			}
			else
			{
				$youhui_id = intval($_REQUEST['id']);
				$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);
				
				if(!$youhui_info)
				{
					showErr($GLOBALS['lang']['YOUHUI_NO_EXIST'],1);
				}
				
				if($youhui_info['end_time']>0&&$youhui_info['end_time']<get_gmtime())
				{
					showErr($GLOBALS['lang']['YOUHUI_END_TIME_OVER'],1);
				}
				
				if($youhui_info['is_sms']==0)
				{
					showErr($GLOBALS['lang']['YOUHUI_NO_SUPPORT_SMS'],1);
				}
				
				if($youhui_info['send_type']==0)
				{
					if(send_youhui_sms($youhui_id,$user_id,$mobile))
					{
						$result['info'] = $GLOBALS['lang']['SMS_SEND_SUCCESS'];
						ajax_return($result);
					}
					else
					{
						$result['info'] = $GLOBALS['lang']['SMS_SEND_FAILED'];
						ajax_return($result);
					}
				}
				else
				{
					//开始需要生成验证的券
					$order_count = intval($_REQUEST['order_count']);
					$is_private_room = intval($_REQUEST['is_private_room']);
					$date_time = addslashes(trim(htmlspecialchars($_REQUEST['date_time'])))." ".addslashes(trim(htmlspecialchars($_REQUEST['date_time_h']))).":".addslashes(trim(htmlspecialchars($_REQUEST['date_time_m']))).":00";
					$date_time = to_timespan($date_time);
					
					$log_id = gen_verify_youhui($youhui_id,$mobile,$user_id,$order_count,$is_private_room,$date_time);
					if($log_id)
					{
						if(send_youhui_log_sms($log_id))
						{
							$result['info'] = $GLOBALS['lang']['SMS_SEND_SUCCESS'];
							ajax_return($result);
						}
						else
						{
							$result['info'] = $GLOBALS['lang']['SMS_SEND_FAILED'];
							ajax_return($result);
						}
					}
					else
					{
						$result['info'] = $GLOBALS['lang']['SMS_SEND_FAILED'];
						ajax_return($result);
					}
				}
			}			
		}
	}
	
	public function fprint()
	{
		$youhui_id = intval($_REQUEST['id']);
		$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);		
		
		if(!$youhui_info)
		{
			showErr($GLOBALS['lang']['YOUHUI_NO_EXIST']);
		}
		
		if($youhui_info['is_print']==0)
		{
			showErr($GLOBALS['lang']['YOUHUI_NO_SUPPORT_PRINT']);
		}
		
		if($youhui_info['end_time']>0&&$youhui_info['end_time']<get_gmtime())
		{
			showErr($GLOBALS['lang']['YOUHUI_END_TIME_OVER']);
		}
		
		$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);		
		$GLOBALS['db']->query("update ".DB_PREFIX."youhui set print_count = print_count +1,view_count = view_count +1 where id = ".$youhui_info['id']);
					
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PRINT']);
		$GLOBALS['tmpl']->display("youhui_fprint.html");
	}
}

?>