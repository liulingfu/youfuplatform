<?php
//地区信息
class cache_region_conf_auto_cache extends auto_cache{
	public function load($param)
	{
		static $region_list;
		if($region_list)return $region_list;		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$region_list = $GLOBALS['fcache']->get($key);				
		if($region_list === false)
		{		
			$region_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf");
			foreach($region_list_rs as $k=>$v)
			{
				$region_list[$v['id']] = $v;
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$region_list);
		}
		return $region_list;	
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