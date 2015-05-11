<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'代金券支付',
	'ecvsn'	=>	'序列号',
	'ecvpassword'	=>	'密码',
	'verify'	=>	'验证',
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Voucher';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
     $module['reg_url'] = '';
    return $module;
}

// 余额支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Voucher_payment implements payment {
	public function get_payment_code($payment_notice_id)
	{
		return false;		
	}
	
	public function response($request)
	{
		return false;
	}
	
	public function notify($request)
	{
		return false;
	}
	
	public function get_display_code()
	{
		$html = "<span>代金券支付：序列号".
				"：<input type='text' value='' maxlength='20' name='ecvsn' class='f-input' />&nbsp;密码".				
				"：<input type='password' value='' maxlength='20' name='ecvpassword' class='f-input' />&nbsp;".
				"<input type='button' class='formbutton' value='验证' id='verify_ecv' ></span>";				
		return $html;
	}
	
	// 直接支付
	// 修正订单pay_amount,ecv_money,ecv_sn的值， 修改payment_notice要应的is_paid为1
	public function direct_pay($ecv_sn,$ecv_password,$payment_notice_id)
	{
		$rs = payment_paid($payment_notice_id);
		if($rs)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count + 1 where sn = '".$ecv_sn."' and password = '".$ecv_password."'");
		}	
	}
}
?>