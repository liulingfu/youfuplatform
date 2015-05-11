<?php
class index
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;
		$adv_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS_".intval($GLOBALS['city_id']));
		if($adv_list===false)
		{
					$advs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_adv where page = 'index' and city_id in (0,".intval($GLOBALS['city_id']).") and status = 1 order by sort desc ");
					$adv_list = array();
					foreach($advs as $k=>$v)
					{
						$adv_list[$k]['id'] = $v['id'];
						$adv_list[$k]['name'] = $v['name'];
						$adv_list[$k]['img'] = get_abs_img_root(get_spec_image($v['img'],640,240,1));
						$adv_list[$k]['type'] = $v['type'];
						$adv_list[$k]['data'] = $v['data'] = unserialize($v['data']);
						if($v['type'] == 1)
						{
							$tag_count = count($v['data']['tags']);
							$adv_list[$k]['data']['count'] = $tag_count;
						}
						
						if(in_array($v['type'],array(9,10,11,12,13))) //列表取分类ID
						{
							if($v['type']==9||$v['type']==12||$v['type']==13) //生活服务类
							{
								$adv_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".intval($v['data']['cate_id']));								
							}
							elseif($v['type']==10)  //商城
							{
								$adv_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".intval($v['data']['cate_id']));						
							}
							elseif($v['type']==11)  //活动
							{
								$adv_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".intval($v['data']['cate_id']));
							}
							$adv_list[$k]['data']['cate_name'] = $adv_list[$k]['data']['cate_name']?$adv_list[$k]['data']['cate_name']:"全部";
						}
					}
					$GLOBALS['cache']->set("MOBILE_INDEX_ADVS_".intval($GLOBALS['city_id']),$adv_list);
		}
		$root['advs'] = $adv_list;
		
		
		$indexs_list = $GLOBALS['cache']->get("MOBILE_INDEX_INDEX_".intval($GLOBALS['city_id']));
		if($indexs_list===false)
		{
					$indexs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_index where status = 1 and city_id in (0,".intval($GLOBALS['city_id']).") order by sort desc ");
					$indexs_list = array();
					foreach($indexs as $k=>$v)
					{
						$indexs_list[$k]['id'] = $v['id'];
						$indexs_list[$k]['name'] = $v['name'];
						$indexs_list[$k]['vice_name'] = $v['vice_name'];
						$indexs_list[$k]['desc'] = $v['desc'];
						$indexs_list[$k]['is_hot'] = $v['is_hot'];
						$indexs_list[$k]['is_new'] = $v['is_new'];
						$indexs_list[$k]['img'] = get_abs_img_root(get_spec_image($v['img'],640,120,1));
						$indexs_list[$k]['type'] = $v['type'];
						$indexs_list[$k]['data'] = $v['data'] = unserialize($v['data']);
						if($v['type'] == 1)
						{
							$tag_count = count($v['data']['tags']);
							$indexs_list[$k]['data']['count'] = $tag_count;
						}
						if(in_array($v['type'],array(9,10,11,12,13))) //列表取分类ID
						{
							if($v['type']==9||$v['type']==12||$v['type']==13) //生活服务类
							{
								$indexs_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".intval($v['data']['cate_id']));								
							}
							elseif($v['type']==10)  //商城
							{
								$indexs_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".intval($v['data']['cate_id']));						
							}
							elseif($v['type']==11)  //活动
							{
								$indexs_list[$k]['data']['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".intval($v['data']['cate_id']));
							}
							$indexs_list[$k]['data']['cate_name'] = $indexs_list[$k]['data']['cate_name']?$indexs_list[$k]['data']['cate_name']:"全部";
						}
					}
					$GLOBALS['cache']->set("MOBILE_INDEX_INDEX_".intval($GLOBALS['city_id']),$indexs_list);
		}
		$root['indexs'] = $indexs_list;
		
		
		output($root);
	}
}
?>