<?php
//文章分类下的子类ID
class deal_shop_acate_belone_ids_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
	 	$ids = $GLOBALS['fcache']->get($key);
		if($ids === false)
		{
			$cate_id = intval($param['cate_id']);
			$ids_util = new ChildIds("article_cate");
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