<?php
//优惠分类列表团购频道右侧的热卖
class recommend_hot_sale_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
	 	$res = $GLOBALS['fcache']->get($key);
		if($res===false)
		{
			$res = get_deal_list(app_conf("SIDE_DEAL_COUNT"),0,0,array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE),"","buy_count desc");
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$res);
		}
		return $res;
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