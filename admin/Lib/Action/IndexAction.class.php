<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class IndexAction extends AuthAction{
	//首页
    public function index(){
		$this->display();
    }
    

    //框架头
	public function top()
	{
		$navs = M("RoleNav")->where("is_effect=1 and is_delete=0")->order("sort asc")->findAll();
		$this->assign("navs",$navs);
		$this->display();
	}
	//框架左侧
	public function left()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$adm_id = intval($adm_session['adm_id']);
		
		$nav_id = intval($_REQUEST['id']);
		$nav_group = M("RoleGroup")->where("nav_id=".$nav_id." and is_effect = 1 and is_delete = 0")->order("sort asc")->findAll();		
		foreach($nav_group as $k=>$v)
		{
			$sql = "select role_node.`action` as a,role_module.`module` as m,role_node.id as nid,role_node.name as name from ".conf("DB_PREFIX")."role_node as role_node left join ".
				   conf("DB_PREFIX")."role_module as role_module on role_module.id = role_node.module_id ".
				   "where role_node.is_effect = 1 and role_node.is_delete = 0 and role_module.is_effect = 1 and role_module.is_delete = 0 and role_node.group_id = ".$v['id']." order by role_node.id asc";
			
			$nav_group[$k]['nodes'] = M()->query($sql);
		}
		$this->assign("menus",$nav_group);
		$this->display();
	}
	//默认框架主区域
	public function main()
	{
		//会员数
		$total_user = M("User")->count();
		$total_verify_user = M("User")->where("is_effect=1")->count();
		$this->assign("total_user",$total_user);
		$this->assign("total_verify_user",$total_verify_user);
		

		$deal_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 0 and is_shop = 0")->count();
		$goods_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 0 and is_shop = 1")->count();
		$daijin_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 0 and is_shop = 2")->count();		
		$score_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 1")->count();
		$youhui_count = M("Youhui")->where("is_effect=1")->count();
		$event_count = M("Event")->where("is_effect=1")->count();	
			
		
		$this->assign("deal_count",$deal_count);
		$this->assign("score_count",$score_count);
		$this->assign("goods_count",$goods_count);
		$this->assign("daijin_count",$daijin_count);		
		$this->assign("youhui_count",$youhui_count);
		$this->assign("event_count",$event_count);
		
		
		$topic_count = M("Topic")->where("is_effect = 1 and is_delete = 0 and relay_id = 0 and fav_id = 0")->count();		
		$dp_count = M("SupplierLocationDp")->where("status=1")->count();		
		$msg_count = M("Message")->where("is_buy = 0")->count();
		$buy_msg_count = M("Message")->where("is_buy = 1")->count();
		
		
		
		$this->assign("topic_count",$topic_count);
		$this->assign("dp_count",$dp_count);
		$this->assign("msg_count",$msg_count);
		$this->assign("buy_msg_count",$buy_msg_count);
		
		//订单数
		$order_count = M("DealOrder")->where("type = 0")->count();
		$this->assign("order_count",$order_count);
		
		$order_buy_count = M("DealOrder")->where("pay_status=2 and type = 0")->count();
		$this->assign("order_buy_count",$order_buy_count);
		
		//充值单数
		$incharge_order_buy_count = M("DealOrder")->where("pay_status=2 and type = 1")->count();
		$this->assign("incharge_order_buy_count",$incharge_order_buy_count);
		
		
		$income_amount = M("DealOrder")->sum("pay_amount");
		$refund_amount = M("DealOrder")->sum("refund_money");
		$this->assign("income_amount",$income_amount);
		$this->assign("refund_amount",$refund_amount);
		
		
		$reminder = M("RemindCount")->find();
		$reminder['topic_count'] = intval(M("Topic")->where("is_effect = 1 and is_delete = 0 and relay_id = 0 and fav_id = 0 and create_time >".$reminder['topic_count_time'])->count());
		$reminder['dp_count'] = intval(M("SupplierLocationDp")->where("status = 1 and create_time >".$reminder['dp_count_time'])->count());
		$reminder['msg_count'] = intval(M("Message")->where("is_buy = 0 and create_time >".$reminder['msg_count_time'])->count());
		$reminder['buy_msg_count'] = intval(M("Message")->where("is_buy = 1 and create_time >".$reminder['buy_msg_count_time'])->count());
		$reminder['order_count'] = intval(M("DealOrder")->where("is_delete = 0 and type = 0 and pay_status = 2 and create_time >".$reminder['order_count_time'])->count());
		$reminder['refund_count'] = intval(M("DealOrder")->where("is_delete = 0 and refund_status = 1 and create_time >".$reminder['refund_count_time'])->count());
		$reminder['retake_count'] = intval(M("DealOrder")->where("is_delete = 0 and retake_status = 1 and create_time >".$reminder['retake_count_time'])->count());
		$reminder['incharge_count'] = intval(M("DealOrder")->where("is_delete = 0 and type = 1 and pay_status = 2 and create_time >".$reminder['incharge_count_time'])->count());
		
		M("RemindCount")->save($reminder);
		$this->assign("reminder",$reminder);
		
		
		$this->display();
	}	
	//底部
	public function footer()
	{
		$this->display();
	}
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$this->assign("adm_data",$adm_session);
		$this->display();
	}
	public function do_change_password()
	{
		$adm_id = intval($_REQUEST['adm_id']);
		if(!check_empty($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_EMPTY_TIP"));
		}
		if(!check_empty($_REQUEST['adm_new_password']))
		{
			$this->error(L("ADM_NEW_PASSWORD_EMPTY_TIP"));
		}
		if($_REQUEST['adm_confirm_password']!=$_REQUEST['adm_new_password'])
		{
			$this->error(L("ADM_NEW_PASSWORD_NOT_MATCH_TIP"));
		}		
		if(M("Admin")->where("id=".$adm_id)->getField("adm_password")!=md5($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_ERROR"));
		}
		M("Admin")->where("id=".$adm_id)->setField("adm_password",md5($_REQUEST['adm_new_password']));
		save_log(M("Admin")->where("id=".$adm_id)->getField("adm_name").L("CHANGE_SUCCESS"),1);
		$this->success(L("CHANGE_SUCCESS"));
		
		
	}
	
	public function reset_sending()
	{
		$field = trim($_REQUEST['field']);
		if($field=='DEAL_MSG_LOCK'||$field=='PROMOTE_MSG_LOCK'||$field=='APNS_MSG_LOCK')
		{
			M("Conf")->where("name='".$field."'")->setField("value",'0');
			$this->success(L("RESET_SUCCESS"),1);
		}
		else
		{
			$this->error(L("INVALID_OPERATION"),1);
		}
	}
}
?>