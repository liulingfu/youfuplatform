<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class StatisticAction extends CommonAction{
	public function index()
	{
		$type = intval($_REQUEST['type']);
		$weektime = htmlspecialchars(trim($_REQUEST['weektime']));
		$yeartime = intval($_REQUEST['yeartime']);
		
		$now = get_gmtime();
		if($weektime=='')$weektime = to_date($now,"Y-m-d");
		if($yeartime==0)$yeartime = to_date($now,"Y");
		$thisyeartime =  to_date($now,"Y");
		
		
		$this->assign("weektime",$weektime);
		$this->assign("yeartime",$yeartime);
		$this->assign("thisyeartime",$thisyeartime);
		$this->assign("type",$type);
		
		$weektimespan = to_timespan($weektime, 'Y-m-d');
		$yeartimespan = to_timespan($yeartime, 'Y');
		
		if($type==0)
		{
			$current_week = to_date($weektimespan,"w");
			$statistic_data = array();
			$week_col = array();
			for($i=0;$i<7;$i++)
			{
				$week_col[$i] = array(
					"begin_time"=> $weektimespan+($i-$current_week)*24*3600,
					"end_time"=> $weektimespan+($i-$current_week)*24*3600+24*3600-1,
					"week"	=> l("WEEK_".$i),
					"datetime"	=>	to_date($weektimespan+($i-$current_week)*24*3600,"Y-m-d"),
				);
				if($i==$current_week)
				{
					$week_col[$i]['current'] = 1;
				}
				else
				{
					$week_col[$i]['current'] = 0;
				}
			}
			
			$statistic_col = array(
					'00_register',  //注册用户
					'01_onsale',  //上架商品
					'02_ordercount',  //下单总数
					'03_paidordercount',  //付款订单数
					'04_totalamount',   //总收金额
					'05_onlinepay',   //在线支付总金额
					'06_creditpay',    //余额支付总额
					'07_scorepay',		//积分消费总额，不含送的积分
					'08_onlineincharge',   //充值总额
			);
			
			foreach($statistic_col as $k=>$v)
			{
				$statistic_data[$k]['name'] = l($v);
				$statistic_data[$k]['data'] = array();
				$total_amount = 0;
				foreach($week_col as $kk=>$vv)
				{
					if($v=="00_register")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where create_time between ".$vv['begin_time']." and ".$vv['end_time']);
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="01_onsale")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and ((begin_time between ".$vv['begin_time']." and ".$vv['end_time'].") or begin_time = 0)");
						$statistic_data[$k]['data'][$kk] = intval($count);					
					}
					if($v=="02_ordercount")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where is_delete = 0 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="03_paidordercount")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);						
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="04_totalamount")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);											
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="05_onlinepay")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount-ecv_money-account_money) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="06_creditpay")
					{
						$count = $GLOBALS['db']->getOne("select sum(account_money) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="07_scorepay")
					{
						$count = $GLOBALS['db']->getOne("select sum(return_total_score) from ".DB_PREFIX."deal_order where is_delete = 0 and return_total_score < 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_score(intval(abs($count)));
						$total_amount+=intval(abs($count));
					}
					if($v=="08_onlineincharge")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and type = 1 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);											
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}					
				}
				
				//总额
				if($v=="01_onsale")
				{
					$total_amount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and ((begin_time between ".$week_col[0]['begin_time']." and ".$week_col[6]['end_time'].") or begin_time = 0)");					
					$total_amount = $total_amount;
				}
				if($v=="04_totalamount")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="05_onlinepay")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="06_creditpay")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="07_scorepay")
				{
					$total_amount = format_score($total_amount);
				}
				if($v=="08_onlineincharge")
				{
					$total_amount = format_price($total_amount);
				}				
				$statistic_data[$k]['data'][count($week_col)] = $total_amount;  //总额
			}
			
			$this->assign("week_col",$week_col);
			$this->assign("statistic_data",$statistic_data);
		}
		else
		{
			if($yeartime == to_date($now,"Y")) //今年
			$current_month = to_date($now,"n");
			else
			$current_month = 0;
			
			
			$statistic_data = array();
			$month_col = array();
			for($i=1;$i<=12;$i++)
			{
				$month_col[$i-1] = array(					
					"begin_time"=> to_timespan($yeartime."-".str_pad($i, 2, "0", STR_PAD_LEFT)."-01 00:00:00","Y-m-d H:i:s"),
					"end_time"=> to_timespan($yeartime."-".str_pad(($i+1), 2, "0", STR_PAD_LEFT)."-01 00:00:00","Y-m-d H:i:s")-1,
					"month"	=> l("MONTH_".$i),
				);
				if($i==$current_month)
				{
					$month_col[$i-1]['current'] = 1;
				}
				else
				{
					$month_col[$i-1]['current'] = 0;
				}
			}
			
		
			
			$statistic_col = array(
					'00_register',  //注册用户
					'01_onsale',  //上架商品
					'02_ordercount',  //下单总数
					'03_paidordercount',  //付款订单数
					'04_totalamount',   //总收金额
					'05_onlinepay',   //在线支付总金额
					'06_creditpay',    //余额支付总额
					'07_scorepay',		//积分消费总额，不含送的积分
					'08_onlineincharge',   //充值总额
			);
			
			
			foreach($statistic_col as $k=>$v)
			{
				$statistic_data[$k]['name'] = l($v);
				$statistic_data[$k]['data'] = array();
				$total_amount = 0;
				foreach($month_col as $kk=>$vv)
				{
					if($v=="00_register")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where create_time between ".$vv['begin_time']." and ".$vv['end_time']);					
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="01_onsale")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and ((begin_time between ".$vv['begin_time']." and ".$vv['end_time'].") or begin_time = 0)");
						$statistic_data[$k]['data'][$kk] = intval($count);					
					}
					if($v=="02_ordercount")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where is_delete = 0 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="03_paidordercount")
					{
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);						
						$statistic_data[$k]['data'][$kk] = intval($count);
						$total_amount+=intval($count);
					}
					if($v=="04_totalamount")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);											
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="05_onlinepay")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount-ecv_money-account_money) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="06_creditpay")
					{
						$count = $GLOBALS['db']->getOne("select sum(account_money) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}
					if($v=="07_scorepay")
					{
						$count = $GLOBALS['db']->getOne("select sum(return_total_score) from ".DB_PREFIX."deal_order where is_delete = 0 and return_total_score < 0 and pay_status = 2 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);																
						$statistic_data[$k]['data'][$kk] = format_score(intval(abs($count)));
						$total_amount+=intval(abs($count));
					}
					if($v=="08_onlineincharge")
					{
						$count = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."deal_order where is_delete = 0 and pay_status = 2 and type = 1 and create_time between ".$vv['begin_time']." and ".$vv['end_time']);											
						$statistic_data[$k]['data'][$kk] = format_price(doubleval($count));
						$total_amount+=doubleval($count);
					}					
				}
				
				//总额
				if($v=="01_onsale")
				{
					$total_amount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and ((begin_time between ".$month_col[0]['begin_time']." and ".$month_col[11]['end_time'].") or begin_time = 0)");					
				}
				if($v=="04_totalamount")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="05_onlinepay")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="06_creditpay")
				{
					$total_amount = format_price($total_amount);
				}
				if($v=="07_scorepay")
				{
					$total_amount = format_score($total_amount);
				}
				if($v=="08_onlineincharge")
				{
					$total_amount = format_price($total_amount);
				}				
				$statistic_data[$k]['data'][count($month_col)] = $total_amount;  //总额
			}
			
			$this->assign("month_col",$month_col);
			$this->assign("statistic_data",$statistic_data);
			
			
		}
		
		
		
		$this->display ();
	}
	
	
}
?>