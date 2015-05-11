<?php
//商圈缓存
class cache_area_auto_cache extends auto_cache{
	public function load($param)
	{
		static $area_list;
		if($area_list)return $area_list;		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$area_list = $GLOBALS['fcache']->get($key);				
		if($area_list === false)
		{		
			$city_id = intval($param['city_id']);
			if($city_id>0)
			$area_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id);
			else
			$area_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area");
			foreach($area_list_rs as $k=>$v)
			{
				$area_list[$v['id']] = $v;
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$area_list);
		}
		return $area_list;	
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