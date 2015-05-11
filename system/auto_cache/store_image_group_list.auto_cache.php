<?php
//商户分类下的图片分组
class store_image_group_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$images_group_list = $GLOBALS['fcache']->get($key);
 		if($images_group_list === false)
 		{
 			$cate_id = intval($param['cate_id']);
 			$images_group_list = $GLOBALS['db']->getAll("SELECT ig.id,ig.name FROM ".DB_PREFIX."images_group AS ig LEFT JOIN ".DB_PREFIX."images_group_link as igl ON  ig.id= igl.images_group_id WHERE igl.category_id =".$cate_id." ORDER BY ig.sort desc ,ig.id ASC ");
 			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
 			$GLOBALS['fcache']->set($key,$images_group_list);
 		}
 		return $images_group_list;
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