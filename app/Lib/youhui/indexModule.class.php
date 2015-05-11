<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';

class indexModule extends YouhuiBaseModule
{
	public function index()
	{		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //优惠首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('youhui_index.html', $cache_id))
		{		
			make_deal_cate_js();
			make_deal_region_js();	

			$result = load_auto_cache("fyouhui_filter_nav_cache",array('city_id'=>$GLOBALS['deal_city']['id']));
			$GLOBALS['tmpl']->assign("cate_list",$result['cate_list']);
			$GLOBALS['tmpl']->assign("area_list",$result['area_list']);
			
			//输出右侧的优惠列表
			$youhui_list = get_free_youhui_list(5,0,""," view_count desc ");
			$youhui_list = $youhui_list['list'];
			$GLOBALS['tmpl']->assign("youhui_list",$youhui_list);		
			$right_youhui_html = $GLOBALS['tmpl']->fetch("index/index_right_youhui.html");
			$GLOBALS['tmpl']->assign("right_youhui_html",$right_youhui_html);		
			
			//输出右侧商家
			$city_ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>intval($GLOBALS['deal_city']['id'])));
			if($city_ids)
			$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location use index(avg_point) WHERE  city_id in(".implode(",",$city_ids).") and is_effect = 1 order by avg_point desc limit 5");
			else 
			$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location use index(avg_point) WHERE  is_effect = 1 order by avg_point desc limit 5");
			
			$GLOBALS['tmpl']->assign("store_list",$store_list);
			$right_store_html = $GLOBALS['tmpl']->fetch("index/index_right_store.html");
			$GLOBALS['tmpl']->assign("right_store_html",$right_store_html);	
			
			//输出左侧推荐分类
			$recommend_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where rec_youhui = 1 order by sort desc");		
			$recommend_cate_html = "";
			foreach($recommend_cate as $cate)
			{				
				$youhui_list = get_free_youhui_list(8,$cate['id'],"","");
				$youhui_list = $youhui_list['list'];
				$GLOBALS['tmpl']->assign("youhui_list",$youhui_list);	
				
				$scate_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where t.is_recommend = 1 and l.cate_id = ".$cate['id']." order by sort desc");
				$GLOBALS['tmpl']->assign("scate_list", $scate_list);
				$GLOBALS['tmpl']->assign("bcate_item", $cate);
				$recommend_cate_html.=$GLOBALS['tmpl']->fetch("inc/recommend_cate_youhui.html");
			}				
			$GLOBALS['tmpl']->assign("recommend_cate_html",$recommend_cate_html);	

			//输出下载的动态
			$down_load_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_msg_list where is_youhui = 1 and youhui_id <> 0 order by create_time desc limit 50");		
			foreach($down_load_list as $k=>$v)
			{
				$down_load_list[$k]['youhui_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."youhui where id = ".intval($v['youhui_id']));
			}
			$GLOBALS['tmpl']->assign("down_load_list",$down_load_list);	
			
			
		}
		$GLOBALS['tmpl']->display("youhui_index.html",$cache_id);
	}
	
	
	

	public function daijin_index()
	{		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //代金券首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('daijin_index.html', $cache_id))
		{		
			make_deal_cate_js();
			make_deal_region_js();	

			$result = load_auto_cache("byouhui_filter_nav_cache",array('city_id'=>$GLOBALS['deal_city']['id']));
			$GLOBALS['tmpl']->assign("cate_list",$result['cate_list']);
			$GLOBALS['tmpl']->assign("area_list",$result['area_list']);
			
			//输出右侧的优惠列表
			$result = search_youhui_list(5,0,"","",false,"",$GLOBALS['deal_city']['id']);
			$daijin_list = $result['list'];
			$GLOBALS['tmpl']->assign("daijin_list",$daijin_list);
			$right_daijin_html = $GLOBALS['tmpl']->fetch("index/index_right_daijin.html");	
			$GLOBALS['tmpl']->assign("right_daijin_html",$right_daijin_html);
			
			//输出右侧商家
			$city_ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>intval($GLOBALS['deal_city']['id'])));
			if($city_ids)
			$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location use index(avg_point) WHERE  city_id in(".implode(",",$city_ids).") and is_effect = 1 order by avg_point desc limit 5");
			else 
			$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location use index(avg_point) WHERE  is_effect = 1 order by avg_point desc limit 5");
			
			$GLOBALS['tmpl']->assign("store_list",$store_list);
			$right_store_html = $GLOBALS['tmpl']->fetch("index/index_right_store.html");
			$GLOBALS['tmpl']->assign("right_store_html",$right_store_html);	
			
			//输出左侧推荐分类
			$recommend_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where rec_daijin = 1 order by sort desc");		
			$recommend_cate_html = "";
			foreach($recommend_cate as $cate)
			{				
				$daijin_list = search_youhui_list(8,$cate['id'],"","",false,"",$GLOBALS['deal_city']['id']);
				$daijin_list = $daijin_list['list'];
				$GLOBALS['tmpl']->assign("daijin_list",$daijin_list);	
				
				$scate_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where t.is_recommend = 1 and l.cate_id = ".$cate['id']." order by sort desc");
				$GLOBALS['tmpl']->assign("scate_list", $scate_list);
				$GLOBALS['tmpl']->assign("bcate_item", $cate);
				$recommend_cate_html.=$GLOBALS['tmpl']->fetch("inc/recommend_cate_daijin.html");
			}				
			$GLOBALS['tmpl']->assign("recommend_cate_html",$recommend_cate_html);	

			
			
		}
		$GLOBALS['tmpl']->display("daijin_index.html",$cache_id);
	}
	
	
	/**
	 * 输出推荐达人
	 */
	function load_index_daren_list(){
		$rnd_daren_list = get_rand_user(20,1);			
		$GLOBALS['tmpl']->assign("rnd_daren_list",$rnd_daren_list);
		return $GLOBALS['tmpl']->fetch("index/index_daren_list.html");	
	}
}
?>