<?php
class getnewyouhui
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;

		$city_id = intval($GLOBALS['request']['city_id']);
		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码
		//检查用户,用户密码
		$user_info = user_check($email,$pwd);
		$user_id  = intval($user_info['id']);

		$last_check_time = intval($GLOBALS['request']['last_check_time']);

        //print_r($user_info);exit;
		$brand_ids = $GLOBALS['db']->getOne("select group_concat(brand_id) from ".DB_PREFIX."brand_dy where uid = ".$user_id);
		//print_r($brand_ids);
		if(!$brand_ids)
			$brand_ids = 0;
		if( substr($brand_ids,-1,1)==',')
		{
			$brand_ids = substr($brand_ids,0,-1);
		}

		$merchant_ids = $GLOBALS['db']->getOne("select group_concat(supplier_location_id) from ".DB_PREFIX."supplier_location_dy where uid = ".$user_id);
		if(!$merchant_ids)
			$merchant_ids = 0;
		if( substr($merchant_ids,-1,1)==',')
		{
			$merchant_ids = substr($merchant_ids,0,-1);
		}

		$sql_count = "select count(*) from ".DB_PREFIX."youhui";


		$now = get_gmtime();
		$where = " where 1 = 1 and create_time > ".$last_check_time." and is_effect = 1 and begin_time<".$now." and (end_time = 0 or end_time > ".$now.") and (brand_id in (".$brand_ids.") or supplier_location_id in (".$merchant_ids."))";
		$sql_count.=$where;

		//echo $sql_count; exit;
        if($brand_ids == 0 && $merchant_ids == 0) {
            $root['count'] = 0;
        }else{
            $root['count'] = intval($GLOBALS['db']->getOne($sql_count));
        }

        $root['adv_youhui'] = m_adv_youhui($city_id);
		/*
		$root['adv_youhui'] = m_adv_youhui($city_id);
		$root['newslist'] = $GLOBALS['m_config']['newslist'];

		$latitude = floatval($GLOBALS['request']['latitude']);//ypoint
		$longitude = floatval($GLOBALS['request']['longitude']);//xpoint
		if ($user_id > 0 && $latitude > 0 && $longitude > 0){
			$user_x_y_point = array(
										'uid' => $user_id,
										'xpoint' => $longitude,
										'ypoint' => $latitude,
										'locate_time' => get_gmtime(),
			);
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_x_y_point", $user_x_y_point, 'INSERT');
			$sql = "update ".DB_PREFIX."user set xpoint = $longitude, ypoint = $latitude, locate_time = ".get_gmtime()." where id = $user_id";
			$GLOBALS['db']->query($sql);
		}
		*/
		output($root);
	}
}
?>