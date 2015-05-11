<?php
class nearbyyouhui
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;
		$city_id = intval($GLOBALS['request']['city_id']);
		
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
		
		$now = get_gmtime();
		$sql_count = "select count(*) from ".DB_PREFIX."youhui ";
		$sql = "select id, supplier_id as merchant_id,name as title,list_brief as content,icon as merchant_logo,create_time,xpoint,ypoint,address as api_address,icon as image_1,
				(ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance  
				from ".DB_PREFIX."youhui";
		
		if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
		$where = " ypoint > $ybottom and ypoint < $ytop and xpoint > $xleft and xpoint < $xright and is_effect = 1 and begin_time<".$now." and (end_time = 0 or end_time > ".$now.") ";
		else
		$where = " is_effect = 1 and begin_time<".$now." and (end_time = 0 or end_time > ".$now.") ";
		
		
		
		$sql.= " where ".$where;
		$sql.=" order by distance asc ";				
		$sql_count.=" where ".$where;		
		$sql.=" limit ".$limit;

				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);

		
		$list = $GLOBALS['db']->getAll($sql);
		$youhui_list = array();
		foreach($list as $item){
			$item = m_youhuiItem($item);
			$item['distance'] = round($item['distance']);
			$youhui_list[] = $item;
		}

		$root['item'] = $youhui_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);
		
		output($root);
	}
}
?>