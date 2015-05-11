<?php
class daijinlist{
	public function index(){
		require_once APP_ROOT_PATH.'app/Lib/youhui_lib.php'; 
		
		$catalog_id = intval($GLOBALS['request']['catalog_id']);//商品分类ID
		$city_id = intval($GLOBALS['request']['city_id']);//城市分类ID			
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		
		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
		$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint
		
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		if($keyword)
		{
			$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kws[$k] = str_to_unicode_string($item);
			}
			$ukeyword = implode(" ",$kws);
			$condition ="  (match(tag_match,name_match,locate_match,deal_cate_match) against('".$ukeyword."' IN BOOLEAN MODE) or name like '%".$keyword."%') ";
		}
		
		if($xpoint>0)
		{		
			$pi = 3.14159265;  //圆周率
			$r = 6378137;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance ";
			if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
			{
			if($condition!="")
			$condition.=" and ";
			$condition.= "  ypoint > $ybottom and ypoint < $ytop and xpoint > $xleft and xpoint < $xright ";
			}
			$order = " distance asc ";
		}
		else
		{
			$field_append = "";
			$order = " sort desc ";
		}	
		
		$deals = m_search_youhui_list($limit,intval($catalog_id),$condition,$order,$city_id,$field_append);
//		$deals = get_goods_list($limit,$catalog_id,"","sort desc",false,$city_id);
		$list = $deals['list'];
		$count= $deals['count'];
		
		$page_total = ceil($count/$page_size);
		
		$root = array();
		$root['return'] = 1;

		
		$goodses = array();
		foreach($list as $item)
		{
			//$goods = array();
			$goods = getGoodsArray($item);
			$goods['distance'] = round($goods['distance']);
			$goodses[] = $goods;
		}
		$root['item'] = $goodses;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);

		
		output($root);
		
	}
}