<?php
//团购的导航
class cache_nav_list_tuan_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$nav_list = $GLOBALS['fcache']->get($key);
		if($nav_list === false)
		{
			$nav_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."nav where is_effect = 1 and is_shop in (0,1) order by sort desc");
			$nav_list = format_nav_list($nav_list);
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$nav_list);
		}
		return $nav_list;
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