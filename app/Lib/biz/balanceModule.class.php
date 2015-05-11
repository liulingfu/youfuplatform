<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class balanceModule extends BizBaseModule
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
		$GLOBALS['tmpl']->assign("page_title","结算报表");
		
		$deal_id = intval($_REQUEST['deal_id']);		
		$is_balance = intval($_REQUEST['is_balance']);
		
		if($_REQUEST['is_redirect']==1)
		{
			$url_param=array("deal_id"=>$deal_id,"is_balance"=>$is_balance);
			app_redirect(url("biz","balance",$url_param));
		}

		$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".$deal_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$deal_info = $GLOBALS['db']->getRow($sql);
		//==========
		
		$GLOBALS['tmpl']->assign("is_balance",$is_balance);
		
		if($deal_info)
		{			
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

			$GLOBALS['tmpl']->assign("deal_info",$deal_info);
			if($deal_info['is_coupon']==1)
			{		
				if($is_balance==2)
				{
					$sort = " order by balance_time desc ";
				}	
				else
				{
					$sort = " order by id desc ";
				}	
				$condition = " deal_id = ".$deal_info['id']." and is_delete = 0 and user_id > 0 and is_valid = 1 ";
				$dataList = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where ".$condition." and is_balance = ".$is_balance.$sort." limit ".$limit);
				$dataTotal = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where ".$condition." and is_balance = ".$is_balance);
			
				foreach($dataList as $k=>$v)
				{
					$dataList[$k]['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$v['order_deal_id']);
					if(!$dataList[$k]['name'])
					$dataList[$k]['name'] = $deal_info['name'];
				}				
				
				$totalBalance0 = $GLOBALS['db']->getOne("select sum(balance_price) from ".DB_PREFIX."deal_coupon where ".$condition." and is_balance = 0");
				$totalBalance1 = $GLOBALS['db']->getOne("select sum(balance_price) from ".DB_PREFIX."deal_coupon where ".$condition." and is_balance = 1");
				$totalBalance2 = $GLOBALS['db']->getOne("select sum(balance_price) from ".DB_PREFIX."deal_coupon where ".$condition." and is_balance = 2");
				
				
				$GLOBALS['tmpl']->assign("totalBalance0",$totalBalance0+$totalBalance1);
				$GLOBALS['tmpl']->assign("totalBalance1",$totalBalance1);
				$GLOBALS['tmpl']->assign("totalBalance2",$totalBalance2);
				
				$GLOBALS['tmpl']->assign ( 'dataList', $dataList );
				$page = new Page($dataTotal,app_conf("PAGE_SIZE"));   //初始化分页对象 		
				$p  =  $page->show();
				$GLOBALS['tmpl']->assign('pages',$p);
				//团购券结算
				
				$html = $GLOBALS['tmpl']->fetch("biz/biz_balance_coupon.html");
				$GLOBALS['tmpl']->assign("html",$html);
				
			}
			else
			{
				if($is_balance==2)
				{
					$sort = " order by balance_time desc ";
				}	
				else
				{
					$sort = " order by id desc ";
				}	
				$condition = " deal_id = ".$deal_info['id']." ";
				$dataList = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where ".$condition." and is_balance = ".$is_balance.$sort."  limit ".$limit);
				$dataTotal = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where ".$condition." and is_balance = ".$is_balance);
					
				
				$totalBalance0 = $GLOBALS['db']->getOne("select sum(balance_total_price) from ".DB_PREFIX."deal_order_item where ".$condition." and is_balance = 0");
				$totalBalance1 = $GLOBALS['db']->getOne("select sum(balance_total_price) from ".DB_PREFIX."deal_order_item where ".$condition." and is_balance = 1");
				$totalBalance2 = $GLOBALS['db']->getOne("select sum(balance_total_price) from ".DB_PREFIX."deal_order_item where ".$condition." and is_balance = 2");

				$GLOBALS['tmpl']->assign("totalBalance0",$totalBalance0+$totalBalance1);
				$GLOBALS['tmpl']->assign("totalBalance1",$totalBalance1);
				$GLOBALS['tmpl']->assign("totalBalance2",$totalBalance2);
				
				$GLOBALS['tmpl']->assign ( 'dataList', $dataList );
				$page = new Page($dataTotal,app_conf("PAGE_SIZE"));   //初始化分页对象 		
				$p  =  $page->show();
				$GLOBALS['tmpl']->assign('pages',$p);

				
				$html = $GLOBALS['tmpl']->fetch("biz/biz_balance_order.html");
				$GLOBALS['tmpl']->assign("html",$html);
				
				
			}
		}
		
		//=============
		
		$GLOBALS['tmpl']->display("biz/biz_balance.html");
	}
	
	
	
}
?>