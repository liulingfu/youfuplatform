<?php
//所有生活服务分类的缓存
class cache_deal_cate_type_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);				
		if($cate_list === false)
		{		
			$cate_id = intval($param['cate_id']);
			if($cate_id>0)
			$cate_list_rs = $GLOBALS['db']->getAll("select dct.* from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$cate_id);
			else
			$cate_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type");
			foreach($cate_list_rs as $k=>$v)
			{
				$cate_list[$v['id']] = $v;
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