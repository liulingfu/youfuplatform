<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

	require_once APP_ROOT_PATH."app/Lib/page.php";
	if($GLOBALS['user_info'])
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		$c_user_info = $GLOBALS['user_info'];
		$c_user_info['user_group'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".intval($GLOBALS['user_info']['group_id']));
		$GLOBALS['tmpl']->assign("user_info",$c_user_info);
		
		//签到数据
		$t_begin_time = to_timespan(to_date(get_gmtime(),"Y-m-d"));  //今天开始
		$t_end_time = to_timespan(to_date(get_gmtime(),"Y-m-d"))+ (24*3600 - 1);  //今天结束
		$y_begin_time = $t_begin_time - (24*3600); //昨天开始
		$y_end_time = $t_end_time - (24*3600);  //昨天结束
		
		$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time);
		if($t_sign_data)
		{			
			$GLOBALS['tmpl']->assign("t_sign_data",$t_sign_data);
		}
		else
		{
			$y_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$y_begin_time." and ".$y_end_time);
			$total_signcount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_sign_log where user_id = ".$user_id);
			if($y_sign_data&&$total_signcount>=3)
			{				
				$tip = "";
				if(doubleval(app_conf("USER_LOGIN_KEEP_MONEY"))>0)
				$tip .= "资金+".format_price(app_conf("USER_LOGIN_KEEP_MONEY"));
				if(intval(app_conf("USER_LOGIN_KEEP_SCORE"))>0)
				$tip .= "积分+".format_score(app_conf("USER_LOGIN_KEEP_SCORE"));
				if(intval(app_conf("USER_LOGIN_KEEP_POINT"))>0)
				$tip .= "经验+".(app_conf("USER_LOGIN_KEEP_POINT"));
				$GLOBALS['tmpl']->assign("sign_tip",$tip);					
			}
			else
			{
				if(!$y_sign_data)
				$GLOBALS['db']->query("delete from ".DB_PREFIX."user_sign_log where user_id = ".$user_id);
				$tip = "";
				if(doubleval(app_conf("USER_LOGIN_MONEY"))>0)
				$tip .= "资金+".format_price(app_conf("USER_LOGIN_MONEY"));
				if(intval(app_conf("USER_LOGIN_SCORE"))>0)
				$tip .= "积分+".format_score(app_conf("USER_LOGIN_SCORE"));
				if(intval(app_conf("USER_LOGIN_POINT"))>0)
				$tip .= "经验+".(app_conf("USER_LOGIN_POINT"));
				$GLOBALS['tmpl']->assign("sign_tip",$tip);
			}
			$GLOBALS['tmpl']->assign("sign_day",$total_signcount);
			$GLOBALS['tmpl']->assign("y_sign_data",$y_sign_data);
		}
	}
	else
	{
		if($_REQUEST['ajax']==1)
		{
			ajax_return(array("status"=>0,"info"=>"请先登录"));
		}
		else
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
	}
	
	//查询会员日志
	function get_user_log($limit,$user_id,$t='')
	{
		if(!in_array($t,array("money","score","point")))
		{
			$t = "";
		}
		if($t=='')
		{
			$condition = "";
		}
		else
		{
			$condition = " and ".$t." <> 0 ";
		}
	
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where user_id = ".$user_id." $condition order by log_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where user_id = ".$user_id." $condition");
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员充值订单
	function get_user_incharge($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0 order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0");
		foreach($list as $k=>$v)
		{
			$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
			$list[$k]['payment'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$v['payment_id']);
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员的团购券
	function get_user_coupon($limit,$user_id,$status=0)
	{
		$user_id = intval($user_id);
		$ext_condition = '';
		if($status==1)
		{
			$ext_condition = " and confirm_time = 0 ";
		}
		if($status==2)
		{
			$ext_condition = " and confirm_time <> 0 ";
		}
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition." order by order_id desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition);
		foreach($list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select forbid_sms from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				//禁止发券时，将已发数改为上限
				$list[$k]['sms_count'] = app_conf("SMS_COUPON_LIMIT");
			}
			$list[$k]['deal_item'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$v['order_deal_id']);
			if(!$list[$k]['deal_item']['name'])
			{
				$list[$k]['deal_item']['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			}
		}
		return array("list"=>$list,'count'=>$count);		
	}
	
	
	//查询会员订单
	function get_user_order($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0 order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0");
		foreach($list as $k=>$v)
		{
			$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员抽奖
	function get_user_lottery($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lottery where user_id = ".$user_id." order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where user_id = ".$user_id);
		foreach($list as $k=>$v)
		{
			$list[$k]['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			$list[$k]['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			if($v['buyer_id']==0)
			{
				$buyer = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
			}
			else
			{
				$buyer = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['buyer_id']);
			}
			$list[$k]['buyer'] = $buyer;
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员邀请及返利列表
	function get_invite_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql = "select u.user_name as i_user_name,u.referral_count as i_referral_count,u.create_time as i_reg_time,o.order_sn as i_order_sn,r.create_time as i_referral_time, r.pay_time as i_pay_time,r.money as i_money,r.score as i_score from ".DB_PREFIX."user as u left join ".DB_PREFIX."referrals as r on u.id = r.rel_user_id and u.pid = r.user_id left join ".DB_PREFIX."deal_order as o on r.order_id = o.id where u.pid = ".$user_id." limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."user where pid = ".$user_id;
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询代金券列表
	function get_voucher_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql = "select * from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." order by e.id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."ecv where user_id = ".$user_id;
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询可兑换代金券列表
	function get_exchange_voucher_list($limit)
	{
		$sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1";
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	function get_collect_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql = "select d.*,c.create_time as add_time ,c.id as cid from ".DB_PREFIX."deal_collect as c left join ".DB_PREFIX."deal as d on d.id = c.deal_id where c.user_id = ".$user_id." order by c.create_time desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."deal_collect where user_id = ".$user_id;
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	function set_uc_right()
	{
		//获取可以相关的用户	
		$user_id = intval($GLOBALS['user_info']['id']);	
		$user_list = get_rand_user(5,0,$user_id);	
		$GLOBALS['tmpl']->assign("user_list",$user_list);	
		
		
		//输出粉丝
		$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id." limit 5");
		
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
		
		
		//输出我的关注
		$focus_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." limit 5");
		$ids = array(0);
		foreach($focus_list as $k=>$v)
		{
			$ids[] = $v['id'];
		}
		$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id in (".implode(",", $ids).")");
		
		foreach($focus_list as $k=>$v)
		{
			foreach($focus_data as $kk=>$vv)
			{
				if($vv['focused_user_id']==$v['id'])
				{
					$focus_list[$k]['focused'] = 1;
					break;
				}
			}
		}
		$GLOBALS['tmpl']->assign("focus_list",$focus_list);	
		
		
		$res = load_dynamic_cache("topic_search_hot");
		if($res===false)
		{
			$res['hot_tag_list'] =$GLOBALS['db']->getAll("select name,color from ".DB_PREFIX."topic_tag where is_recommend = 1 order by sort desc, count desc limit 10");
			$res['hot_title_list'] =$GLOBALS['db']->getAll("select name,color from ".DB_PREFIX."topic_title where is_recommend = 1 order by sort desc,count desc limit 10");
			set_dynamic_cache("topic_search_hot", $res);
		}
		
		//输出搜索热词
		
		$GLOBALS['tmpl']->assign("hot_tag_list",$res['hot_tag_list']);
		$GLOBALS['tmpl']->assign("hot_title_list",$res['hot_title_list']);

		
		//输出推荐分享
		$recommend_topic = load_auto_cache("recommend_uc_topic");
		$GLOBALS['tmpl']->assign("recommend_topic",$recommend_topic);

		$GLOBALS['tmpl']->assign("has_right",1);
	}
?>