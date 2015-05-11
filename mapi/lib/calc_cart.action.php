<?php
class calc_cart{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);

		//print_r($user); exit;
		$cartdata = $GLOBALS['request']['cartdata'];		
		
		$root = array();
		$root['return'] = 1;
		$root['first_calc'] = $GLOBALS['request']['first_calc'];	
		
		if($user_id>0)
		{
			//用户登陆状态：1:成功登陆;0：未成功登陆
			$root['user_login_status']	=	1;
			//第一次计算,主要是处理一些初始化参数,比如：默认配送地址
			
			if ($GLOBALS['request']['first_calc']==1){
				$delivery = getUserAddr($user_id,false);
				
				$root['delivery'] = $delivery;
				$delivery_region = array(
				   		'region_lv1'=>intval($delivery['region_lv1']),
				   		'region_lv2'=>intval($delivery['region_lv2']),
				   		'region_lv3'=>intval($delivery['region_lv3']),
				   		'region_lv4'=>intval($delivery['region_lv4'])
				);	
				
				$root['send_mobile'] = $user['mobile'];//默认填上用户手机号码						
										
				$payment_id = intval($root['order_parm']['select_payment_id']);//默认支付方式
			}else{
				$delivery_region = array(
				   		'region_lv1'=>intval($GLOBALS['request']['region_lv1']),
				   		'region_lv2'=>intval($GLOBALS['request']['region_lv2']),
				   		'region_lv3'=>intval($GLOBALS['request']['region_lv3']),
				   		'region_lv4'=>intval($GLOBALS['request']['region_lv4'])
				);		
				$payment_id = intval($GLOBALS['request']['payment_id']);
			}			
			$res = insertCartData($user_id,session_id(),$cartdata);			
			if($res['info']!='')
			{
				//不可购买
				$root['info'] = $res['info'];
				$root['status'] = 0;
			}
			else
			{
				
				//可以购买
				$root['status'] = 1;
				$delivery_id = intval($requestData['delivery_id']);//配送方式;
				if ($delivery_id == 0)
					$delivery_id = intval($GLOBALS['m_config']['delivery_id']);//取系统配置
				
				$root['select_delivery_id'] = $delivery_id;
	
				$ecvSn = strim($GLOBALS['request']['ecv_sn']);//优惠券
				$ecvPassword = strim($GLOBALS['request']['ecv_pwd']);//优惠券密码			   			
			
				require_once APP_ROOT_PATH."system/libs/cart.php";
				$region4_id = intval($delivery_region['region_lv4']);
				$region3_id = intval($delivery_region['region_lv3']);
				$region2_id = intval($delivery_region['region_lv2']);
				$region1_id = intval($delivery_region['region_lv1']);
				
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
				
				$goods_list = $res['data']; 
				$GLOBALS['user_info']['id'] = $user_id;
				
				$ids = array();
				foreach($goods_list as $cart_goods)
				{
					array_push($ids,$cart_goods['deal_id']);
				}
				$ids_str = implode(",",$ids);
				
				$is_delivery = intval($GLOBALS['db']->getOne("select is_delivery from ".DB_PREFIX."deal where is_delivery = 1 and id in (".$ids_str.")"));
				if($is_delivery==0)
				$delivery_id = 0;
				
				$account_pay = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = 'Account'");
				if($account_pay)
				$data = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=1,$ecvSn,$ecvPassword,$goods_list); 
				else 
				$data = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=0,$ecvSn,$ecvPassword,$goods_list); 
				

				$root['use_user_money'] = floatval($data['account_money']);//使用会员余额支付金额
				$root['pay_money'] = $data['pay_price'];//还需要支付金额
				$root['feeinfo'] = getFeeItem($data);		

				$root['order_parm'] = init_order_parm($GLOBALS['m_config']);	
				
				
				$ecv_payment_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'"));
				
				//重新为order_parm赋值
				if($ecv_payment_id)
				{
					$forbid_ecv = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_payment where payment_id =".$ecv_payment_id." and deal_id in (".$ids_str.")");
					if($forbid_ecv)
					$root['order_parm']['has_ecv'] = 0;//无优惠券
				}
				else
				$root['order_parm']['has_ecv'] = 0;//无优惠券
				
				$has_coupon = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_coupon = 1 and id in (".$ids_str.")"));
				if(!$has_coupon)
				$root['order_parm']['has_moblie'] = 0;
				else
				$root['order_parm']['has_moblie'] = 1;

				$has_delivery = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_delivery = 1 and id in (".$ids_str.")"));		
				if(!$has_delivery)
				$root['order_parm']['has_delivery'] = 0;
				else
				$root['order_parm']['has_delivery'] = 1;
				
				$root['order_parm']['has_mcod'] = 1;
				$forbid_payment =	$GLOBALS['db']->getAll("select payment_id from ".DB_PREFIX."deal_payment where deal_id in (".$ids_str.")");
				foreach($forbid_payment as $forbid_payment_item)
				{
					foreach($root['order_parm']['payment_list'] as $k=>$v)
					{
						if($v['id']==$forbid_payment_item['payment_id'])
						{
							unset($root['order_parm']['payment_list'][$k]);
						}
					}
				}	
				
				$forbid_delivery =	$GLOBALS['db']->getAll("select delivery_id from ".DB_PREFIX."deal_delivery where deal_id in (".$ids_str.")");
				foreach($forbid_delivery as $forbid_delivery_item)
				{
					foreach($root['order_parm']['delivery_list'] as $k=>$v)
					{
						if($v['id']==$forbid_payment_item['delivery_id'])
						{
							unset($root['order_parm']['delivery_list'][$k]);
						}
					}
				}			
//has_delivery_list				
				//$root['order_parm']['delivery_list'] = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."delivery");
			}		
		}
		else
		{
			//未登录
			$root['user_login_status'] = 0;
		}

		output($root);
	}
}
?>