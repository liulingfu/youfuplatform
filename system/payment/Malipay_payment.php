<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'支付宝手机支付',
	'alipay_partner'	=>	'合作者身份ID',
	'alipay_account'	=>	'支付宝帐号',
	'alipay_rsa_public'	=>	'支付宝公钥',
);
$config = array(
	'alipay_partner'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //合作者身份ID
	'alipay_account'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //支付宝帐号: 
	//支付宝公钥
	'alipay_rsa_public'	=>	array(
		'INPUT_TYPE'	=>	'0'
	)
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Malipay';

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

// 支付宝手机支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Malipay_payment implements payment {

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
		//$data_return_url = get_domain().APP_ROOT.'/../payment.php?act=return&class_name=Malipay';
		$notify_url = get_domain().APP_ROOT.'/../shop.php?ctl=payment&act=response&class_name=Malipay';
		//$data_return_url = "http://tuan.7dit.com/payment.php?act=return&class_name=Malipay";
		//$data_return_url = "http://tuan.7dit.com";
		$pay = array();
		$pay['subject'] = $subject;
		$pay['body'] = $title_name;
		$pay['total_fee'] = $money;
		$pay['total_fee_format'] = format_price($money);
		$pay['out_trade_no'] = $payment_notice['notice_sn'];
		$pay['notify_url'] = $notify_url;
		
		$pay['partner'] = $payment_info['config']['alipay_partner'];//合作商户ID
		$pay['seller'] = $payment_info['config']['alipay_account'];//账户ID
				
		$pay['rsa_alipay_public'] = $payment_info['config']['alipay_rsa_public'];//支付宝(RSA)公钥
		

		$pay['pay_code'] = 'malipay';//,支付宝;mtenpay,财付通;mcod,货到付款
				
		$order_spec = '';
		$order_spec .= 'partner="'.$pay['partner'].'"';//合作商户ID
		$order_spec .= '&seller="'.$pay['seller'].'"';//账户ID
		$order_spec .= '&out_trade_no="'.$pay['out_trade_no'].'"';
		$order_spec .= '&subject="'.$pay['subject'].'"';
		$order_spec .= '&body="'.$pay['body'].'"';
		$order_spec .= '&total_fee="'.$pay['total_fee'].'"';
		$order_spec .= '&notify_url="'.$pay['notify_url'].'"';
		
		
		$pay['order_spec'] = $order_spec;
		$sign = $this->sign($order_spec);
		$pay['sign'] = urlencode($sign);
		$pay['sign_type'] = 'RSA';
		/*
		$pubkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC6IQ/HH06GbTIhKNN/YSQXxllnP7cNERMuN16GgZDfuf9NrY/Bw2ZINkq1RMNlbP66Vu5y0gwYPC/7PbO5l6pbnl3N4rw5VY3U6rtIC0f8ADDLrIZwShYUitaFq+Ao7rhk/GbpfSD7vgnugQz74fVewi17S3Apujq4U4LAxFmVowIDAQAB';
		$pubkey = $this->getPublicKeyFromX509($pubkey);
		
		$res = openssl_pkey_get_public($pubkey);		
		$sign = base64_decode($sign);
		$verify = openssl_verify($order_spec, $sign, $res);
		if ($verify == 1)
		{
			$pay['openssl_verify'] = 'ok';
		}else{
			$pay['openssl_verify'] = 'error';
		}		
		*/
//			
//		print_r($payment_info['config']);
//		print_r($pay);exit;
		return $pay;
	}
	
	public function response($request)
	{	
		//echo APP_ROOT_PATH."/alipaylog/ealipay_".date("Y-m-d H:i:s").".txt";exit;
		//@file_put_contents(APP_ROOT_PATH."/alipaylog/ealipay_".date("Y-m-dHis").".txt",$_SERVER["REQUEST_URI"].print_r($_REQUEST,true));
		/**
		 * 4.1     服务器通知服务 

		通知参数：notify_data,sign 
		
		签名原始字符串： 
		notify_data=<notify> 
		    <trade_status>TRADE_FINISHED</trade_status> 
		    <total_fee>25.00</total_fee> 
		    <subject>product24</subject> 
		    <out_trade_no>500000020113134</out_trade_no> 
		    <notify_reg_time>2010-09-20 15:26:51.000</notify_reg_time> 
		    <trade_no>2010092000164773</trade_no> 
		</notify> 
		
		签名结果： 
		sign=590e7b2b1faf573847008d0234992066 
		
		TRADE_FINISHED 表示交易成功； 
		WAIT_BUYER_PAY 等待买家付款。 

		 */
		$sign = $request['sign'];
		$notify_data = $request['notify_data'];
		
		
		$config_str = $sign.";notify_data=".$notify_data;
		
		//@file_put_contents(APP_ROOT_PATH."/alipaylog/ealipay_".date("Y-m-d H:i:s").".txt",$config_str);
//		
//		print_r($request)."<br /><br />";
//		echo $request['notify_data']."<br /><br />";
//		echo $notify_data."<br /><br />";
		$para_data = @XML_unserialize($notify_data);

		//@file_put_contents(APP_ROOT_PATH."/alipaylog/ealipay2_".date("Y-m-dHis").".txt",print_r($para_data,true));
		
		$payment_notice_sn = $para_data['notify']['out_trade_no'];
        
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Malipay'");  
    	$payment['config'] = unserialize($payment['config']);
    			
    	$pubkey = $payment['config']['alipay_rsa_public'];
    					
		//$pubkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCRm32ueC6j8MiUCUSrHZpSICJmE3nSt3puyh8Yc1MlHlkNy3WSESTtbbihkhvwQnpHkkBdZtcQLkP3ZcXFOaSgPHcLRLRbICtrrpB7AsAfeRV83LGY1mKwqixNzZUGIZl4ZkHrS3x2GiNCwf10es2CeAtkldlO6NE2MGOKRgv3wQIDAQAB';
		$pubkey = $this->getPublicKeyFromX509($pubkey);
		
		$res = openssl_pkey_get_public($pubkey);
		
		$sign = base64_decode($sign);
		$verify = openssl_verify("notify_data=".$notify_data, $sign, $res);
		if ($verify == 1)
		{
			$trade_status = $para_data['notify']['trade_status'];			
	    	$money = $para_data['notify']['total_fee'];
			$outer_notice_sn = $para_data['notify']['trade_no'];
			
		
			if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED' || $trade_status == 'WAIT_SELLER_SEND_GOODS'){
			   $payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
			   //file_put_contents(APP_ROOT_PATH."/alipaylog/payment_notice_sn_3.txt",$payment_notice_sn);
			   
			   $order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			   require_once APP_ROOT_PATH."system/libs/cart.php";
			   $rs = payment_paid($payment_notice['id']);					
			   if ($rs)
			   {
			   		//file_put_contents(APP_ROOT_PATH."/alipaylog/1.txt","");
				   	$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$outer_notice_sn."' where id = ".$payment_notice['id']);				
					order_paid($payment_notice['order_id']);	
			   	  echo "success";
			   }else{
			   		//file_put_contents(APP_ROOT_PATH."/alipaylog/2.txt","");
			   	  echo "success";
			   }
			}else{
				//file_put_contents(APP_ROOT_PATH."/alipaylog/3.txt","");
			   echo "fail";
			} 			
		}
		else
		{
			//file_put_contents(APP_ROOT_PATH."/alipaylog/4.txt","");
		    echo "fail";
		}		
		exit; 				
	}
	
	function getPublicKeyFromX509($certificate)  
	{  
	    $publicKeyString = "-----BEGIN PUBLIC KEY-----\n" .  
	          wordwrap($certificate, 64, "\n", true) .  
	          "\n-----END PUBLIC KEY-----";     
	    return $publicKeyString;  
	}	
	
/**RSA签名
 * $data待签名数据
 * 签名用商户私钥，必须是没有经过pkcs8转换的私钥
 * 最后的签名，需要用base64编码
 * return Sign签名
 */
function sign($data) {
    //读取私钥文件
    $priKey = file_get_contents(APP_ROOT_PATH.'/mapi/key/rsa_private_key.pem');
	//print_r($priKey); exit;
    //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
    $res = openssl_get_privatekey($priKey);

    //调用openssl内置签名方法，生成签名$sign
    openssl_sign($data, $sign, $res);

    //释放资源
    openssl_free_key($res);
    
    //base64编码
    $sign = base64_encode($sign);
    return $sign;
}	
		//响应通知
	function notify($request)
	{}
	
	//获取接口的显示
	function get_display_code()
	{}
	
}



//=======================
###################################################################################
#
# XML Library, by Keith Devens, version 1.2b
# http://keithdevens.com/software/phpxml
#
# This code is Open Source, released under terms similar to the Artistic License.
# Read the license at http://keithdevens.com/software/license
#
###################################################################################

###################################################################################
# XML_unserialize: takes raw XML as a parameter (a string)
# and returns an equivalent PHP data structure
###################################################################################
function XML_unserialize($xml){
	$xml_parser = new XML();
	$data = $xml_parser->parse($xml);
	$xml_parser->destruct();
	return $data;
}
###################################################################################
# XML_serialize: serializes any PHP data structure into XML
# Takes one parameter: the data to serialize. Must be an array.
###################################################################################
function & XML_serialize($data, $level = 0, $prior_key = NULL){
	if($level == 0){ ob_start(); echo '<?xml version="1.0" ?>',"\n"; }
	while(list($key, $value) = each($data))
		if(!strpos($key, ' attr')) #if it's not an attribute
			#we don't treat attributes by themselves, so for an empty element
			# that has attributes you still need to set the element to NULL

			if(is_array($value) and array_key_exists(0, $value)){
				XML_serialize($value, $level, $key);
			}else{
				$tag = $prior_key ? $prior_key : $key;
				echo str_repeat("\t", $level),'<',$tag;
				if(array_key_exists("$key attr", $data)){ #if there's an attribute for this element
					while(list($attr_name, $attr_value) = each($data["$key attr"]))
						echo ' ',$attr_name,'="',htmlspecialchars($attr_value),'"';
					reset($data["$key attr"]);
				}

				if(is_null($value)) echo " />\n";
				elseif(!is_array($value)) echo '>',htmlspecialchars($value),"</$tag>\n";
				else echo ">\n",XML_serialize($value, $level+1),str_repeat("\t", $level),"</$tag>\n";
			}
	reset($data);
	if($level == 0){ $str = &ob_get_contents(); ob_end_clean(); return $str; }
}
###################################################################################
# XML class: utility class to be used with PHP's XML handling functions
###################################################################################
class XML{
	var $parser;   #a reference to the XML parser
	var $document; #the entire XML structure built up so far
	var $parent;   #a pointer to the current parent - the parent will be an array
	var $stack;    #a stack of the most recent parent at each nesting level
	var $last_opened_tag; #keeps track of the last tag opened.

	function XML(){
 		$this->parser = &xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}
	function destruct(){ xml_parser_free($this->parser); }
	function & parse($data){
		$this->document = array();
		$this->stack    = array();
		$this->parent   = &$this->document;
		return xml_parse($this->parser, $data, true) ? $this->document : NULL;
	}
	function open($parser, $tag, $attributes){
		$this->data = ''; #stores temporary cdata
		$this->last_opened_tag = $tag;
		if(is_array($this->parent) and array_key_exists($tag,$this->parent)){ #if you've seen this tag before
			if(is_array($this->parent[$tag]) and array_key_exists(0,$this->parent[$tag])){ #if the keys are numeric
				#this is the third or later instance of $tag we've come across
				$key = count_numeric_items($this->parent[$tag]);
			}else{
				#this is the second instance of $tag that we've seen. shift around
				if(array_key_exists("$tag attr",$this->parent)){
					$arr = array('0 attr'=>&$this->parent["$tag attr"], &$this->parent[$tag]);
					unset($this->parent["$tag attr"]);
				}else{
					$arr = array(&$this->parent[$tag]);
				}
				$this->parent[$tag] = &$arr;
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		}else{
			$key = $tag;
		}
		if($attributes) $this->parent["$key attr"] = $attributes;
		$this->parent  = &$this->parent[$key];
		$this->stack[] = &$this->parent;
	}
	function data(&$parser, $data){
		if($this->last_opened_tag != NULL) #you don't need to store whitespace in between tags
			$this->data .= $data;
	}
	function close(&$parser, $tag){
		if($this->last_opened_tag == $tag){
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) $this->parent = &$this->stack[count($this->stack)-1];
	}
}
function count_numeric_items(&$array){
	return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}
?>