<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_moneyModule extends ShopBaseModule
{
	private $creditsettings;
	private $allow_exchange = false;
	private  $credits_CFG = array(
		'1' => array('title'=>'经验', 'unit'=>'' ,'field'=>'point'),
		'2' => array('title'=>'积分', 'unit'=>'' ,'field'=>'score'),
		'3' => array('title'=>'资金', 'unit'=>'' ,'field'=>'money'),
	);
	public function __construct()
	{
		if(file_exists(APP_ROOT_PATH."public/uc_config.php"))
		{
			require_once APP_ROOT_PATH."public/uc_config.php";
		}
		if(app_conf("INTEGRATE_CODE")=='Ucenter'&&UC_CONNECT=='mysql')
		{
			if(file_exists(APP_ROOT_PATH."public/uc_data/creditsettings.php"))
			{
				require_once APP_ROOT_PATH."public/uc_data/creditsettings.php";
				$this->creditsettings = $_CACHE['creditsettings'];
				if(count($this->creditsettings)>0)
				{
					foreach($this->creditsettings as $k=>$v)
					{
						$this->creditsettings[$k]['srctitle'] = $this->credits_CFG[$v['creditsrc']]['title'];
					}
					$this->allow_exchange = true;
					$GLOBALS['tmpl']->assign("allow_exchange",$this->allow_exchange);
				}
			}
		}
		parent::__construct();
	}
	
	
	public function exchange()
	{		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_EXCHANGE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_exchange.html");
		
		$GLOBALS['tmpl']->assign("exchange_data",$this->creditsettings);
		$GLOBALS['tmpl']->assign("exchange_json_data",json_encode($this->creditsettings));
		
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	public function doexchange()
	{		
		if($this->allow_exchange)
		{
			$user_pwd = md5(addslashes(trim($_REQUEST['password'])));
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
			
			if($user_info['user_pwd']=="")
			{
				//判断是否为初次整合
				//载入会员整合
				$integrate_code = trim(app_conf("INTEGRATE_CODE"));
				if($integrate_code!='')
				{
					$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
					if(file_exists($integrate_file))
					{
						require_once $integrate_file;
						$integrate_class = $integrate_code."_integrate";
						$integrate_obj = new $integrate_class;
					}	
				}
				if($integrate_obj)
				{			
					$result = $integrate_obj->login($user_info['user_name'],$user_pwd);						
					if($result['status'])
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$user_pwd."' where id = ".$user_info['id']);
						$user_info['user_pwd'] = $user_pwd;
					}								
				}
			}
			if($user_info['user_pwd']==$user_pwd)
			{
				$cfg = $this->creditsettings[addslashes(trim($_REQUEST['key']))];
				if($cfg)
				{	
					$amount = floor($_REQUEST['amountdesc']);
					$use_amount = floor($amount*$cfg['ratio']); //消耗的本系统积分
				    $field = $this->credits_CFG[$cfg['creditsrc']]['field'];
					
				    if($user_info[$field]<$use_amount)
				    {
				    	$data = array("status"=>false,"message"=>$cfg['srctitle']."不足，不能兑换");
						ajax_return($data);
				    }				    
					
					include_once(APP_ROOT_PATH . 'uc_client/client.php');	
				    $res = call_user_func_array("uc_credit_exchange_request", array(
				    	$user_info['integrate_id'],  //uid(整合的UID)
				    	$cfg['creditsrc'],  //原积分ID
				    	$cfg['creditdesc'],  //目标积分ID
				    	$cfg['appiddesc'],  //toappid目标应用ID
				    	$amount,  //amount额度(计算过的目标应用的额度)
				    ));
				    if($res)
				    {
				    	//兑换成功
				    	$use_amount = 0 - $use_amount;				    	
				    	$credit_data = array($field=>$use_amount);
				    	require_once APP_ROOT_PATH."system/libs/user.php";
				    	modify_account($credit_data,$user_info['id'],"ucenter兑换支出");
				    	$data = array("status"=>true,"message"=>"兑换成功");
						ajax_return($data);
				    }
				    else
				    {
				    	$data = array("status"=>false,"message"=>"兑换失败");
						ajax_return($data);
				    }
				}
				else
				{
					$data = array("status"=>false,"message"=>"非法的兑换请求");
					ajax_return($data);
				}
			}
			else
			{
				$data = array("status"=>false,"message"=>"登录密码不正确");
				ajax_return($data);
			}
		}
		else
		{
			$data = array("status"=>false,"message"=>"未开启兑换功能");
			ajax_return($data);
		}
	}
	
	public function index()
	{
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
		$level_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_info['group_id']));
		$point_level = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_level where id = ".intval($user_info['level_id']));
		$user_info['user_level'] = $level_info['name'];
		$user_info['point_level'] = $point_level['name'];
		$user_info['discount'] = $level_info['discount']*10;		
		$GLOBALS['tmpl']->assign("user_data",$user_info);
		$t = addslashes(htmlspecialchars(trim($_REQUEST['t'])));
		$GLOBALS['tmpl']->assign("t",$t);
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_user_log($limit,$GLOBALS['user_info']['id'],$t);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_index.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	
	public function incharge()
	{
		 
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY_INCHARGE']);
	
		//输出支付方式
		$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name <> 'Account' and class_name <> 'Voucher' and class_name <> 'tenpayc2c' and online_pay = 1 order by sort desc");			
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=='Alipay')
			{
				$cfg = unserialize($v['config']);
				if($cfg['alipay_service']!=2)
				{
					unset($payment_list[$k]);
					continue;
				}
			}
			$directory = APP_ROOT_PATH."system/payment/";
			$file = $directory. '/' .$v['class_name']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = $v['class_name']."_payment";
				$payment_object = new $payment_class();
				$payment_list[$k]['display_code'] = $payment_object->get_display_code();						
			}
			else
			{
				unset($payment_list[$k]);
			}
		}
		$GLOBALS['tmpl']->assign("payment_list",$payment_list);
				
		//输出充值订单
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	
		$result = get_user_incharge($limit,$GLOBALS['user_info']['id']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	public function incharge_done()
	{
		$payment_id = intval($_REQUEST['payment']);
		$money = floatval($_REQUEST['money']);
		if($money<=0)
		{
			showErr($GLOBALS['lang']['PLEASE_INPUT_CORRECT_INCHARGE']);
		}
		
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_id);
		if(!$payment_info)
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
		}
		//开始生成订单
		$now = get_gmtime();
		$order['type'] = 1; //充值单
		$order['user_id'] = $GLOBALS['user_info']['id'];
		$order['create_time'] = $now;
		$order['total_price'] = $money + $payment_info['fee_amount'];
		$order['deal_total_price'] = $money;
		$order['pay_amount'] = 0;  
		$order['pay_status'] = 0;  
		$order['delivery_status'] = 5;  
		$order['order_status'] = 0; 
		$order['payment_id'] = $payment_id;
		$order['payment_fee'] = $payment_info['fee_amount'];
		$order['bank_id'] = addslashes(htmlspecialchars(trim($_REQUEST['bank_id'])));
		
	
		do
		{
			$order['order_sn'] = to_date(get_gmtime(),"Ymdhis").rand(100,999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);
		
		require_once APP_ROOT_PATH."system/libs/cart.php";
		$payment_notice_id = make_payment_notice($order['total_price'],$order_id,$payment_info['id']);
		//创建支付接口的付款单
	
		$rs = order_paid($order_id);  
		if($rs)
		{
			app_redirect(url("shop","payment#incharge_done",array("id"=>$order_id))); //充值支付成功
		}
		else
		{
			app_redirect(url("shop","payment#pay",array("id"=>$payment_notice_id))); 
		}
	}
	
	public function carry()
	{
		 
		require_once APP_ROOT_PATH.'app/Lib/message.php';
		//以下关于提现留言的输出
		$rel_table = 'tx';
		$id = 0;
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	
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
		//message_form 变量输出
		$GLOBALS['tmpl']->assign("post_title",$message_type['show_name']);
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',$rel_table);
	
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$message = get_message_list_shop($limit,$condition);
		
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		//end订单留言
		
		$GLOBALS['tmpl']->assign("page_title",$message_type['show_name']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_carry.html");
		$GLOBALS['tmpl']->display("uc.html");
	}
}
?>