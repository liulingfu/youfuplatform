<?php
class searchgoods{
	public function index(){
		require_once APP_ROOT_PATH.'app/Lib/deal.php'; 
		
		//$catalog_id = intval($GLOBALS['request']['catalog_id']);//商品分类ID
		$city_id = intval($GLOBALS['request']['city_id']);//城市分类ID			
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']); //分页
		$page=$page==0?1:$page;
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$deals = get_deal_list($limit,$catalog_id,$city_id,array(DEAL_ONLINE),"buy_type<>1 and is_lottery = 0 and name like '%".$keyword."%'","sort desc");
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
			$goodses[] = $goods;
		}
		$root['item'] = $goodses;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);

		
		output($root);
		
	}
}