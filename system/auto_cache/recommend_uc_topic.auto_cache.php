<?php
//会员中心右侧的推荐分享
class recommend_uc_topic_auto_cache extends auto_cache
{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$recommend_topic = $GLOBALS['fcache']->get($key);
		if($recommend_topic===false)
		{
			$recommend_topic = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where is_recommend = 1 order by create_time desc limit 3");
			foreach($recommend_topic as $k=>$item)
			{
				$recommend_topic[$k] = get_topic_item($item);
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$recommend_topic);
		}
		return $recommend_topic;
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