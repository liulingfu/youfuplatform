<?php
class add_addr{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);			
			
		$root = array();
		$root['return'] = 1;		
		if($user_id>0)
		{
			$root['user_login_status'] = 1;		

			$id = intval($GLOBALS['request']['id']);//id,有ID值则更新，无ID值，则插入
		
			$addr = array();
			$addr['user_id'] = $user_id;
		
			$addr['region_lv1'] = intval($GLOBALS['request']['region_lv1']);//国家
			$addr['region_lv2'] = intval($GLOBALS['request']['region_lv2']);//省
			$addr['region_lv3'] = intval($GLOBALS['request']['region_lv3']);//城市
			$addr['region_lv4'] = intval($GLOBALS['request']['region_lv4']);//地区/县
		
			$addr['consignee'] = addslashes(trim($GLOBALS['request']['consignee']));//联系人姓名
			$addr['address'] = addslashes(trim($GLOBALS['request']['delivery_detail']));//详细地址
			$addr['mobile'] = addslashes(trim($GLOBALS['request']['phone']));//手机号码
			$addr['zip'] = addslashes(trim($GLOBALS['request']['postcode']));//邮编
		
			if ($id == 0){
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee", $addr, 'INSERT');
				$addr_id = $GLOBALS['db']->insert_id();
			}else{
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee", $addr, 'UPDATE', "user_id = {$user_id} and id = {$id}");
				$addr_id = $id;
			}
			
			$root['id'] = $addr_id;
		}
		else
		{
			$root['user_login_status'] = 0;		
		}		
	
		output($root);
	}
}
?>