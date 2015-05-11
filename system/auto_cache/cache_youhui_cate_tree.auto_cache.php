<?php
//指定生活服务父分类下的所有子分类并树状格式化后的结果，链接为代金券
class cache_youhui_cate_tree_auto_cache extends auto_cache{

	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);
		if($cate_list===false)
		{
			$pid = intval($param['pid']);
			$extwhere = $param['extwhere'];
			require_once APP_ROOT_PATH."system/utils/child.php";
			require_once APP_ROOT_PATH."system/utils/tree.php";
			$ids_util = new child("deal_cate");
			$ids = $ids_util->getChildIds($pid);
			$ids[] = $pid;
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 and id in (".implode(",",$ids).") $extwhere order by sort desc");
			foreach($cate_list as $k=>$v)
			{
				if($v['uname']!='')
				$curl = url("youhui","ycate",array("id"=>$v['uname']));
				else
				$curl = url("youhui","ycate",array("id"=>$v['id']));
				$cate_list[$k]['url'] = $curl;
				$sub_ids = $ids_util->getChildIds($v['id']);
				$sub_ids[] = $v['id'];
				$cate_list[$k]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and is_shop = 2 and cate_id in (".implode(",",$sub_ids).")");
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