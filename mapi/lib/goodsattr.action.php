<?php
class goodsattr{
	public function index(){
		$goods_id = intval($GLOBALS['request']['id']);//商品ID
		
		$root = array();
		$root['return'] = 1;
		$root['attr'] = getAttrArray($goods_id);
	
		output($root);
	}
}
?>