<?php
class nearbyuser
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();
		$root['return'] = 1;
		$city_id = intval($GLOBALS['request']['city_id']);
		
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		
		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$m_distance = doubleval($GLOBALS['request']['m_distance']);   //范围(米)
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
		$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint

		
		
		$page = intval($GLOBALS['request']['page']); //分页
		
		$page=$page==0?1:$page;
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$pi = 3.14159265;  //圆周率
		$r = 6378137;  //地球平均半径(米)
		

		$sql_count = "select count(*) from ".DB_PREFIX."user ";
		$sql = "select id,xpoint,ypoint,locate_time,user_name,daren_title, sex,
				(ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance  
				from ".DB_PREFIX."user ";
		if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
		$where = " ypoint > $ybottom and ypoint < $ytop and xpoint > $xleft and xpoint < $xright  and is_effect = 1 and is_delete = 0 ";
		else
		$where = " is_effect = 1 and is_delete = 0 ";
		$sql.= " where ".$where;
		$sql.=" order by distance asc ";				
		$sql_count.=" where ".$where;		
		$sql.=" limit ".$limit;

				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);

		
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k => $item){
			
		
			$item['uid'] = $item['id'];
			if($item['daren_title']!='')
			$item['user_name'] .= "[".$item['daren_title']."]";
			$item['fans'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ". $item['id']);
			$item['user_avatar'] = get_abs_img_root(get_muser_avatar($item['id'],"big"));
			if($item['id']==$user_data['id'])
			{
				$item['is_follow'] = -1;
			}
			else
			{
				$focus_uid = intval($item['id']);
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".intval($user_data['id'])." and focused_user_id = ".intval($focus_uid));
				if($focus_data)
				$item['is_follow'] = 1;
				else
				$item['is_follow'] = 0;
			}
			
			$item['locate_time_format'] = pass_date($item['locate_time']);
			$item['distance'] = round($item['distance']);
			$list[$k] = $item;
		}

		$root['item'] = $list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		
		output($root);
	}
}
?>