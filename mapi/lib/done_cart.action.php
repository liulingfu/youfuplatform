<?php
class done_cart{
	public function index(){
		
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);		
		$money = floatval($user['money']);
			
		$root = array();
		$root['return'] = 1;
		if($user_id>0)
		{
			$root['user_login_status']	=	1;
			//已登录
			
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
			
			$payment = intval($GLOBALS['request']['payment_id']);
			$account_money = floatval($GLOBALS['request']['use_user_money']);
			$address = strim($GLOBALS['request']['delivery_detail']);
			$consignee_mobile = strim($GLOBALS['request']['phone']);
			$zip = strim($GLOBALS['request']['postcode']);
			$consignee = strim($GLOBALS['request']['consignee']);
			$ecvsn = $GLOBALS['request']['ecv_sn']?strim($GLOBALS['request']['ecv_sn']):'';
			$ecvpassword = $GLOBALS['request']['ecv_pwd']?strim($GLOBALS['request']['ecv_pwd']):'';
			$order_memo = strim($GLOBALS['request']['content']);	
			$send_mobile = strim($GLOBALS['request']['send_mobile']);			
			
			$delivery_id = intval($GLOBALS['request']['delivery_id']);	
			//$delivery_id = intval($GLOBALS['m_config']['delivery_id']);

			$account_pay = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = 'Account'");
			if($account_pay)
			$all_account_money = 1;
			else
			$all_account_money = 0;
			
			$res = insertCartData($user_id,session_id(),$GLOBALS['request']['cartdata']);
			if($res['info']!='')
			{
				//失败有错误
				$root['status'] = 0;
				$root['info'] = $res['info'];
				output($root);
			}
			else
			{
				//可以提交订单
				$goods_list = $res['data'];
				require_once APP_ROOT_PATH."system/libs/cart.php";
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
				$data = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list); 
							
				if($data['is_delivery'] == 1)
				{
							//配送验证
							if(!$data['region_info']||$data['region_info']['region_level'] != 4)
							{
								$root['info'] = $GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS'];
								$root['status'] = 0;
								output($root);
							}
							elseif($consignee=='')
							{
								$root['info'] = $GLOBALS['lang']['FILL_CORRECT_CONSIGNEE'];
								$root['status'] = 0;
								output($root);
							}
							elseif($address=='')
							{
								$root['info'] = $GLOBALS['lang']['FILL_CORRECT_ADDRESS'];
								$root['status'] = 0;
								output($root);
							}
							elseif($zip=='')
							{
								$root['info'] = $GLOBALS['lang']['FILL_CORRECT_ZIP'];
								$root['status'] = 0;
								output($root);
							}
							elseif($consignee_mobile=='')
							{
								$root['info'] = $GLOBALS['lang']['FILL_MOBILE_PHONE'];	
								$root['status'] = 0;	
								output($root);						
							}
							elseif(!check_mobile(trim($consignee_mobile)))
							{
								$root['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
								$root['status'] = 0;
								output($root);
							}
							elseif(!$data['delivery_info'])
							{
								$root['info'] = $GLOBALS['lang']['PLEASE_SELECT_DELIVERY'];
								$root['status'] = 0;
								output($root);
							}			
							
				}
					
				
						
				if(round($data['pay_price'],4)>0&&!$data['payment_info'])
				{
					$root['info'] = $GLOBALS['lang']['PLEASE_SELECT_PAYMENT'];
					$root['status'] = 0;
					output($root);
				}	
				else
				{
					
					//验证成功
					//开始生成订单
					$now = get_gmtime();
					$order['type'] = 0; //普通订单
					$order['user_id'] = $user_id;
					$order['create_time'] = $now;	
					$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
					$order['pay_amount'] = 0;  
					$order['pay_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
					$order['delivery_status'] = $data['is_delivery']==0?5:0;  
					$order['order_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
					$order['return_total_score'] = $data['return_total_score'];  //结单后送的积分
					$order['return_total_money'] = $data['return_total_money'];  //结单后送的现金
					$order['memo'] = htmlspecialchars($order_memo);
					$order['region_lv1'] = $region1_id;
					$order['region_lv2'] = $region2_id;
					$order['region_lv3'] = $region3_id;
					$order['region_lv4'] = $region4_id;
					$order['address']	=	htmlspecialchars($address);
					$order['mobile']	=	htmlspecialchars($consignee_mobile);
					$order['consignee']	=	htmlspecialchars($consignee);
					$order['zip']	=	htmlspecialchars($zip);
					$order['deal_total_price'] = $data['total_price'];   //团购商品总价
					$order['discount_price'] = $data['user_discount'];
					$order['delivery_fee'] = $data['delivery_fee'];
					$order['ecv_money'] = 0;
					$order['account_money'] = 0;
					$order['ecv_sn'] = '';
					$order['delivery_id'] = $data['delivery_info']['id'];
					$order['payment_id'] = $data['payment_info']['id'];
					$order['payment_fee'] = $data['payment_fee'];
					$order['payment_fee'] = $data['payment_fee'];
					$order['bank_id'] = 0;
					

					if($send_mobile!='')
					$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$send_mobile."' where id = ".$user_id);
					
					
					do
					{
						$order['order_sn'] = to_date(get_gmtime(),"Ymdhis").rand(10,99);
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
						$order_id = intval($GLOBALS['db']->insert_id());
					}while($order_id==0);
					//end 验证成功提交订单成功					
					//开始生成订单产品以及支付
				
					//生成订单商品
					foreach($goods_list as $k=>$v)
					{
						$goods_item = array();
						$goods_item['deal_id'] = $v['deal_id'];
						$goods_item['number'] = $v['number'];
						$goods_item['unit_price'] = $v['unit_price'];
						$goods_item['total_price'] = $v['total_price'];
						$goods_item['name'] = addslashes($v['name']);
						$goods_item['sub_name'] = addslashes($v['sub_name']);
						$goods_item['attr'] = $v['attr'];
						$goods_item['verify_code'] = $v['verify_code'];
						$goods_item['order_id'] = $order_id;
						$goods_item['return_score'] = $v['return_score'];
						$goods_item['return_total_score'] = $v['return_total_score'];
						$goods_item['return_money'] = $v['return_money'];
						$goods_item['return_total_money'] = $v['return_total_money'];
						$goods_item['buy_type']	=	$v['buy_type']; 
						$goods_item['attr_str']	=	$v['attr_str']; 
						
						$deal_info = load_auto_cache("cache_deal_cart",array("id"=>$v['deal_id']));
						$goods_item['balance_unit_price'] = $deal_info['balance_price'];
						$goods_item['balance_total_price'] = $deal_info['balance_price'] * $v['number'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_item",$goods_item,'INSERT','','SILENT'); 	
					}					
					//开始更新订单表的deal_ids
					
					$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set deal_ids = '".$deal_ids."' where id = ".$order_id);
					
					if($data['is_delivery']==1)
					{
						//保存收款人
						$user_consignee = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where user_id = ".$user_id." order by id desc");
						$user_consignee['region_lv1'] = $region1_id;
						$user_consignee['region_lv2'] = $region2_id;
						$user_consignee['region_lv3'] = $region3_id;
						$user_consignee['region_lv4'] = $region4_id;
						$user_consignee['address']	=	htmlspecialchars($address);
						$user_consignee['mobile']	=	htmlspecialchars($consignee_mobile);
						$user_consignee['consignee']	=	htmlspecialchars($consignee);
						$user_consignee['zip']	=	htmlspecialchars($zip);
						$user_consignee['user_id']	=	$user_id;
						if(intval($user_consignee['id'])==0)
						{
							//新增 
							$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'INSERT','','SILENT'); 	
						}
						else
						{
							//更新
							$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'UPDATE','id='.$user_consignee['id'],'SILENT'); 
							rm_auto_cache("consignee_info",array("consignee_id"=>intval($user_consignee['id'])));
						}
					}					
					
					//生成order_id 后
					//1. 代金券支付
					$ecv_data = $data['ecv_data'];
					if($ecv_data)
					{
						$ecv_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
						$payment_notice_id = make_payment_notice($ecv_data['money'],$order_id,$ecv_payment_id);
						require_once APP_ROOT_PATH."system/payment/Voucher_payment.php";
						$voucher_payment = new Voucher_payment();
						$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
					}
					
					//2. 余额支付
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
						$payment_notice_id = make_payment_notice($account_money,$order_id,$account_payment_id);
						require_once APP_ROOT_PATH."system/payment/Account_payment.php";
						$account_payment = new Account_payment();
						$account_payment->get_payment_code($payment_notice_id);
					}
					

					$root['order_id'] = $order_id;
					$rs = order_paid($order_id);  
					if($rs)
					{
						$root['pay_status'] = 1;
					}
					else
					{
						$root['pay_status'] = 0;
					}
					//end 订单产品生成及支付
					$root['status'] =1;
					
				}
				//end 提交订单
			}			
		}
		else
		{
			$root['user_login_status']	=	1;
			$root['status'] =0;
			//未登录
		}
		output($root);	
	}
}
?>