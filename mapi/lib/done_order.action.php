<?php
class done_order{
	public function index(){
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);			
		$order_id = intval($GLOBALS['request']['id']);	
		$region4_id = intval($GLOBALS['request']['region_lv4']);
				$region3_id = intval($GLOBALS['request']['region_lv3']);
				$region2_id = intval($GLOBALS['request']['region_lv2']);
				$region1_id = intval($GLOBALS['request']['region_lv1']);
				
				if ($region4_id==0)
				{
					if ($region3_id==0)
					{
						if ($region2_id==0)
						{
							$region_id = $region1_id;
						}
						else
						$region_id = $region2_id;
					}
					else
					$region_id = $region3_id;
				}
				else
				$region_id = $region4_id;
				
		$payment_id = intval($GLOBALS['request']['payment_id']);
		$consignee = strim($GLOBALS['request']['consignee']);
		$delivery_detail = strim($GLOBALS['request']['delivery_detail']);
		$phone = strim($GLOBALS['request']['phone']);
		$postcode = strim($GLOBALS['request']['postcode']);
		$content = strim($GLOBALS['request']['content']);
		$send_mobile = strim($GLOBALS['request']['send_mobile']);
		//$delivery_id = $GLOBALS['m_config']['delivery_id'];
		$delivery_id = $GLOBALS['request']['delivery_id'];
		
		$root = array();
		$root['return'] = 1;		
		if($user_id>0)
		{
			$root['user_login_status'] = 1;	
		
			//开始计算订单
			
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if(!$order_info)
			{				
				$root['status'] =0;
				$root['info'] = '订单不存在';	
				output($root);
			}
			require_once APP_ROOT_PATH."system/libs/cart.php";	
			$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
			$GLOBALS['user_info']['id'] = $user_id;
			$account_pay = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = 'Account'");
			if($account_pay)
				$data = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=1,'','',$goods_list,$order_info['account_money'],$order_info['ecv_money']); 
			else 
				$data = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=0,'','',$goods_list,$order_info['account_money'],$order_info['ecv_money']); 
			
			
				
			if($data['is_delivery'] == 1)
			{
						//配送验证
						if(!$data['region_info']||$data['region_info']['region_level'] != 4)
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS'];	
							output($root);
						}
						if($consignee=='')
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_CORRECT_CONSIGNEE'];	
							output($root);							

						}
						if($delivery_detail=='')
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_CORRECT_ADDRESS'];	
							output($root);								
						}
						if($postcode=='')
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_CORRECT_ZIP'];	
							output($root);								
						}
						if($phone=='')
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_MOBILE_PHONE'];	
							output($root);								
						}
						if(!check_mobile($phone))
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];	
							output($root);							
						}
						if(!$data['delivery_info'])
						{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['PLEASE_SELECT_DELIVERY'];	
							output($root);							
						}			
			}
			
			if(round($data['pay_price'],4)>0&&!$data['payment_info'])
			{
							$root['status'] =0;
							$root['info'] = $GLOBALS['lang']['PLEASE_SELECT_PAYMENT'];	
							output($root);							
			}	
			//结束验证订单接交信息
			
			
			//开始修正订单
			$now = get_gmtime();
			$order_info['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
			$order_info['memo'] = htmlspecialchars($content);
			$order_info['region_lv1'] = intval($region1_id);
			$order_info['region_lv2'] = intval($region2_id);
			$order_info['region_lv3'] = intval($region3_id);
			$order_info['region_lv4'] = intval($region4_id);
			$order_info['address']	=	htmlspecialchars($delivery_detail);
			$order_info['mobile']	=	htmlspecialchars($phone);
			$order_info['consignee']	=	htmlspecialchars($consignee);
			$order_info['zip']	=	htmlspecialchars($postcode);
			$order_info['delivery_fee'] = $data['delivery_fee'];
			$order_info['delivery_id'] = $data['delivery_info']['id'];
			$order_info['payment_id'] = $data['payment_info']['id'];
			$order_info['payment_fee'] = $data['payment_fee'];
			$order_info['delivery_fee'] = $data['delivery_fee'];
			$order_info['discount_price'] = $data['user_discount'];

			$coupon_mobile = htmlspecialchars($send_mobile);
			if($coupon_mobile!='')
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$coupon_mobile."' where id = ".intval($user_id));
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order_info,'UPDATE','id='.$order_info['id'],'SILENT'); 
			
			
			
		
		//生成order_id 后
		//1. 余额支付
		
		$account_money = $data['account_money'];
		if(floatval($account_money) > 0)
		{
			$GLOBALS['payment_lang'] = array(
			'name'	=>	'余额支付',
			'account_credit'	=>	'帐户余额',
			'use_user_money' =>	'使用余额支付',
			'use_all_money'	=>	'全额支付',
			'USER_ORDER_PAID'	=>	'%s订单付款,付款单号%s'
			);
			$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
			$payment_notice_id = make_payment_notice($account_money,$order_info['id'],$account_payment_id);			
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
			$account_payment = new Account_payment();
			$account_payment->get_payment_code($payment_notice_id);
		}

		$rs = order_paid($order_info['id']); 		
			$root['status'] = 1;
			$root['info'] = '';
			$root['has_pay'] = 1;
			$root['order_id'] = $order_id;
		}
		else
		{
			$root['user_login_status'] = 0;	
			$root['status'] =0;
			$root['info'] = '请先登录';	
		}		
	
		output($root);
	}
}
?>