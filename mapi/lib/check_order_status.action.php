<?php
class check_order_status{
	public function index()
	{
		$payment_notice_sn = strim($GLOBALS['request']['out_trade_no']);
	
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);	
		$root = array();
		$root['return'] = 1;
		$root['pay_status'] = 0;//0:订单未收款(全额);1:订单已经收款(全额)
		if($user_id>0)
		{
			$root['user_login_status'] = 1;		
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".intval($payment_notice['order_id']));
			if($order_info)
			{
				if($order_info['pay_status']==2)
				{
					$root['pay_status'] = 1;
					$root['info'] = "下单成功,购物愉快";
				}
				else
				{
					$root['pay_status'] = 0;
					$root['info'] = "订单末处理成功,请联系客服人员处理";
				}
			}
			else
			{
				$root['info'] = "无效的订单";	
			}
		}
		else
		{
			$root['user_login_status'] = 0;		
		}
		
		

		output($root);
	}
}
?>