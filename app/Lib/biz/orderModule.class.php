<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class orderModule extends BizBaseModule
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
			
		$order_sn = htmlspecialchars(addslashes(trim($_REQUEST['order_sn'])));
		$coupon_sn = htmlspecialchars(addslashes(trim($_REQUEST['coupon_sn'])));
		
		if($_REQUEST['is_redirect']==1)
		{
			$url_param=array("order_sn"=>$order_sn,"coupon_sn"=>$coupon_sn);
			app_redirect(url("biz","order",$url_param));
		}
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_LIST']);
		
		$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($s_account_info['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		
		
		$deal_id = intval($_REQUEST['id']);
		
		if($deal_id>0)
		{
			$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$deal_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
			$deal_info = $GLOBALS['db']->getRow($sql);
			if(!$deal_info)
			{
				showErr($GLOBALS['lang']['NO_AUTH']);	
			}
		}
		
		$ext_where = ' and do.is_delete = 0 and do.after_sale = 0';	
		if($order_sn != '')
		{
			$ext_where.= " and do.order_sn like '%".$order_sn."%' ";
			$GLOBALS['tmpl']->assign("order_sn",$order_sn);
		}
		if($coupon_sn != '')
		{
			$ext_where.= " and do.id in (select order_id from ".DB_PREFIX."deal_coupon where sn like '%".$coupon_sn."%')";
			$GLOBALS['tmpl']->assign("coupon_sn",$coupon_sn);
		}
		if($deal_id>0)
		{
			$ext_where.=" and doi.deal_id = ".$deal_id;
			$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		}
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		$order_list_sql = "select distinct(do.id) as oid,do.user_id,do.order_sn,do.create_time as ocreate_time,doi.name,doi.sub_name,d.* from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where." group by do.id order by do.create_time desc limit ".$limit;
		$order_list_count_sql = "select count(distinct(do.id)) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where;
		
	
		$order_list = $GLOBALS['db']->getAll($order_list_sql);
		foreach($order_list as $k=>$v)
		{
			$order_list[$k]['number'] = $GLOBALS['db']->getOne("select sum(number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where do.id = ".$v['oid']);
		}
		$GLOBALS['tmpl']->assign('order_list',$order_list);
	
		
		$order_count = $GLOBALS['db']->getOne($order_list_count_sql);
		$page = new Page($order_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_order.html");
	}
	
	public function view()
	{
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_VIEW']);
		
		$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($s_account_info['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getAll("select distinct(do.id),do.*,doi.name,doi.sub_name,doi.number,doi.delivery_status,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.id = ".$order_id." and d.supplier_id = ".$supplier_id." and do.is_delete = 0 and do.pay_status = 2");
	
		if($order_info)
		{
			$GLOBALS['tmpl']->assign("order_info",$order_info[0]);
			$GLOBALS['tmpl']->assign("order_goods",$order_info);
			$GLOBALS['tmpl']->display("biz/biz_order_view.html");
		}
		else
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_MATCH'],0);
		}
	}
	
	
	public function do_delivery()
	{
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$account_data = $GLOBALS['db']->getRow("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		if(intval($account_data['allow_delivery'])==0)
		{
			showErr($GLOBALS['lang']['NO_DELIVERY_AUTH']);		
		}
			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_DELIVERY']);
		
		$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($s_account_info['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getAll("select distinct(do.id), do.*,doi.name,doi.sub_name,doi.number,doi.delivery_status,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.id = ".$order_id." and d.supplier_id = ".$supplier_id." and do.is_delete = 0 and do.pay_status = 2 and d.is_delivery = 1");
		if($order_info)
		{
			$GLOBALS['tmpl']->assign("order_info",$order_info[0]);
			$GLOBALS['tmpl']->assign("order_goods",$order_info);
			
			
			//输出快递接口
			$express_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."express where is_effect = 1");			
			$GLOBALS['tmpl']->assign("express_list",$express_list);
			
			$GLOBALS['tmpl']->display("biz/biz_order_do_delivery.html");
		}
		else
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_MATCH'],0);
		}
	}
	
	
	public function do_delivery_form()
	{
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$account_data = $GLOBALS['db']->getRow("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		if(intval($account_data['allow_delivery'])==0)
		{
			showErr($GLOBALS['lang']['NO_DELIVERY_AUTH']);		
		}
		
		
		$order_id = intval($_REQUEST['order_id']);
		$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($s_account_info['id'])));
		$order_info = $GLOBALS['db']->getAll("select do.*,doi.name,doi.sub_name,doi.number,doi.delivery_status,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.id = ".$order_id." and d.supplier_id = ".$supplier_id." and do.is_delete = 0 and do.pay_status = 2 and d.is_delivery = 1");
		if(!$order_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);
		}
		
		$order_deals = $_REQUEST['order_deals'];
		$delivery_sn = htmlspecialchars(addslashes($_REQUEST['delivery_sn']));
		$express_id = intval($_REQUEST['express_id']);
		$memo = htmlspecialchars(addslashes($_REQUEST['memo']));
			if(!$order_deals)
			{
				showErr($GLOBALS['lang']["PLEASE_SELECT_DELIVERY_ITEM"]);
			}
			else
			{
				$deal_names = array();
				foreach($order_deals as $order_deal_id)
				{
					$order_deal_id = intval($order_deal_id);
					$deal_info =$GLOBALS['db']->getOne("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_order_item as doi on doi.deal_id = d.id where doi.id = ".$order_deal_id);
				
					$deal_name =$deal_info['sub_name'];
					array_push($deal_names,$deal_name);
					$rs = make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo,$express_id);
					if($rs)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1 where id = ".$order_deal_id);
						update_balance($order_deal_id,$deal_info['id']);
					}
				}
				$deal_names = implode(",",$deal_names);
				
				send_delivery_mail($delivery_sn,$deal_names,$order_id);
				send_delivery_sms($delivery_sn,$deal_names,$order_id);
				//开始同步订单的发货状态
				$order_deal_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
				foreach($order_deal_items as $k=>$v)
				{
					
					if(intval($GLOBALS['db']->getOne("select is_delivery from ".DB_PREFIX."deal where id = ".$v['deal_id']))==0) //无需发货的商品
					{
						unset($order_deal_items[$k]);
					}				
				}
				$delivery_deal_items = $order_deal_items;
				foreach($delivery_deal_items as $k=>$v)
				{
					if($v['delivery_status']==0) //未发货去除
					{
						unset($delivery_deal_items[$k]);
					}				 
				}
				
	
				if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0 where id = ".$order_id); //未发货
				}
				elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1 where id = ".$order_id); //部分发
				}
				else
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2 where id = ".$order_id); //全部发
				}		
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set update_time = '".get_gmtime()."' where id = ".$order_id);
				
				
				order_log($account_data['name'].$account_data['account_name'].":".$GLOBALS['lang']["DELIVERY_SUCCESS"].$delivery_sn.$_REQUEST['memo'],$order_id);
				
				showSuccess($GLOBALS['lang']["DELIVERY_SUCCESS"],0,url("biz","order#view",array("id"=>$order_id)));
			}
	}
	
	public function export_order(){
		$s_account_info = es_session::get("account_info");
		$deal_id = intval($_REQUEST['id']);	
		$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$deal_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$deal_info = $GLOBALS['db']->getRow($sql);
		if(!$deal_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);	
		}
			
		export_order(1,$deal_id);
	}

}



function export_order($page = 1,$deal_id=0)
{
	set_time_limit(0);
	$s_account_info = es_session::get("account_info");
	$account_id = intval($s_account_info['id']);
	$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($s_account_info['id'])));

	
	$ext_where = ' and do.is_delete = 0 and do.after_sale = 0';		
	if($deal_id>0)
	{
		$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$deal_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$deal_info = $GLOBALS['db']->getRow($sql);
		if(!$deal_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);	
		}
		$ext_where.=" and doi.deal_id = ".$deal_id;
	}
	else
	{
		showErr($GLOBALS['lang']['PLEASE_SPEC_DEAL']);	
	}
	
	//分页
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");		
	$order_list_sql = "select do.*,doi.number".
					  " from ".DB_PREFIX."deal_order_item as doi left join ".
					  DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".
					  DB_PREFIX."deal as d on doi.deal_id = d.id where do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where.
					  " group by do.id order by do.create_time desc limit ".$limit;
	$list = $GLOBALS['db']->getAll($order_list_sql);
	
	if($list)
	{
		register_shutdown_function("export_order", $page+1,$deal_id);
		$order_value = array('sn'=>'""', 'user_name'=>'""', 'deal_name'=>'""','number'=>'""', 'create_time'=>'""', 'consignee'=>'""', 'address'=>'""','zip'=>'""','email'=>'""', 'mobile'=>'""', 'memo'=>'""');
	    if($page == 1)
	    {
		    	$content = iconv("utf-8","gbk","订单编号,用户名,产品及团购券,订购总数量,下单时间,收货人,发货地址,邮编,用户邮件,手机号码,订单留言");	    		    	
		    	$content = $content . "\n";
	    }
	    
		foreach($list as $k=>$v)
		{
				
				$order_value['sn'] = '"' . "sn:".iconv('utf-8','gbk',$v['order_sn']) . '"';
				$order_value['user_name'] = '"' . iconv('utf-8','gbk',$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id'])) . '"';
				
				
				//获取相应的团购名称，数量与团购券
				$deal_order_item = $GLOBALS['db']->getAll("select doi.* from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.order_id = ".$v['id']." and d.supplier_id = ".$supplier_id." and d.id = ".intval($deal_id));
				$str = '';
				foreach($deal_order_item as $kk=>$vv)
				{
					$str .=$vv['sub_name']."[数量：".$vv['number'];	
					
					$coupon_list = $GLOBALS['db']->getAll("select sn,confirm_account,confirm_time,begin_time,end_time from ".DB_PREFIX."deal_coupon where order_deal_id = ".$vv['id']." and is_valid = 1");
					if($coupon_list)
					{
						$str.=" 团购券：";
						foreach($coupon_list as $kkk=>$vvv)
						{
							$str.=$vvv['sn'];
							if($vvv['confirm_account']!=0)
							{
								$account_name = $GLOBALS['db']->getOne("select account_name from ".DB_PREFIX."supplier_account where id = ".$vvv['confirm_account']);
								$str.= " (".to_date($vvv['confirm_time']).") ".$GLOBALS['lang']['COUPON_USED'];						
							}
							else
							{
								if($vvv['begin_time']!=0&&$vvv['begin_time']>get_gmtime())
								{
									$str.= " (".$GLOBALS['lang']['COUPON_NOT_BEGIN'].")";
								}
								
								if($vvv['end_time']!=0&&$vvv['end_time']<get_gmtime())
								{
									$str.= " (".$GLOBALS['lang']['COUPON_ENDED'].")";
								}
							}
							
							$str.=",";
						}
						$str = substr($str,0,-1);
						$str.="]";
					}
					else
					{
						$str.=$GLOBALS['lang']['NO_COUPON_GEN']."]";
					}
				}
				
				//end
				
				$order_value['deal_name'] = '"' . iconv('utf-8','gbk',$str) . '"';
				$order_value['number'] = '"' . iconv('utf-8','gbk',$v['number']) . '"';					
				$order_value['create_time'] = '"' . iconv('utf-8','gbk',to_date($v['create_time'])) . '"';				
				$order_value['consignee'] = '"' . iconv('utf-8','gbk',$v['consignee']) . '"';
				
				$region_lv1_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv1']);
				$region_lv2_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv2']);
				$region_lv3_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv3']);
				$region_lv4_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv4']);
				$address = $region_lv1_name.$region_lv2_name.$region_lv3_name.$region_lv4_name.$v['address'];
				$order_value['address'] = '"' . iconv('utf-8','gbk',$address) . '"';
				$order_value['zip'] = '"' . iconv('utf-8','gbk',$v['zip']) . '"';
				$order_value['email'] = '"' . iconv('utf-8','gbk',$v['email']) . '"';
				$order_value['mobile'] = '"' . iconv('utf-8','gbk',$v['mobile']) . '"';
				$order_value['memo'] = '"' . iconv('utf-8','gbk',$v['memo']) . '"';
				
				$content .= implode(",", $order_value) . "\n";
		}
		header("Content-Disposition: attachment; filename=order_list.csv");
	    echo $content;
	}
	else
	{
			if($page==1)
			showErr($GLOBALS['lang']["NO_RESULT"]);
	}	
	
}
?>