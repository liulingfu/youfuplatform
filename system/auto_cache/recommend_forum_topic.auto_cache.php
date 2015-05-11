<?php
//小组的推荐
class recommend_forum_topic_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$result = $GLOBALS['fcache']->get($key);
		if($result===false)
		{
			$result['image'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where group_id <> 0 and is_effect = 1 and is_delete = 0 and is_recommend = 1 and has_image = 1 order by create_time desc limit 4");
			$ids = array(0);
			foreach($result['image'] as $k=>$v)
			{
				$result['image'][$k] = get_topic_item($v);
				$result['image'][$k]['group_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$v['group_id']);
				$ids[] = $v['id'];
			}			
			$result['list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where group_id <> 0 and is_effect = 1 and is_delete = 0 and is_recommend = 1 and id not in(".implode(",",$ids).") order by create_time desc limit 8");	
			foreach($result['list'] as $k=>$v)
			{
				$result['list'][$k] = get_topic_item($v);
				$result['list'][$k]['group_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$v['group_id']);
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$result);
		}	

		return $result;
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