<?php
//某个地区支持的配送方式列表
class cache_support_delivery_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$support_delivery_list = $GLOBALS['fcache']->get($key);
		if($support_delivery_list===false)
		{
			$region_id = intval($param['region_id']);
			$support_delivery_list = array();
			$delivery_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery where is_effect = 1 order by sort desc");
				
			foreach($delivery_list as $k=>$v)
			{
				//读取相应的支持地区
				$delivery_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_fee where delivery_id = ".$v['id']);
				if($delivery_items)
				{
					foreach($delivery_items as $kk=>$vv)
					{
						$sp_ids = $vv['region_ids']; //每条支持地区值
						$sp_ids = explode(",",$sp_ids);
						if(in_array($region_id,$sp_ids)||$v['allow_default'] == 1)
						{				
							$support_delivery_list[] = $v;
							break;
						}	
					}
				}
				else
				{
					if($v['allow_default']==1)
					$support_delivery_list[] = $v;
				}				
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$support_delivery_list);
		}	
		return $support_delivery_list;
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