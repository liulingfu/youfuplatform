<?php
//商品数据的静态缓存
class static_goods_info_auto_cache extends auto_cache{
	public function load($param)
	{
		static $deal;
		if($deal)return $deal;
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$deal = $GLOBALS['fcache']->get($key);				
		if($deal === false)
		{		
			$id = intval($param['id']);
			$deal = $GLOBALS['db']->getRow("select begin_time,end_time,success_time,origin_price,current_price,discount,supplier_id,brand_id,id,uname from ".DB_PREFIX."deal where is_delete = 0 and id = ".$id);

			if($deal)
			{
				//格式化数据
				$deal['begin_time_format'] = to_date($deal['begin_time']);
				$deal['end_time_format'] = to_date($deal['end_time']);
				$deal['success_time_format']  = to_date($deal['success_time']);
				$deal['origin_price_format'] = format_price($deal['origin_price']);
				$deal['current_price_format'] = format_price($deal['current_price']);
					
				if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
					$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];
				else
					$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
					
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);
				
				$deal['discount'] = round($deal['discount'],2);
					
				$deal['save_price_format'] = format_price($deal['save_price']);
				
				//团购图片集
				$img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
				foreach($img_list as $k=>$v)
				{
					$img_list[$k]['origin_img'] = preg_replace("/\/big\//","/origin/",$v['img']);
				}
				$deal['image_list'] = $img_list;
					
				//商户信息
				$deal['supplier_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($deal['supplier_id']));
				$deal['supplier_address_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");
					
				//品牌信息
				$deal['brand_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".intval($deal['brand_id']));
				
					
				//属性列表
				$deal_attrs_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_attr where deal_id = ".intval($deal['id'])." order by id asc");
				if($deal_attrs_res)
				{
					foreach($deal_attrs_res as $k=>$v)
					{
						$deal_attr[$v['goods_type_attr_id']]['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."goods_type_attr where id = ".intval($v['goods_type_attr_id']));
						$deal_attr[$v['goods_type_attr_id']]['attrs'][] = $v;
					}
					$deal['deal_attr_list'] = $deal_attr;
				}
					
				$locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."deal_location_link as b on a.id = b.location_id where a.is_effect = 1 and b.deal_id = ".intval($deal['id']));
					
				$json_location = array();
				foreach($locations as $litem)
				{
					$arr = array();
					$arr['title'] = $litem['name'];
					$arr['address'] = $litem['address'];
					$arr['tel'] = $litem['tel'];
					$arr['lng'] = $litem['xpoint'];
					$arr['lat'] = $litem['ypoint'];
					$json_location[] = $arr;
				}
				$deal['json_location'] = json_encode($json_location);
				$deal['locations'] = $locations;
				if($deal['uname']!='')
					$gurl = url("shop","goods",array("id"=>$deal['uname']));
				else
					$gurl = url("shop","goods",array("id"=>$deal['id']));
					
				$deal['share_url'] = $gurl;
			}
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$deal);
		}
		return $deal;	
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['fcache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['fcache']->clear();
	}
}
?>