<?php
//生活服务分类父类下的所有子类ID,不包含父分类
class deal_sub_cate_ids_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$ids = $GLOBALS['fcache']->get($key);
			if($ids === false)
			{
				$cate_id = intval($param['cate_id']);
				$ids_util = new ChildIds("deal_cate");
				$ids = $ids_util->getChildIds($cate_id);
				$ids[] = $cate_id;
				$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$GLOBALS['fcache']->set($key,$ids);
			}
			return $ids;
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