<?php
//指定商城父分类下的所有子分类并树状格式化后的结果
class cache_shop_cate_tree_auto_cache extends auto_cache{

	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);
		
		if($cate_list===false)
		{
			$pid = intval($param['pid']);
			$is_all = intval($param['is_all']);
			require_once APP_ROOT_PATH."system/utils/child.php";
			require_once APP_ROOT_PATH."system/utils/tree.php";
			$ids_util = new child("shop_cate");
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_effect = 1 and is_delete = 0 order by sort desc,id desc");
			foreach($cate_list as $k=>$v)
			{
				$sort=array('0'=>'sort','1'=>'id');
				$ids = $ids_util->getChildIds($v['pid'],"id","pid",$sort);
				$ids[] = $v['pid'];
				if($v['pid']>0&&!in_array($pid,$ids)&&$is_all==0)
				{
					unset($cate_list[$k]);
				}
				else
				{
					if($v['uname']!='')
					$curl = url("shop","cate",array("id"=>$v['uname']));
					else
					$curl = url("shop","cate",array("id"=>$v['id']));
					$cate_list[$k]['url'] = $curl;
					$sub_ids = $ids_util->getChildIds($v['pid'],"id","pid",$sort);
					$sub_ids[] = $v['id'];
					$cate_list[$k]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_delete = 0 and is_effect = 1 and buy_type <> 1 and shop_cate_id in (".implode(",",$sub_ids).")");
				}
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