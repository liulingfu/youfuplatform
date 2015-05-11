<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/youhui_lib.php';
class indexModule extends ShopBaseModule
{
	public function index()
	{		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('index.html', $cache_id))
		{		
			make_deal_cate_js();
			make_deal_region_js();	
			

			$result = load_auto_cache("store_filter_nav_cache",array('city_id'=>$GLOBALS['deal_city']['id']));
			
			$GLOBALS['tmpl']->assign("cate_list",$result['cate_list']);
			$GLOBALS['tmpl']->assign("area_list",$result['area_list']);
			
			//输出公告
			$notice_list = get_notice(0,array(0,1));
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);			

			//获取推荐的大分类
			$bcate_list = load_dynamic_cache("INDEX_RECOMMEND_BCATE");
			if($bcate_list===false)
			{
				$bcate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 and recommend = 1 order by sort desc");
				set_dynamic_cache("INDEX_RECOMMEND_BCATE",$bcate_list);
			}
			
			$conf_left = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."conf where name in ('INDEX_LEFT_STORE','INDEX_LEFT_TUAN','INDEX_LEFT_YOUHUI','INDEX_LEFT_DAIJIN','INDEX_LEFT_EVENT') and `value`>0 order by -`value` asc");


			$left_html = "";
			foreach($conf_left as $row)
			{
				$func_name = strtolower($row['name']);
				$left_html.= call_user_method($func_name,$this);
			}
			$GLOBALS['tmpl']->assign("left_html",$left_html);
			
			//关于活跃小组
			$hot_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group where is_effect = 1 order by topic_count desc,user_count desc limit 4");
			$GLOBALS['tmpl']->assign("hot_group",$hot_group);	
			
			
			$conf_right = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."conf where name in ('INDEX_RIGHT_STORE','INDEX_RIGHT_TUAN','INDEX_RIGHT_YOUHUI','INDEX_RIGHT_DAIJIN','INDEX_RIGHT_EVENT') and `value`>0 order by -`value` asc");
			$right_html = "";
			foreach($conf_right as $row)
			{
				$func_name = strtolower($row['name']);
				$right_html.= call_user_method($func_name,$this);
			}
			$GLOBALS['tmpl']->assign("right_html",$right_html);
			
			
			//输出最新的点评
			$dp_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_dp where status = 1 order by create_time desc limit 50");
			foreach($dp_list as $k=>$v)
			{
				$dp_list[$k]['sp_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier_location where id = ".$v['supplier_location_id']);
			}
			$GLOBALS['tmpl']->assign("dp_list",$dp_list);
			$right_dp_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/index_dp_list.html"));
			$GLOBALS['tmpl']->assign("right_dp_html",$right_dp_html);
			
			//开始输出友情链接
			$f_link_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link_group where is_effect = 1 order by sort desc");
			foreach($f_link_group as $k=>$v)
			{
				$g_links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1 and group_id = ".$v['id']." order by sort desc");
				if($g_links)
				{
					foreach($g_links as $kk=>$vv)
					{
						if(substr($vv['url'],0,7)=='http://')
						{
							$g_links[$kk]['url'] = str_replace("http://","",$vv['url']);
						}
					}
					$f_link_group[$k]['links'] = $g_links;
				}
				else
				unset($f_link_group[$k]);
			}
			
			$now = get_gmtime();
			$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
			$GLOBALS['tmpl']->assign("vote",$vote);
			$GLOBALS['tmpl']->assign("f_link_data",$f_link_group);
		}
		$GLOBALS['tmpl']->display("index.html",$cache_id);
	}
	
	
	//左侧模块
	public function index_left_store()
	{
		$city_ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>intval($GLOBALS['deal_city']['id'])));
		
		if($city_ids)
		$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location  use index (search_idx1, is_verify) WHERE is_recommend=1 AND city_id in(".implode(",",$city_ids).") and is_effect = 1 order by is_verify desc,sort desc limit 0,".app_conf("INDEX_SUPPLIER_COUNT"));
		else
		$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location  use index (search_idx1, is_verify) WHERE is_recommend=1 AND is_effect = 1 order by is_verify desc,sort desc limit 0,".app_conf("INDEX_SUPPLIER_COUNT"));
		
		
		$bcate_list = load_dynamic_cache("INDEX_RECOMMEND_BCATE");
		$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);
		$GLOBALS['tmpl']->assign("store_list",$store_list);
		return $GLOBALS['tmpl']->fetch("index/index_left_store.html");
	}
	public function index_left_tuan()
	{
		$tuan_list = get_deal_list_show(0,0,$GLOBALS['deal_city']['id'],array(DEAL_ONLINE)," is_recommend = 1 ");
		$tuan_list =$tuan_list['list'];
		$GLOBALS['tmpl']->assign("tuan_list",$tuan_list);
		$bcate_list = load_dynamic_cache("INDEX_RECOMMEND_BCATE");
		$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);
		return $GLOBALS['tmpl']->fetch("index/index_left_tuan.html");
	}
	public function index_left_youhui()
	{
		$youhui_list = get_free_youhui_list(0,0," is_recommend = 1 ","");
		$youhui_list = $youhui_list['list'];
		$GLOBALS['tmpl']->assign("youhui_list",$youhui_list);		
		$bcate_list = load_dynamic_cache("INDEX_RECOMMEND_BCATE");
		$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);
		return $GLOBALS['tmpl']->fetch("index/index_left_youhui.html");
	}
	public function index_left_daijin()
	{
		$result = search_youhui_list(0,0," d.is_recommend = 1 ","",false,"",$GLOBALS['deal_city']['id']);
		$daijin_list = $result['list'];
		$GLOBALS['tmpl']->assign("daijin_list",$daijin_list);
		$bcate_list = load_dynamic_cache("INDEX_RECOMMEND_BCATE");
		$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);
		return $GLOBALS['tmpl']->fetch("index/index_left_daijin.html");
	}
	public function index_left_event()
	{
		$bcate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_cate where is_effect = 1 order by sort desc limit 8");
		$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);		
		$event_list  = search_event_list(0,0,$GLOBALS['deal_city']['id'],"is_recommend = 1 ");
		$event_list = $event_list['list'];
		$GLOBALS['tmpl']->assign("event_list",$event_list);
		return $GLOBALS['tmpl']->fetch("index/index_left_event.html");
	}
	
	
	//右侧模块
	public function index_right_store()
	{
		$city_ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>intval($GLOBALS['deal_city']['id'])));		
		if($city_ids)
		$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location  use index(avg_point) WHERE  city_id in(".implode(",",$city_ids).") and is_effect = 1 order by dp_count desc,avg_point desc limit 5");
		else
		$store_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location  use index(avg_point) WHERE  is_effect = 1 order by dp_count desc,avg_point desc limit 5");
		
		$GLOBALS['tmpl']->assign("store_list",$store_list);
		return $GLOBALS['tmpl']->fetch("index/index_right_store.html");
	}
	public function index_right_tuan()
	{
		$tuan_list = get_deal_list_show(3,0,$GLOBALS['deal_city']['id'],array(DEAL_ONLINE),"");
		$tuan_list =$tuan_list['list'];
		$GLOBALS['tmpl']->assign("tuan_list",$tuan_list);
		return $GLOBALS['tmpl']->fetch("index/index_right_tuan.html");
	}
	public function index_right_youhui()
	{
		$youhui_list = get_free_youhui_list(5,0,""," view_count desc ");
		$youhui_list = $youhui_list['list'];
		$GLOBALS['tmpl']->assign("youhui_list",$youhui_list);		
		return $GLOBALS['tmpl']->fetch("index/index_right_youhui.html");
	}
	public function index_right_daijin()
	{
		$result = search_youhui_list(5,0,"","",false,"",$GLOBALS['deal_city']['id']);
		$daijin_list = $result['list'];
		$GLOBALS['tmpl']->assign("daijin_list",$daijin_list);
		return $GLOBALS['tmpl']->fetch("index/index_right_daijin.html");
	}
	public function index_right_event()
	{
		$event_list  = search_event_list(5,0,$GLOBALS['deal_city']['id'],""," submit_count desc ");
		$event_list = $event_list['list'];
		$GLOBALS['tmpl']->assign("event_list",$event_list);
		return $GLOBALS['tmpl']->fetch("index/index_right_event.html");
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