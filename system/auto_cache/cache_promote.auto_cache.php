<?php
//促销
class cache_promote_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$promote = $GLOBALS['fcache']->get($key);
		if($promote===false)
		{
			$promote_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."promote order by sort");
			foreach($promote_rs as $k=>$v)
			{
				$v['config'] = unserialize($v['config']);
				$promote[$v['id']] = $v;
			}
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$promote);
		}	
		return $promote;
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