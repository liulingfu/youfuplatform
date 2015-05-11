<?php
class sharecate
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;

		$cate_list = $GLOBALS['cache']->get("MOBILE_SHARECATE_CATELIST");
		if($cate_list === false)
		{
			//取出标签分类
			$cate_list_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag_cate where showin_mobile = 1 order by sort desc");
			$cate_list = array();
			foreach($cate_list_data as $k=>$v)
			{
				$cate_list[$k]['cate_id'] = $v['id'];
				$cate_list[$k]['cate_name'] = $v['name'];
				$cate_list[$k]['desc'] = $v['sub_name']==''?$v['name']:$v['sub_name'];
				$cate_list[$k]['share_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic as t left join ".DB_PREFIX."topic_cate_link as l on l.topic_id = t.id where l.cate_id =".$v['id']);
				
				//查询分类下的分享
				$img_tags_data = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic as t left join ".DB_PREFIX."topic_cate_link as l on l.topic_id = t.id where l.cate_id =".$v['id']." and is_recommend = 1 and has_image = 1 order by t.create_time desc limit 5");
				$img_tags = array();
				foreach($img_tags_data as $kk=>$vv)
				{
					$img_tags[$kk]['share_id'] = $vv['id'];
					$topic_tags = explode(" ",$vv['tags']);
					$img_tags[$kk]['tag_name'] = trim($topic_tags[0]);
					$image = $GLOBALS['db']->getOne("select o_path from ".DB_PREFIX."topic_image where topic_id = ".$vv['id']." limit 1");
					if($kk==0)
					$img_tags[$kk]['img'] = get_abs_img_root(get_spec_image($image,320,320,1));
					else
					$img_tags[$kk]['img'] = get_abs_img_root(get_spec_image($image,160,160,1));
				}
				
				$cate_list[$k]['img_tags'] = $img_tags;
				
				//查询分类下的标签
				$txt_tags_data = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag as t left join ".DB_PREFIX."topic_tag_cate_link as l on l.tag_id = t.id where l.cate_id =".$v['id']." order by t.sort desc limit 11");
				$txt_tags = array();
				foreach($txt_tags_data as $kk=>$vv)
				{
					$txt_tags[$kk]['tag_name'] = $vv['name'];
					$txt_tags[$kk]['color'] = $vv['color'];
				}
				$cate_list[$k]['txt_tags'] = $txt_tags;
			}
			$GLOBALS['cache']->set("MOBILE_SHARECATE_CATELIST",$cate_list,CACHE_TIME);
		}
		$root['item'] = $cate_list;
		
		output($root);
	}
}
?>