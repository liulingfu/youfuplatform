<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'盛支付',
	'sdo_account'	=>	'商户编号',
	'sdo_key'	=>	'商户密钥',
	
	'sdopay_gateway'	=>	'支持的银行',
	'sdopay_gateway_SDO1'	=>	'盛付通',
	'sdopay_gateway_SDTBNK'	=>	'测试银行',
	'sdopay_gateway_ICBC'	=>	'工商银行',
	'sdopay_gateway_CCB'	=>	'建设银行',
	'sdopay_gateway_ABC'	=>	'农业银行',
	'sdopay_gateway_CMB'	=>	'招商银行',
	'sdopay_gateway_COMM'	=>	'交通银行',
	'sdopay_gateway_CMBC'	=>	'民生银行',
	'sdopay_gateway_CIB'	=>	'兴业银行',
	'sdopay_gateway_HCCB'	=>	'杭州银行',
	'sdopay_gateway_CEB'	=>	'光大银行',
	'sdopay_gateway_CITIC'	=>	'中信银行',
	'sdopay_gateway_GZCB'	=>	'广州银行',
	'sdopay_gateway_HXB'	=>	'华夏银行',
	'sdopay_gateway_HKBEA'	=>	'东亚银行',
	'sdopay_gateway_BOC'	=>	'中国银行',
	'sdopay_gateway_WZCB'	=>	'温州银行',
	'sdopay_gateway_BCCB'	=>	'北京银行',
	'sdopay_gateway_SXJS'	=>	'晋商银行',
	'sdopay_gateway_NBCB'	=>	'宁波银行',
	'sdopay_gateway_SZPAB'	=>	'平安银行',
	'sdopay_gateway_BOS'	=>	'上海银行',
	'sdopay_gateway_NJCB'	=>	'南京银行',
	'sdopay_gateway_SPDB'	=>	'浦东发展银行',
	'sdopay_gateway_GNXS'	=>	'广州市农村信用合作社',
	'sdopay_gateway_GDB'	=>	'广东发展银行',
	'sdopay_gateway_SHRCB'	=>	'上海市农村商业银行',
	'sdopay_gateway_CBHB'	=>	'渤海银行',
	'sdopay_gateway_HKBCHINA'	=>	'汉口银行',
	'sdopay_gateway_ZHNX'	=>	'珠海市农村信用合作联社',
	'sdopay_gateway_SDE'	=>	'顺德农信社',
	'sdopay_gateway_YDXH'	=>	'尧都信用合作联社',
	'sdopay_gateway_CZCB'	=>	'浙江稠州商业银行',
	'sdopay_gateway_BJRCB'	=>	'北京农商行',
	'sdopay_gateway_PSBC'	=>	'中国邮政储蓄银行',
	'sdopay_gateway_SDB'	=>	'深圳发展银行',
	
);
$config = array(
	'sdo_account'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户编号
	'sdo_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //商户密钥 
	
	//'sdo_bankcode'=>'SDTBNK',  //银行编号
	'sdopay_gateway'	=>	array(
		'INPUT_TYPE'	=>	'3',
		'VALUES'	=>	array(
			'SDO1',    //盛付通
			'SDTBNK',    //测试银行
			'ICBC',    //工商银行
			'CCB',    //建设银行
			'ABC',    //农业银行
			'CMB',    //招商银行
			'COMM',    //交通银行
			'CMBC',    //民生银行
			'CIB',    //兴业银行
			'HCCB',    //杭州银行
			'CEB',    //光大银行
			'CITIC',    //中信银行
			'GZCB',    //广州银行
			'HXB',    //华夏银行
			'HKBEA',    //东亚银行
			'BOC',    //中国银行
			'WZCB',    //温州银行
			'BCCB',    //北京银行
			'SXJS',    //晋商银行
			'NBCB',    //宁波银行
			'SZPAB',    //平安银行
			'BOS',    //上海银行
			'NJCB',    //南京银行
			'SPDB',    //浦东发展银行
			'GNXS',    //广州市农村信用合作社
			'GDB',    //广东发展银行
			'SHRCB',    //上海市农村商业银行
			'CBHB',    //渤海银行
			'HKBCHINA',    //汉口银行
			'ZHNX',    //珠海市农村信用合作联社
			'SDE',    //顺德农信社
			'YDXH',    //尧都信用合作联社
			'CZCB',    //浙江稠州商业银行
			'BJRCB',    //北京农商行
			'PSBC',    //中国邮政储蓄银行
			'SDB',    //深圳发展银行
		)
	)	
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Sdo';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    
    return $module;
}


//http://pre.biz.sfubao.com/MerLogin.aspx 测试商户号:827438 测试登陆密码:38953532

require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Sdo_payment implements payment{
	private  $payment_lang = array(
		'GO_TO_PAY'	=>	'前往盛支付支付',
		'sdopay_gateway'	=>	'支持的银行',
		'sdopay_gateway_SDO1'	=>	'盛付通',
		'sdopay_gateway_SDTBNK'	=>	'测试银行',
		'sdopay_gateway_ICBC'	=>	'工商银行',
		'sdopay_gateway_CCB'	=>	'建设银行',
		'sdopay_gateway_ABC'	=>	'农业银行',
		'sdopay_gateway_CMB'	=>	'招商银行',
		'sdopay_gateway_COMM'	=>	'交通银行',
		'sdopay_gateway_CMBC'	=>	'民生银行',
		'sdopay_gateway_CIB'	=>	'兴业银行',
		'sdopay_gateway_HCCB'	=>	'杭州银行',
		'sdopay_gateway_CEB'	=>	'光大银行',
		'sdopay_gateway_CITIC'	=>	'中信银行',
		'sdopay_gateway_GZCB'	=>	'广州银行',
		'sdopay_gateway_HXB'	=>	'华夏银行',
		'sdopay_gateway_HKBEA'	=>	'东亚银行',
		'sdopay_gateway_BOC'	=>	'中国银行',
		'sdopay_gateway_WZCB'	=>	'温州银行',
		'sdopay_gateway_BCCB'	=>	'北京银行',
		'sdopay_gateway_SXJS'	=>	'晋商银行',
		'sdopay_gateway_NBCB'	=>	'宁波银行',
		'sdopay_gateway_SZPAB'	=>	'平安银行',
		'sdopay_gateway_BOS'	=>	'上海银行',
		'sdopay_gateway_NJCB'	=>	'南京银行',
		'sdopay_gateway_SPDB'	=>	'浦东发展银行',
		'sdopay_gateway_GNXS'	=>	'广州市农村信用合作社',
		'sdopay_gateway_GDB'	=>	'广东发展银行',
		'sdopay_gateway_SHRCB'	=>	'上海市农村商业银行',
		'sdopay_gateway_CBHB'	=>	'渤海银行',
		'sdopay_gateway_HKBCHINA'	=>	'汉口银行',
		'sdopay_gateway_ZHNX'	=>	'珠海市农村信用合作联社',
		'sdopay_gateway_SDE'	=>	'顺德农信社',
		'sdopay_gateway_YDXH'	=>	'尧都信用合作联社',
		'sdopay_gateway_CZCB'	=>	'浙江稠州商业银行',
		'sdopay_gateway_BJRCB'	=>	'北京农商行',
		'sdopay_gateway_PSBC'	=>	'中国邮政储蓄银行',
		'sdopay_gateway_SDB'	=>	'深圳发展银行',
	);
	private $config = array(
		'sdo_paychannel'=>'04',  //支付通道
		'sdo_defaultchannel'=>'04',  //默认支付通道
	);
	
	private $bank_types = array(
			'SDO1',//盛付通
			'SDTBNK',    //测试银行
			'ICBC',    //工商银行
			'CCB',    //建设银行
			'ABC',    //农业银行
			'CMB',    //招商银行
			'COMM',    //交通银行
			'CMBC',    //民生银行
			'CIB',    //兴业银行
			'HCCB',    //杭州银行
			'CEB',    //光大银行
			'CITIC',    //中信银行
			'GZCB',    //广州银行
			'HXB',    //华夏银行
			'HKBEA',    //东亚银行
			'BOC',    //中国银行
			'WZCB',    //温州银行
			'BCCB',    //北京银行
			'SXJS',    //晋商银行
			'NBCB',    //宁波银行
			'SZPAB',    //平安银行
			'BOS',    //上海银行
			'NJCB',    //南京银行
			'SPDB',    //浦东发展银行
			'GNXS',    //广州市农村信用合作社
			'GDB',    //广东发展银行
			'SHRCB',    //上海市农村商业银行
			'CBHB',    //渤海银行
			'HKBCHINA',    //汉口银行
			'ZHNX',    //珠海市农村信用合作联社
			'SDE',    //顺德农信社
			'YDXH',    //尧都信用合作联社
			'CZCB',    //浙江稠州商业银行
			'BJRCB',    //北京农商行
			'PSBC',    //中国邮政储蓄银行
			'SDB',    //深圳发展银行
	);	
	
		
	public function get_name($bank_id)
	{
		return $this->payment_lang['sdopay_gateway_'.$bank_id];
	}
	
	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		
		/* 银行类型 */
        $bank_id = $GLOBALS['db']->getOne("select bank_id from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		
		$payChannel = $this->config['sdo_paychannel'];
		$defaultChannel = $this->config['sdo_defaultchannel'];
		
		if ($bank_id=='0' || trim($bank_id) == 'SDO1' || trim($bank_id) == 'SDO'){
			$bank_id = '';
		}		
				
		if (empty($bank_id)){
			$paymentGateWayURL = 'http://mas.sdo.com/web-acquire-channel/cashier30.htm';
		}else{
			$paymentGateWayURL = 'http://netpay.sdo.com/paygate/ibankpay.aspx';	
		}

		
		$_orderNo = $payment_notice_id;
		$_amount = $money ;
		$_merchantNo = $payment_info['config']['sdo_account'];
		$_merchantUserId = "";
		$_orderTime = to_date($payment_notice['create_time'],'YmdHis');//date('YmjHis');
		$_productNo = '';
		$_productDesc = '';//$data_sn_1;
		$_remark1 = "";
		$_remark2 = "";
		$_bankCode = $bank_id;//$payment_info['config']['sdo_bankcode'];
		$_productURL = "";
		
		$Version = '3.0';
		
		$postBackURL = get_domain().APP_ROOT.'/shop.php?ctl=payment&act=response&class_name=Sdo';//付款完成后的跳转页面
		$notifyURL = get_domain().APP_ROOT.'/shop.php?ctl=payment&act=notify&class_name=Sdo';//通知发货页面
		$backURL = '';
		$currencyType = "RMB";
		$notifyUrlType='http'; //发货通知方式  http,https,tcp等,默认是http（如果不填写也为http）
		$signType = 2; //MD5

		
        $signString = $Version .$_amount .$_orderNo .$_merchantNo
					        .$_merchantUserId .$payChannel
					        .$postBackURL .$notifyURL
					        .$backURL .$_orderTime.$currencyType 
					        .$notifyUrlType .$signType
					        .$_productNo .$_productDesc .$_remark1 .$_remark2 .$_bankCode
					        .$defaultChannel.$_productURL;

        
		$_mac = md5($signString.$payment_info['config']['sdo_key']);
		
        $code  = '<form style="text-align:center;" method=post action="'.$paymentGateWayURL.'" target="_blank">';
        $code .= "<input type=HIDDEN name='Amount' value='".$_amount."'>";
        $code .= "<input type=HIDDEN name='MerchantUserId' value='".$_merchantUserId."'>";
        $code .= "<input type=HIDDEN name='OrderNo' value='".$_orderNo."'>";
        $code .= "<input type=HIDDEN name='OrderTime'  value='".$_orderTime."'>";
        $code .= "<input type=HIDDEN name='ProductNo'  value='".$_productNo."'>";
        $code .= "<input type=HIDDEN name='ProductDesc' value='".$_productDesc."'>";
        $code .= "<input type=HIDDEN name='Remark1' value='".$_remark1."'>";
        $code .= "<input type=HIDDEN name='Remark2' value='".$_remark2."'>";
        $code .= "<input type=HIDDEN name='ProductURL' value='".$_productURL."'>";
        
        $code .= "<input type=HIDDEN name='BankCode' value='".$_bankCode."'>";
        
        $code .= "<input type=HIDDEN name='Version' value='".$Version."'>";
        $code .= "<input type=HIDDEN name='MerchantNo' value='".$_merchantNo."'>";
        $code .= "<input type=HIDDEN name='PayChannel' value='".$payChannel."'>";
        $code .= "<input type=HIDDEN name='PostBackURL' value='".$postBackURL."'>";
        $code .= "<input type=HIDDEN name='NotifyURL' value='".$notifyURL."'>";
        $code .= "<input type=HIDDEN name='BackURL' value='".$backURL."'>";
        $code .= "<input type=HIDDEN name='CurrencyType' value='".$currencyType."'>";
        $code .= "<input type=HIDDEN name='NotifyURLType' value='".$notifyUrlType."'>";
        $code .= "<input type=HIDDEN name='SignType' value='".$signType."'>";
        $code .= "<input type=HIDDEN name='DefaultChannel' value='".$defaultChannel."'>";
        $code .= "<input type=HIDDEN name='MAC' value='".$_mac."'>";
		if(!empty($payment_info['logo']))
			$code .= "<input type='image' src='".APP_ROOT.$payment_info['logo']."' style='border:solid 1px #ccc;'><div class='blank'></div>";
			
        $code .= "<input type='submit' class='paybutton' value=".sprintf($this->payment_lang['GO_TO_PAY'],$this->get_name($bank_id))."支付></form>";
        $code .= "</form>";
        $code.="<br /><span class='red'>".$GLOBALS['lang']['PAY_TOTAL_PRICE'].":".format_price($_amount)."</span>";
        return $code; 
	}
	
	public function response($request)
	{	
        
		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		
		
		
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Sdo'");  
    	$payment['config'] = unserialize($payment['config']);
    	
		//获取参数
		$_amount = $request["Amount"];//订单金额
		$_payAmount = $request["PayAmount"];//实际支付金额
		$_orderNo = $request["OrderNo"];//商户订单号
		$_serialNo = $request["serialno"];//支付序列号
		$_status = $request["Status"];//支付状态 "01"表示成功
		$_merchantNo = $request["MerchantNo"];//商户号
		$_payChannel = $request["PayChannel"];//实际支付渠道
		$_discount = $request["Discount"];//实际折扣率
		$_signType = $request["SignType"];//签名方式。1-RSA 2-Md5
		$_payTime = $request["PayTime"];//支付时间
		$_currencyType = $request["CurrencyType"];//货币类型
		$_productNo = $request["ProductNo"];//产品编号
		$_productDesc = $request["ProductDesc"];//产品描述
		$_remark1 = $request["Remark1"];//产品备注1
		$_remark2 = $request["Remark2"];//产品备注2
		$_exInfo = $request["ExInfo"];//额外的返回信息
		$_mac = $request["MAC"];//签名字符串
		
		$verifyResult= $this->verifySign($_amount,$_payAmount,$_orderNo,$_serialNo,$_status
				,$_merchantNo,$_payChannel,$_discount,$_signType,$_payTime,$_currencyType
				,$_productNo,$_productDesc,$_remark1,$_remark2,$_exInfo,$payment['config']['sdo_key']);
       
		if (strtoupper($verifyResult) == strtoupper($_mac))
        {
        	$payment_log_id = intval($_orderNo);
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = '".$payment_log_id."'");
			
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$_serialNo."' where id = ".$payment_notice['id']);
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);
				if($rs)
				{
					if($order_info['type']==0)
					app_redirect(url("shop","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
					else
					app_redirect(url("shop","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
				}
				else 
				{
					if($order_info['pay_status'] == 2)
					{
						if($order_info['type']==0)
						app_redirect(url("shop","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
						else
						app_redirect(url("shop","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
						
					}
					else
					app_redirect(url("shop","payment#pay",array("id"=>$payment_notice['id']))); 
				}
				
			}
			else
			{
				app_redirect(url("shop","payment#pay",array("id"=>$payment_notice['id']))); 
			}
        }
        else{
		    showErr($GLOBALS['payment_lang']["PAY_FAILED"]);
        }  	
	}
	
	public function notify($request)
	{
		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		
		
		
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Sdo'");  
    	$payment['config'] = unserialize($payment['config']);
    	
		//获取参数
		$_amount = $request["Amount"];//订单金额
		$_payAmount = $request["PayAmount"];//实际支付金额
		$_orderNo = $request["OrderNo"];//商户订单号
		$_serialNo = $request["serialno"];//支付序列号
		$_status = $request["Status"];//支付状态 "01"表示成功
		$_merchantNo = $request["MerchantNo"];//商户号
		$_payChannel = $request["PayChannel"];//实际支付渠道
		$_discount = $request["Discount"];//实际折扣率
		$_signType = $request["SignType"];//签名方式。1-RSA 2-Md5
		$_payTime = $request["PayTime"];//支付时间
		$_currencyType = $request["CurrencyType"];//货币类型
		$_productNo = $request["ProductNo"];//产品编号
		$_productDesc = $request["ProductDesc"];//产品描述
		$_remark1 = $request["Remark1"];//产品备注1
		$_remark2 = $request["Remark2"];//产品备注2
		$_exInfo = $request["ExInfo"];//额外的返回信息
		$_mac = $request["MAC"];//签名字符串
		
		$verifyResult= $this->verifySign($_amount,$_payAmount,$_orderNo,$_serialNo,$_status
				,$_merchantNo,$_payChannel,$_discount,$_signType,$_payTime,$_currencyType
				,$_productNo,$_productDesc,$_remark1,$_remark2,$_exInfo,$payment['config']['sdo_key']);
       
		if (strtoupper($verifyResult) == strtoupper($_mac))
        {
        	$payment_log_id = intval($_orderNo);
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = '".$payment_log_id."'");
			
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$_serialNo."' where id = ".$payment_notice['id']);
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);
				if($rs)
				{
					if($order_info['type']==0)
					app_redirect(url("shop","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
					else
					app_redirect(url("shop","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
				}
				else 
				{
					if($order_info['pay_status'] == 2)
					{
						if($order_info['type']==0)
						app_redirect(url("shop","payment#done",array("id"=>$payment_notice['order_id']))); //支付成功
						else
						app_redirect(url("shop","payment#incharge_done",array("id"=>$payment_notice['order_id']))); //支付成功
						
					}
					else
					app_redirect(url("shop","payment#pay",array("id"=>$payment_notice['id']))); 
				}
				
			}
			else
			{
				app_redirect(url("shop","payment#pay",array("id"=>$payment_notice['id']))); 
			}
        }
        else{
		    showErr($GLOBALS['payment_lang']["PAY_FAILED"]);
        }  	
	}
	
	
	public function get_display_code(){
		
		$payment_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name='Sdo'");
		
		$payment_cfg = unserialize($payment_item['config']);
 		if($payment_item)
		{
	        $def_url = "<style type='text/css'>.bank_sdo_types{float:left; display:block; background:url(".get_domain().APP_ROOT."/system/payment/SdoBank/banklogo.gif) no-repeat; font-size:0px; width:150px; height:10px; text-align:left; padding:15px 0px; }";
	        
	        $def_url .=".bk_typeSDO1{background-position:15px -1414px; }";    //盛付通
			$def_url .=".bk_typeSDTBNK{background-position:15px -5px; }";    //测试银行
			$def_url .=".bk_typeICBC{background-position:15px -44px; }";    //工商银行
			$def_url .=".bk_typeCCB{background-position:15px -84px; }";    //建设银行
			$def_url .=".bk_typeABC{background-position:15px -124px; }";    //农业银行
			$def_url .=".bk_typeCMB{background-position:15px -164px; }";    //招商银行
			$def_url .=".bk_typeCOMM{background-position:15px -204px; }";    //交通银行
			$def_url .=".bk_typeCMBC{background-position:15px -244px; }";    //民生银行
			$def_url .=".bk_typeCIB{background-position:15px -284px; }";    //兴业银行
			$def_url .=".bk_typeHCCB{background-position:15px -324px; }";    //杭州银行
			$def_url .=".bk_typeCEB{background-position:15px -364px; }";    //光大银行
			$def_url .=".bk_typeCITIC{background-position:15px -404px; }";    //中信银行
			$def_url .=".bk_typeGZCB{background-position:15px -444px; }";    //广州银行
			$def_url .=".bk_typeHXB{background-position:15px -484px; }";    //华夏银行
			$def_url .=".bk_typeHKBEA{background-position:15px -524px; }";    //东亚银行
			$def_url .=".bk_typeBOC{background-position:15px -568px; }";    //中国银行
			$def_url .=".bk_typeWZCB{background-position:15px -610px; }";    //温州银行
			$def_url .=".bk_typeBCCB{background-position:15px -655px; }";    //北京银行
			$def_url .=".bk_typeSXJS{background-position:15px -700px; }";    //晋商银行
			$def_url .=".bk_typeNBCB{background-position:15px -745px; }";    //宁波银行
			$def_url .=".bk_typeSZPAB{background-position:15px -785px; }";    //平安银行
			$def_url .=".bk_typeBOS{background-position:15px -825px; }";    //上海银行
			$def_url .=".bk_typeNJCB{background-position:15px -860px; }";    //南京银行
			$def_url .=".bk_typeSPDB{background-position:15px -900px; }";    //浦东发展银行
			$def_url .=".bk_typeGNXS{background-position:15px -935px; }";    //广州市农村信用合作社
			$def_url .=".bk_typeGDB{background-position:15px -975px; }";    //广东发展银行
			$def_url .=".bk_typeSHRCB{background-position:15px -1015px; }";    //上海市农村商业银行
			$def_url .=".bk_typeCBHB{background-position:15px -1045px; }";    //渤海银行
			$def_url .=".bk_typeHKBCHINA{background-position:15px -1095px; }";    //汉口银行
			$def_url .=".bk_typeZHNX{background-position:15px -1135px; }";    //珠海市农村信用合作联社
			$def_url .=".bk_typeSDE{background-position:15px -1175px; }";    //顺德农信社
			$def_url .=".bk_typeYDXH{background-position:15px -1215px; }";    //尧都信用合作联社
			$def_url .=".bk_typeCZCB{background-position:15px -1255px; }";    //浙江稠州商业银行
			$def_url .=".bk_typeBJRCB{background-position:15px -1295px; }";    //北京农商行
			$def_url .=".bk_typePSBC{background-position:15px -1335px; }";    //中国邮政储蓄银行
			$def_url .=".bk_typeSDB{background-position:15px -1375px; }";    //深圳发展银行        
	        $def_url .="</style>";
	        $def_url .="<script type='text/javascript'>function set_bank(bank_id)";
			$def_url .="{";
			$def_url .="$(\"input[name='bank_id']\").val(bank_id);";
			$def_url .="}</script>";
			foreach ($payment_cfg['sdopay_gateway'] AS $key=>$val)
	        {
	            $def_url  .= "<label class='bank_sdo_types bk_type".$key."'><input type='radio' name='payment' value='".$payment_item['id']."' rel='".$key."' onclick='set_bank(\"".$key."\")' /></label>";
	        }
	        $def_url .= "<input type='hidden' name='bank_id' />";
			return $def_url;
		}
		else
		{
			return '';
		}
	}
		
	public function verifySign($amount , $payAmount ,$orderNo
    		,$serialNo,$status,$merchantNo , $payChannel
    		,$discount,$signType ,$payTime,$currencyType
    		,$productNo,$productDesc,$remark1,$remark2,$exInfo,$md5key){
    			
			$toSignString = $amount."|".$payAmount."|".$orderNo."|".
										$serialNo."|".$status."|".$merchantNo."|".
										$payChannel."|".$discount."|".$signType."|".
										$payTime."|".$currencyType."|".$productNo."|".
										$productDesc."|".$remark1."|".$remark2."|".$exInfo;

			return  md5($toSignString. "|" . $md5key);			
		}	
		
}
?>