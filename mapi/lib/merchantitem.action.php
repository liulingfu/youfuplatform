<?php
class merchantitem
{
	public function index()
	{

		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);

		$id = intval($GLOBALS['request']['id']);

		$act_2 = $GLOBALS['request']['act_2'];//子操作 空:没子操作; dz:设置打折提醒
		
		if ($act_2 != '' && $user_id == 0){
			$root['act_2'] = $act_2;
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			output($root);
		}
		
		if ($act_2 == "dz"){
				$sql = "select uid from  ".DB_PREFIX."supplier_dy where uid = $user_id and supplier_id = $id";
				if (intval($GLOBALS['db']->getOne($sql) > 0)) {
					//已经设置打折提醒，则取消
					$sql = "delete from ".DB_PREFIX."supplier_dy where uid = $user_id and supplier_id = $id";
					$GLOBALS['db']->query($sql);
				}else{
					//没设置，则设置
					$merchant_dy = array(
						 						'uid' => $user_id,
						 						'supplier_id' => $id
					);
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_dy", $merchant_dy, 'INSERT');
				}
		}

			$sql = "select a.id,a.name,a.content as brief,a.preview as logo, b.uid as is_dy from ".DB_PREFIX."supplier as a ".
			 			   " left outer join ".DB_PREFIX."supplier_dy as b on b.uid = $user_id and b.supplier_id = a.id ".						   
							"where a.id = $id ";
		//echo $sql; exit;
		$merchant = $GLOBALS['db']->getRow($sql);
		$root = m_merchantItem($merchant);
		
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
			$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint
			$pi = 3.14159265;  //圆周率
			$r = 6378137;  //地球平均半径(米)
		
			$sql = "select a.id,a.name,a.address,a.tel,a.supplier_id as brand_id,a.brief,a.preview as logo,a.xpoint,a.ypoint,a.route as api_address,(select count(*) from ".DB_PREFIX."supplier_location_dp as dp where dp.supplier_location_id = a.id and dp.status = 1) as comment_count, c.name as city_name, 
			(ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((a.ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((a.ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (a.xpoint * $pi) / 180 ) ) * $r) as distance 
			  from ".DB_PREFIX."supplier_location as a ".			 			   
						   " left outer join ".DB_PREFIX."deal_city as c on c.id = a.city_id ".
							"where a.supplier_id = $id ";			
			$list = $GLOBALS['db']->getAll($sql);
			$list_merchant = array();
			foreach($list as $item){			
				$list_merchant[] = m_merchantItem($item);
			}
			$root['list_merchant'] = $list_merchant;		
		
		
		$root['return'] = 1;
		$root['user_login_status'] = 1;
		output($root);
	}
}
?>