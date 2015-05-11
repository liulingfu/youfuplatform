<?php
class searchcate
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;

		$cate_list = $GLOBALS['cache']->get("MOBILE_SEARCHCATE_CATELIST");
		if($cate_list === false)
		{
			//取出标签分类
			$cate_list_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag_cate where showin_mobile = 1 order by sort desc");
			$cate_list = array();
			foreach($cate_list_data as $k=>$v)
			{
				$cate_list[$k]['id'] = $v['id'];
				$cate_list[$k]['name'] = $v['name'];
				$cate_list[$k]['bg'] = get_abs_img_root($v['mobile_title_bg']);
				

				
				//查询分类下的标签
				$txt_tags_data = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag as t left join ".DB_PREFIX."topic_tag_cate_link as l on l.tag_id = t.id where l.cate_id =".$v['id']." order by t.sort desc limit 12");
				$txt_tags = array();
				foreach($txt_tags_data as $kk=>$vv)
				{
					$txt_tags[$kk]['tag_name'] = $vv['name'];
					$txt_tags[$kk]['color'] = $vv['color'];
				}
				$cate_list[$k]['tags'] = $txt_tags;
			}
			$GLOBALS['cache']->set("MOBILE_SEARCHCATE_CATELIST",$cate_list,CACHE_TIME);
		}
		$root['item'] = $cate_list;
		
		output($root);
	}
}
?>