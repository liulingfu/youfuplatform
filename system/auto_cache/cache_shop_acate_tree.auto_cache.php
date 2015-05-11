<?php
//指定文章父分类下子分类树状格式化后的结果
class cache_shop_acate_tree_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);
		if($cate_list===false)
		{
			$pid = intval($param['pid']);
			require_once APP_ROOT_PATH."system/utils/child.php";
			require_once APP_ROOT_PATH."system/utils/tree.php";
			$ids_util = new child("article_cate");
			$ids = $ids_util->getChildIds($pid);
			$ids[] = $pid;
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."article_cate where is_effect = 1 and is_delete = 0 and type_id = 0 and id in (".implode(",",$ids).")");
			foreach($cate_list as $k=>$v)
			{
				if($v['uname']!='')
				$curl = url("shop","acate",array("id"=>$v['uname']));
				else
				$curl = url("shop","acate",array("id"=>$v['id']));
				$cate_list[$k]['url'] = $curl;
			}	
			$tree_util = new tree();
			$cate_list = $tree_util->toFormatTree($cate_list,'name');	
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$cate_list);
		}
		return $cate_list;
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