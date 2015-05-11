<?php
//某个城市支持的相关城市ID，包含父分类
class deal_city_belone_ids_auto_cache extends auto_cache{

	public function load($param)
	{
		if(intval($param['city_id'])!=1)
		{
			return array(intval($param['city_id']),1);
		}
		else
		{
			return false;
		}
//		$key = $this->build_key(__CLASS__,$param);
//		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
//		$ids = $GLOBALS['fcache']->get($key);
//		if($ids===false)
//					{					
//						$ids_util = new ChildIds("deal_city");
//						$city_id = intval($param['city_id']);
//						$ids = $ids_util->getChildIds($city_id);
//						$ids[] = $city_id;
//						//开始取出父地区ID
//						$r_city_id = $city_id;
//						while($r_city_id!=0){
//							$r_city_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city where id = ".$r_city_id);
//							if($r_city_id!=0)
//							$ids[] = $r_city_id;
//						}
//						$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
//						$GLOBALS['fcache']->set($key,$ids);
//		}
//		return $ids;
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