<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

//用于QXT上行验证
//短信格式
//验证格式 v-xxxx xxxx为序列号
//消费格式 u-xxxx-xxxx 前半段为序列号，后半段为密码
//api验证地址 http://xxx/cpapi/qxtapi.php?SrcMobile=13333333333&Content=xxxxxx&RecvTime=xxxxxxxxxxx

require 'cp_init.php';
$ip = get_client_ip();
$xml = file_get_contents('php://input');

if($ip!='221.179.180.156' || $xml=="")
{
	header("Content-Type:text/html; charset=utf-8");
	echo "非法访问";
	exit;
}

//更新时间 2013-7-23  REQUEST  改为    POST 过来的XML
$xml = str_replace(array("/r/n", "/r", "/n"), "", $xml);
$xml_arr = simplexml_load_string($xml);

$SrcMobile = $xml_arr->Body->Message->SrcMobile;
$Content = $xml_arr->Body->Message->Content;
$RecvTime = $xml_arr->Body->Message->RecvTime;

$arr = explode("-",$Content);
$prefix = $arr[0];

if($prefix!='u'&&$prefix!='v')
{
	if(log_coupon("","短信内容:".$Content,$RecvTime)&&$SrcMobile)
	{
	$msg_data['dest'] = $SrcMobile;
	$msg_data['send_type'] = 0;
	$msg_data['content'] = "短信格式错误";
	$msg_data['send_time'] = 0;
	$msg_data['is_send'] = 0;
	$msg_data['create_time'] = get_gmtime();
	$msg_data['user_id'] = 0;
	$msg_data['is_html'] = 0;
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
	echo "ok";
	exit;
	}

}
else
{
	if(strtolower($prefix)=='v')
	{
		//验证
		$sn = isset($arr[1])?$arr[1]:'';		
		$now = get_gmtime();
		$coupon_data = $GLOBALS['db']->getRow("select doi.name as name,c.sn as sn,doi.sub_name as sub_name,doi.unit_price as price from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.is_valid = 1 and c.is_delete = 0 and c.confirm_time = 0 and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")");
		
		if($coupon_data)
		{
			$msg = $GLOBALS['lang']['COUPON_VERIFY_LOG'].":(".$SrcMobile.")".sprintf($GLOBALS['lang']['COUPON_IS_VALID'],$coupon_data['sub_name'],$coupon_data['sn']);
			if(log_coupon($coupon_data['sn'],$msg,$RecvTime)&&$SrcMobile)
			{
			$msg_data['dest'] = $SrcMobile;
			$msg_data['send_type'] = 0;
			$msg_data['content'] = sprintf($GLOBALS['lang']['COUPON_IS_VALID'],$coupon_data['sub_name'],$coupon_data['sn']);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = 0;
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
			echo "ok";
			exit;
			}
		}
		else
		{
			$msg = $GLOBALS['lang']['COUPON_VERIFY_LOG'].":(".$SrcMobile.")".$GLOBALS['lang']['COUPON_INVALID'];
			if(log_coupon($sn,$msg,$RecvTime)&&$SrcMobile)
			{				
			$msg_data['dest'] = $SrcMobile;
			$msg_data['send_type'] = 0;
			$msg_data['content'] = $GLOBALS['lang']['COUPON_INVALID'];
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = 0;
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
			echo "ok";
			exit;
			}
		}	
	}//end 验证
	
	if(strtolower($prefix)=='u')
	{		
		
		$sn = isset($arr[1])?$arr[1]:'';	
		$pwd = isset($arr[2])?$arr[2]:"";
		$now = get_gmtime();
		
		
		$coupon_data = $GLOBALS['db']->getRow("select c.id as id,doi.name as name,doi.sub_name as sub_name,doi.unit_price as price,c.sn as sn,c.supplier_id as supplier_id,c.confirm_time as confirm_time from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.password = '".$pwd."' and c.is_valid = 1 and c.is_delete = 0  and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")"); 
			if($coupon_data)
			{
				if($coupon_data['confirm_time'] > 0)
				{
					$msg = $GLOBALS['lang']['COUPON_USE_LOG'].":(".$SrcMobile.")".sprintf($GLOBALS['lang']['COUPON_INVALID_USED'],to_date($coupon_data['confirm_time']));
					if(log_coupon($coupon_data['sn'],$msg,$RecvTime)&&$SrcMobile)
					{
					$msg_data['dest'] = $SrcMobile;
					$msg_data['send_type'] = 0;
					$msg_data['content'] = $coupon_data['sn'].sprintf($GLOBALS['lang']['COUPON_INVALID_USED'],to_date($coupon_data['confirm_time']));
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = 0;
					$msg_data['is_html'] = 0;
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
					echo "ok";
					exit;
					}
				}
				else
				{
					//开始确认
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set confirm_account = 999999,confirm_time=".$now." where id = ".intval($coupon_data['id']));
					$msg = $GLOBALS['lang']['COUPON_USE_LOG'].":(".$SrcMobile.")".sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
					if(log_coupon($coupon_data['sn'],$msg,$RecvTime)&&$SrcMobile)
					{
					send_use_coupon_sms(intval($coupon_data['id'])); //发送团购券确认消息
					send_use_coupon_mail(intval($coupon_data['id'])); //发送团购券确认消息
					$msg_data['dest'] = $SrcMobile;
					$msg_data['send_type'] = 0;
					$msg_data['content'] = $coupon_data['sub_name']."(".$coupon_data['sn'].")".sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = 0;
					$msg_data['is_html'] = 0;
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入	
					echo "ok";	
					exit;			
					}		
				}
			}
			else
			{				
				$msg = $GLOBALS['lang']['COUPON_USE_LOG'].":(".$SrcMobile.")".$GLOBALS['lang']['COUPON_INVALID'];
				if(log_coupon($sn,$msg,$RecvTime)&&$SrcMobile)
				{
				$msg_data['dest'] = $SrcMobile;
				$msg_data['send_type'] = 0;
				$msg_data['content'] = $GLOBALS['lang']['COUPON_INVALID'];
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = 0;
				$msg_data['is_html'] = 0;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
				echo "ok";
				exit;
				}
			}
	}//end 使用
}

?>