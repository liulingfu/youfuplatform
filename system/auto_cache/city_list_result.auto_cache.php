<?php
//城市列表
class city_list_result_auto_cache extends auto_cache{

	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$city_list_result = $GLOBALS['fcache']->get($key);
		if($city_list_result === false)
		{
			$city_list = $GLOBALS['db']->getAll("select id,name,uname,is_open,left(uname,1) as zm from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0 and pid > 0  order by left(uname,1) asc,sort desc");
			$city_zm_list = array();
			foreach($city_list as $k=>$v)
			{						
				$v['url'] = url("index","index",array("city"=>$v['uname']));
				$v['zm'] = strtoupper($v['zm']);
				$city_zm_list[strtoupper($v['zm'])][] = $v;
				$city_list[$v['id']]= $v;
				unset($city_list[$k]);
			}
			$city_list_result = array('zm'=>$city_zm_list,'ls'=>$city_list);
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$city_list_result);
		}
		return $city_list_result;		
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