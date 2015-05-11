<?php
class mapsearch{
	public function index(){
		
	
		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
		$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint
		$type = intval($GLOBALS['request']['type']); //-1:全部，0：优惠券；1：活动；2：团购；3：代金券；4：商家		
			
		$pi = 3.14159265;  //圆周率
		$r = 6378137;  //地球平均半径(米)
		$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance ";
		$condition = "  ypoint > $ybottom and ypoint < $ytop and xpoint > $xleft and xpoint < $xright ";
		$limit = 10;
		
		
		if($type==-1||$type==0)
		{
			//查优惠
			$now = get_gmtime();			
			$sql = "select id,name,icon,xpoint,ypoint $field_append from ".DB_PREFIX."youhui";
			$where = " where is_effect = 1 and begin_time<".$now." and (end_time = 0 or end_time > ".$now.") ";
			$where.=" and ".$condition;
			$sql.= $where;	
			$sql.=" limit $limit ";			
			$list = $GLOBALS['db']->getAll($sql);
			$youhui_list = array();
			foreach($list as $item){	
				$item['icon'] = get_abs_img_root($item['icon']);	
				$item['type'] = 0;		
				$item['distance'] = round($item['distance']);
				$youhui_list[] = $item;
			}			
		}
		
		
		if($type==-1||$type==1)
		{
			//查活动

			$res = m_search_event_list($limit,0,0,$condition, " distance asc ",$field_append);	
			$event_list = array();
			foreach($res['list'] as $item){	
				$item['icon'] = get_abs_img_root($item['icon']);	
				$item['type'] = 1;		
				$item['distance'] = round($item['distance']);
				$event_list[] = $item;
			}
			
		}
		
		if($type==-1||$type==2)
		{
			//查团购

			$res = m_get_deal_list($limit,0,0,array(DEAL_ONLINE),$condition,"distance asc",0,$field_append);
			$tuan_list = array();
			foreach($res['list'] as $item){	
				$item['icon'] = get_abs_img_root($item['icon']);	
				$item['type'] = 2;		
				$item['distance'] = round($item['distance']);
				$tuan_list[] = $item;
			}
		}
		
		
		if($type==-1||$type==3)
		{
			//查代金

			$res = m_search_youhui_list($limit,0,$condition," distance asc ",0,$field_append);
			$dianjin_list = array();
			foreach($res['list'] as $item){	
				$item['icon'] = get_abs_img_root($item['icon']);	
				$item['type'] = 3;		
				$item['distance'] = round($item['distance']);
				$dianjin_list[] = $item;
			}
		}
		
		
		if($type==-1||$type==4)
		{
			//查商家
			$sql = "select id,supplier_id,name,address,preview as icon,xpoint,ypoint $field_append from   ".DB_PREFIX."supplier_location  ";
			$sql.=" where ".$condition;
			$sql.=" order by distance asc limit ".$limit;
			$list = $GLOBALS['db']->getAll($sql);
			$merchant_list = array();
			foreach($list as $item){	
				$item['icon'] = get_abs_img_root($item['icon']);	
				$item['type'] = 4;
                                $item['id']=$item['supplier_id'];
				$item['distance'] = round($item['distance']);
				$merchant_list[] = $item;
			}
		}
		
		if($type==-1)
		{
			$result_list = array_merge($youhui_list,$event_list,$tuan_list,$dianjin_list,$merchant_list); 
		}
		elseif($type=0)
		{
			$result_list= $youhui_list;
		}
		elseif($type=1)
		{
			$result_list= $event_list;
		}
		elseif($type=2)
		{
			$result_list= $tuan_list;
		}
		elseif($type=3)
		{
			$result_list= $dianjin_list;
		}
		elseif($type=4)
		{
			$result_list= $merchant_list;
		}
		
		if($result_list)
		$root['item'] = $result_list;
		else
		$root['item'] = array();

		
		output($root);
		
	}
}