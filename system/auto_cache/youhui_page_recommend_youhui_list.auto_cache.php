<?php
//免费优惠券列表右侧的推荐优惠券
class youhui_page_recommend_youhui_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$free_youhui_list = $GLOBALS['fcache']->get($key);
		if($free_youhui_list===false)
		{
			$free_youhui_list = get_free_youhui_list(app_conf("SIDE_DEAL_COUNT"), $cate_id=0, $where='',$orderby = 'sms_count desc');
			$free_youhui_list = $free_youhui_list['list'];
			foreach($free_youhui_list as $k=>$v)
			{
					$free_youhui_list[$k]['url'] = url("youhui","fdetail",array("id"=>$v['id']));
					$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($v['supplier_id']));
					$locations = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($supplier_info['id']));
					if(count($locations)!=1)
					$supplier_info['url'] = url("youhui","store",array("id"=>$supplier_info['id']));
					else
					$supplier_info['url'] = url("youhui","store#view",array("id"=>$locations[0]['id'])); 
					$free_youhui_list[$k]['supplier_info'] = $supplier_info;
			}	
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$free_youhui_list);	
		}	
		return $free_youhui_list;
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