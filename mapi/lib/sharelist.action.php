<?php
//接口名: sharelist
//参数: 
//cid: 标签分类ID
//tag: 查询的标签
//is_hot: 最热:1
//is_new: 最新:1
//page: 当前分页数
class sharelist
{
	public function index()
	{
		
		
		$cid = intval($GLOBALS['request']['cid']);
		$keyword = addslashes($GLOBALS['request']['tag']);
		$is_hot = intval($GLOBALS['request']['is_hot']);
		$is_new = intval($GLOBALS['request']['is_new']);
		$page = intval($GLOBALS['request']['page'])>0?intval($GLOBALS['request']['page']):1;

		
			$page_size = 20;
		
			$limit = ($page-1)*$page_size.",".$page_size;
			
			$root = array();
			$root['return'] = 1;
			$root['tag'] = $tag;
			$root['cid'] = $cid;
			
			$condition = " 1 = 1 ";
			$sort = "";
			if($is_hot>0)
			{
				$condition .= " and t.is_recommend = 1 and t.has_image = 1 ";
				$sort .= " order by  t.click_count desc,t.id desc  ";
			}
			
			if($is_new>0)
			{
				$condition .= " and t.has_image = 1 ";
				$sort .= " order by t.create_time desc,t.id desc  ";
			}
			if($cid>0)
			{
				$condition .=" and l.cate_id = ".$cid;
			}
			
			if($keyword)
			{			
				$kws_div = div_str($keyword);
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$ukeyword = implode(" ",$kw);
				$condition.=" and match(t.keyword_match) against('".$ukeyword."'  IN BOOLEAN MODE) ";
			}
			
			$sql = "select distinct(t.id) from ".DB_PREFIX."topic as t left join ".DB_PREFIX."topic_cate_link as l on l.topic_id = t.id where ".$condition.$sort." limit ".$limit;
			$sql_total = "select count(distinct(t.id)) from ".DB_PREFIX."topic as t left join ".DB_PREFIX."topic_cate_link as l on l.topic_id = t.id where ".$condition;
			
			
			$total = $GLOBALS['db']->getOne($sql_total);		
			$result = $GLOBALS['db']->getAll($sql);
	
			
			$share_list =array();
			foreach($result as $k=>$v)
			{
				$share_list[$k]['share_id'] = $v['id'];
				$image = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_image where topic_id = ".$v['id']." limit 1");
				if($image['o_path'])
					$share_list[$k]['img'] = get_abs_img_root(get_spec_image($image['o_path'],200,0,0));
				else	{
					unset($share_list[$k]);
				}
				$share_list[$k]['height'] = floor($image['height'] * (200 / $image['width']));			
			}
			$root['item'] = array_values($share_list);
			
			//分页
			$page_info['page'] = $page;
			$page_info['page_total'] = ceil($total/$page_size);
			$root['page'] = $page_info;
			
			//广告
			if($page==1)
			{
				$adv_list = $GLOBALS['cache']->get("MOBILE_SHARELIST_ADVS_".intval($GLOBALS['city_id']));
				if($adv_list===false)
				{
					$advs = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."m_adv where page = 'sharelist' and city_id in (0,".intval($GLOBALS['city_id']).") and status = 1 order by sort desc ");
					$adv_list = array();
					foreach($advs as $k=>$v)
					{
						$adv_list[$k]['id'] = $v['id'];
						$adv_list[$k]['name'] = $v['name'];
						$adv_list[$k]['img'] = get_abs_img_root(get_spec_image($v['img'],640,100,1));
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
					$GLOBALS['cache']->set("MOBILE_SHARELIST_ADVS_".intval($GLOBALS['city_id']),$adv_list);
				}
				$root['advs'] = $adv_list;
			}

		output($root);
		
	}
}
?>