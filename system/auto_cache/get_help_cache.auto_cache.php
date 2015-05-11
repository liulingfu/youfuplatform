<?php
//帮助信息
class get_help_cache_auto_cache extends auto_cache
{

	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$help_list = $GLOBALS['fcache']->get($key);
		if($help_list===false)
		{
			$ids_util = new ChildIds("article_cate");
			$help_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."article_cate where type_id = 1 and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_CATE_LIMIT")));
			foreach($help_list as $k=>$v)
			{
				$ids = $GLOBALS['fcache']->get("CACHE_HELP_ARTICLE_CATE_".$v['id']);
				if($ids===false)
				{
					$ids = $ids_util->getChildIds($v['id']);
					$ids[] = $v['id'];
					$GLOBALS['fcache']->set("CACHE_HELP_ARTICLE_CATE_".$v['id'],$ids);
				}
				$help_cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."article where cate_id in (".implode(",",$ids).") and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_ITEM_LIMIT")));
				foreach($help_cate_list as $kk=>$vv)
				{
					if($vv['rel_url']!='')
					{
						if(!preg_match ("/http:\/\//i", $vv['rel_url']))
						{
							if(substr($vv['rel_url'],0,2)=='u:')
							{
								$help_cate_list[$kk]['url'] = parse_url_tag($vv['rel_url']);
							}
							else
							$help_cate_list[$kk]['url'] = APP_ROOT."/".$vv['rel_url'];
						}
						else
						$help_cate_list[$kk]['url'] = $vv['rel_url'];
						
						$help_cate_list[$kk]['new'] = 1;
					}
					else
					{
						if($vv['uname']!='')
						$hurl = url("shop","help",array("id"=>$vv['uname']));
						else
						$hurl = url("shop","help",array("id"=>$vv['id']));
						$help_cate_list[$kk]['url'] = $hurl;
					}
				}
				$help_list[$k]['help_list'] = $help_cate_list;
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$help_list);	
		}
		return $help_list;
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