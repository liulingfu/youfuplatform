<?php
//商城分类
class cache_shop_cate_auto_cache extends auto_cache{
	public function load($param)
	{
		static $cate_list;
		if($cate_list)return $cate_list;		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);				
		if($cate_list === false)
		{		
			$cate_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_effect = 1 and is_delete = 0");
			foreach($cate_list_rs as $k=>$v)
			{
				
				//输出筛选
				$ids = load_auto_cache("shop_sub_parent_cate_ids",array("cate_id"=>intval($v['id'])));
				$brand_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."brand where shop_cate_id in (".implode(",",$ids).")");
				$brand_list[]	=	array("name"=>$GLOBALS['lang']['ALL'],"id"=>0);
				$v['brand_list'] = $brand_list;
				
				$filter_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."filter_group where is_effect = 1 and cate_id in (".implode(",",$ids).") order by sort desc");
				foreach($filter_group as $kk=>$vv)
				{
					$filter_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."filter where filter_group_id = ".$vv['id']." limit 20");
					$filter_list[]	=	array("name"=>$GLOBALS['lang']['ALL'],"id"=>0);					
					$filter_group[$kk]['filter_list'] = $filter_list;
				}
				$v['filter_group'] = $filter_group;
				
				$cate_list[$v['id']] = $v;
				if($v['uname']!="")
				$cate_list[$v['uname']] = $v;
			}
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