<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'货到付款手机端',
);
$config = array(
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Mcod';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '0';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = '';
    return $module;
}

// 手机货到付款支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Mcod_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
	
		$sql = "select name ".
						  "from ".DB_PREFIX."deal_order_item ".					
						  "where order_id =". intval($payment_notice['order_id']);
		$title_name = $GLOBALS['db']->getOne($sql);

		
		$subject = msubstr($title_name,0,40);
		$data_return_url = get_domain().APP_ROOT.'/../payment.php?act=return&class_name=Malipay';
		//$data_return_url = "http://tuan.7dit.com/payment.php?act=return&class_name=Malipay";
		
		$pay = array();
		$pay['subject'] = $subject;
		$pay['body'] = $title_name;
		$pay['total_fee'] = $money;
		$pay['total_fee_format'] = format_price($money);
		$pay['out_trade_no'] = $payment_notice['notice_sn'];
		
		
		$pay['pay_code'] = 'mcod';//,支付宝;mtenpay,财付通;mcod,货到付款

		return $pay;

	}
	
	public function response($request)
	{}
	
	//响应通知
	function notify($request)
	{}
	
	//获取接口的显示
	function get_display_code()
	{}
	
}
?>