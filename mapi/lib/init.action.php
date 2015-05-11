<?php
class init{
	public function index()
	{
		$cur_city_id = intval($GLOBALS['request']['cur_city_id']);
		if ($cur_city_id == 0){
			$deal_city = get_current_deal_city();//默认城市id
            //print_r($deal_city); exit;
			$cur_city_id = $deal_city['id'];
			$city_name = $deal_city['name'];
		}
		else
		{
			$city_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city where id = ".$cur_city_id);
		}

		$root = array();
		$root['return'] = 1;


		$root['city_id'] = $cur_city_id;
		$root['city_name'] = $city_name;
		$root['catalog_id'] = intval($GLOBALS['m_config']['catalog_id']);//团购,优惠券默认分类id
		$root['catalog_id_name'] = $GLOBALS['m_config']['catalog_id_name'];

		$root['shop_cate_id'] = intval($GLOBALS['m_config']['shop_cate_id']);//商城默认分类id
		$root['shop_cate_id_name'] = $GLOBALS['m_config']['shop_cate_id_name'];
		
		$root['event_cate_id'] = intval($GLOBALS['m_config']['event_cate_id']);//活动默认分类id
		$root['event_cate_id_name'] = $GLOBALS['m_config']['event_cate_id_name'];
		
		$root['citylist'] = getCityArray();
		//$root['cataloglist'] = getCatalogArray();
		//$root['cataloglistsearch'] = getCatalogArraySearch();

		$root['region_version'] = intval($GLOBALS['m_config']['region_version']);//当前配送地区的数据版本(如果大于客户端的版本号,则客户端在选择，配送地区时会提示升级),int 数字类型
		$root['only_one_delivery'] = intval($GLOBALS['m_config']['only_one_delivery']);//1：会员只有一个配送地址；0：会员可以有多个配送地址

		$root['kf_phone'] = $GLOBALS['m_config']['kf_phone'];//客服电话
		$root['kf_email'] = $GLOBALS['m_config']['kf_email'];//客服邮箱
		$root['about_info'] = $GLOBALS['m_config']['about_info'];
		$root['version'] = VERSION; //接口版本号int
		$root['page_size'] = PAGE_SIZE;//默认分页大小
		$root['has_region'] = intval($GLOBALS['m_config']['has_region']);
		$root['newslist'] = $GLOBALS['m_config']['newslist'];
		$root['program_title'] = $GLOBALS['m_config']['program_title'];

		$root['addr_tlist'] = $GLOBALS['m_config']['addr_tlist'];//保存地址标题

		//$root['adv_youhui'] = m_adv_youhui($cur_city_id);
		$root['quanlist'] = getQuanArray($cur_city_id);//商圈列表
		$root['deal_cate_list'] = getDealCateArray();//优惠券分类
		$root['index_logo'] = get_abs_img_root($GLOBALS['m_config']['index_logo']);
		
		if(strim($GLOBALS['m_config']['sina_app_key'])!=""&&strim($GLOBALS['m_config']['sina_app_secret'])!="")
		{
			$root['api_sina'] = 1;
			$root['sina_app_key'] = $GLOBALS['m_config']['sina_app_key'];
			$root['sina_app_secret'] = $GLOBALS['m_config']['sina_app_secret'];
			$root['sina_bind_url'] = $GLOBALS['m_config']['sina_bind_url'];
		}
		if(strim($GLOBALS['m_config']['tencent_app_key'])!=""&&strim($GLOBALS['m_config']['tencent_app_secret'])!="")
		{
			$root['api_tencent'] = 1;
			$root['tencent_app_key'] = $GLOBALS['m_config']['tencent_app_key'];
			$root['tencent_app_secret'] = $GLOBALS['m_config']['tencent_app_secret'];
			$root['tencent_bind_url'] = $GLOBALS['m_config']['tencent_bind_url'];
		}

		output($root);
	}
}

function getCityArray(){
	$sql = "select id, name, pid, uname as py, null as image, 0 as has_child from ".DB_PREFIX."deal_city where is_delete = 0 and is_effect = 1 order by uname asc ";
	$list = $GLOBALS['db']->getAll($sql);
	return $list;
}

/**
 * tree_list
 * array("id"=>id,"name"=>name,"py"=>py,"has_childs"=>1,"child"=>array("id"=>id,"name"=>name.....))
 *
 * search_list
 * array("id"=>id,"name"=>name,"py"=>py,"has_childs"=>1)
*/

function getCatalogArray(){

	$tree_list = $GLOBALS['cache']->get("m_CATELIST");
	if($tree_list===false)
	{
		$sql = "select id,name,pid,uname as py,icon from ".DB_PREFIX."deal_cate";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
			$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate where pid = ".$v['id']));
			if($count>0)
			$list[$k]['has_child'] = 1;
			else
			$list[$k]['has_child'] = 0;
		}
		$tree_list = m_toTree($list,"id","pid","child");
		$GLOBALS['cache']->set("m_CATELIST",$tree_list);
	}

	return $tree_list;
}

function getCatalogArraySearch(){

	$list = $GLOBALS['cache']->get("m_CATELISTSEARCH");
	if($list === false)
	{
		$sql = "select id,name,pid,uname as py,icon from ".DB_PREFIX."deal_cate";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
			$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate where pid = ".$v['id']));
			if($count>0)
			{
				$list[$k]['has_child'] = 1;
				$child = new m_child("deal_cate");
				$ids = $child->getChildIds($v['id'], $pk_str='id' , $pid_str ='pid');
				$ids[] = 0;
				$child_list = $GLOBALS['db']->getAll( "select id,name,pid,uname as py  from ".DB_PREFIX."deal_cate where id in (".implode(",",$ids).")");
				foreach($child_list as $kk=>$vv)
				{
					$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate where pid = ".$vv['id']));
					if($count>0)
					$child_list[$kk]['has_child'] = 1;
					else
					$child_list[$kk]['has_child'] = 0;
				}

				$list[$k]['child'] = m_toTree($child_list,"id","pid","child");
			}
			else
			$list[$k]['has_child'] = 0;
		}
		$GLOBALS['cache']->set("m_CATELISTSEARCH",$list);
	}

	return $list;
}

function getQuanArray($city_id){
	
		$sql = "select id, name from ".DB_PREFIX."area where pid = 0 and city_id = ".intval($city_id)." order by sort desc ";
		//echo $sql; exit;
		$list = $GLOBALS['db']->getAll($sql);

	return $list;
}

function getDealCateArray(){
	//$land_list = FanweService::instance()->cache->loadCache("land_list");
		
		$sql = "select id, pid, name, icon from ".DB_PREFIX."deal_cate where pid = 0 and is_effect = 1 and is_delete = 0 order by sort desc ";
		//echo $sql; exit;
		$list = $GLOBALS['db']->getAll($sql);

	return $list;
}
?>