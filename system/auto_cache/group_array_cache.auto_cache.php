<?php
//分享采集接口缓存
class group_array_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$group_array = $GLOBALS['fcache']->get($key);
		if($group_array===false)
		{
			$group_list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."fetch_topic where is_effect = 1");
			foreach($group_list as $k=>$v)
			{
				$group_array[] = $v['class_name'];
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$group_array);
		}
		return $group_array;
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