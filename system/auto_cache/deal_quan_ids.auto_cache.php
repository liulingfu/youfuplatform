<?php
//某个商圈下的子商圈ID
class deal_quan_ids_auto_cache extends auto_cache{
	public function load($param)
	{
		return array(intval($param['quan_id']));
//		$key = $this->build_key(__CLASS__,$param);
//		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
//		$ids = $GLOBALS['fcache']->get($key);
//		if($ids===false)
//		{
//				$quan_id = intval($param['quan_id']);
//				$ids_util = new ChildIds("area");
//				$ids = $ids_util->getChildIds($quan_id);
//				$ids[] = $quan_id;
//				$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
//				$GLOBALS['fcache']->set($key,$ids);
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