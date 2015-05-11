<?php
class goodsdesc{
	public function index(){
		require_once APP_ROOT_PATH.'app/Lib/deal.php'; 
		/**

		 * has_attr: 0:无属性; 1:有属性
		 * 有商品属性在要购买时，要选择属性后，才能购买
		 
		 * change_cart_request_server: 
		 * 编辑购买车商品时，需要提交到服务器端，让服务器端通过一些判断返回一些信息回来(如：满多少钱，可以免运费等一些提示)
		 * 0:提交，1:不提交；
		 
		 * image_attr_a_id_{$attr_a_id} 图片列表，可以根据属性ID值，来切换图片列表;默认为：0
		 * limit_num: 库存数量
		 
		 */
		
		$id = intval($GLOBALS['request']['id']);//商品ID
		
		$item = get_deal($id);
		
		$root = getGoodsArray($item);		
	
		$root['return'] = 1;
		$root['attr'] = getAttrArray($id);
		
		$images = array();
		//image_attr_1_id_{$attr_1_id} 图片列表，可以根据属性ID值，来切换图片列表
		$sql = "select img from ".DB_PREFIX."deal_gallery where deal_id = ".intval($id);
		$list = $GLOBALS['db']->getAll($sql);
	
		$gallery = array();
		$big_gallery = array();
		foreach($list as $k=>$image){
			$gallery[] = get_abs_img_root(get_spec_image($image['img'],320,320,0));
			$big_gallery[] = get_abs_img_root(get_spec_image($image['img'],0,0,0));	
		}
		$root['gallery'] = $gallery;
		$root['big_gallery'] = $big_gallery;
			
		output($root);	
	}
}
?>