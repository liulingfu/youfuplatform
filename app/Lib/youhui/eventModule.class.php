<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH.'app/Lib/page.php';
class eventModule extends YouhuiBaseModule
{
	public function index()
	{		
		convert_req($_REQUEST);	
		$_REQUEST['cid'] = intval($_REQUEST['cid']);
		$keyword = addslashes(htmlspecialchars(trim($_REQUEST['keyword'])));
		$GLOBALS['tmpl']->assign("keyword",$keyword);	
		
		$url_param = array(
				"cid"	=> addslashes(trim($_REQUEST['cid'])),
				"aid"	=>	intval($_REQUEST['aid']),
				"qid"	=>	intval($_REQUEST['qid']),
				"keyword"	=> $keyword
			);			
		if(intval($_REQUEST['is_redirect'])==1)
		{
			app_redirect(url("youhui","event",$url_param));
		}

					
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['YOUHUI_EVENT'],'url'=>url("youhui","event#index"));
							
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		//输出当前的site_nav
				
		//输出热卖
		
		
		$seo_title = $GLOBALS['lang']['YOUHUI_EVENT'];
		$seo_keyword = $GLOBALS['lang']['YOUHUI_EVENT'];
		$seo_description = $GLOBALS['lang']['YOUHUI_EVENT'];
		
		//		
			$city_id = intval($GLOBALS['deal_city']['id']);
				$quan_id = intval($_REQUEST['qid']);
			//分页
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");	
				
			
			$id = intval($_REQUEST['cid']);
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_cate where id = ".$id);
					
			$condition = " 1=1 ";  //条件
					
			$tp_url_param = $url_param;
			unset($tp_url_param['keyword']);
			
			$sub_nav[] = array(
				"name"	=>	$GLOBALS['lang']['YOUHUI_EVENT'],	
				"url"	=> url("youhui","event",$tp_url_param),
				"current"	=>	1,
			);
			$GLOBALS['tmpl']->assign("sub_nav",$sub_nav); 
			
			$GLOBALS['tmpl']->assign("url_param",$tp_url_param); //将变量输出到模板
			
			
			$append_seo = "";
			
			//输出大区
			$area_id = intval($_REQUEST['aid']);	
			if($area_id>0)
			{
				$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area_id);			
				$append_seo.=$area_name;
				if($quan_id>0)
				{					
					$kw_unicode = str_to_unicode_string($area_name);
					//有筛选
					$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";						
				}
				else
				{
					$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
					$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
					$unicode_quans = array();
					foreach($quan_list as $k=>$v){
						$unicode_quans[] = str_to_unicode_string($v['name']);
					}
					$kw_unicode = implode(" ", $unicode_quans);
					$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
				}	
			}
			$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = 0 order by sort desc");
			$area_list[]	=	array("name"=>$GLOBALS['lang']['ALL'],"id"=>0);
			foreach($area_list as $k=>$v)
			{
				if($area_id==$v['id'])
				{
					$area_list[$k]['act'] = 1;
				}
				$tmp_url_param = $url_param;
				unset($tmp_url_param['qid']);
				$tmp_url_param['aid'] = $v['id'];
				$area_list[$k]['url'] = url("youhui","event",$tmp_url_param);	
			}		
			$GLOBALS['tmpl']->assign("area_list",$area_list);
			
			if($area_id>0)
			{
				//输出商圈
			
				if($quan_id>0)
				{
						$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);				
						$kw_unicode = str_to_unicode_string($area_name);
						//有筛选
						$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";	
						$append_seo = $append_seo.$area_name;			
				}
				
				$quan_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = ".$area_id." order by sort desc");
				$quan_list[]	=	array("name"=>$GLOBALS['lang']['ALL'],"id"=>0);
				foreach($quan_list as $k=>$v)
					{
						if($quan_id==$v['id'])
						{
							$quan_list[$k]['act'] = 1;
						}
						$tmp_url_param = $url_param;
						$tmp_url_param['qid'] = $v['id'];
						$quan_list[$k]['url'] = url("youhui","event",$tmp_url_param);	
				}		
				$GLOBALS['tmpl']->assign("quan_list",$quan_list);
			}
			
			//输出分类
			$cate_id = $cate_item['id'];	
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_cate where is_effect = 1 order by sort desc");
			$cate_list[]	=	array("name"=>$GLOBALS['lang']['ALL'],"cid"=>0);
			foreach($cate_list as $k=>$v)
			{
					if($cate_id==$v['id'])
					{
						$cate_list[$k]['act'] = 1;
					}
					$tmp_url_param = $url_param;
					$tmp_url_param['cid'] = $v['id'];
					$cate_list[$k]['url'] = url("youhui","event",$tmp_url_param);	
			}		
			$GLOBALS['tmpl']->assign("cate_list",$cate_list);	

			$deal_cate_id = $cate_item['id'];
			$deal_quan_id = $area_id;
			if($deal_cate_id>0)
			{
				$append_seo.=$cate_item['name'];
			}
			
	
			$sort_field = es_cookie::get("event_sort_field")?es_cookie::get("event_sort_field"):"sort";
			$sort_type = es_cookie::get("event_sort_type")?es_cookie::get("event_sort_type"):"desc";
			if($sort_field!="event_end_time"&&$sort_field!="submit_count"&&$sort_field!="sort")
			{
				$sort_field = "sort";
			}
			if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
			$GLOBALS['tmpl']->assign('sort_field',$sort_field);
			$GLOBALS['tmpl']->assign('sort_type',$sort_type);
			
			$sort_by = $sort_field." ".$sort_type;
			
			
			$seo_title = $append_seo.$seo_title;
		    $seo_keyword = $append_seo.$seo_keyword;
		    $seo_description = $append_seo.$seo_keyword;
			if($keyword)
			{				
					$kws_div = div_str($keyword);
					foreach($kws_div as $k=>$item)
					{
						$kw[$k] = str_to_unicode_string($item);
					}
					$ukeyword = implode(" ",$kw);
					$condition.=" and (match(name_match) against('".$ukeyword."'  IN BOOLEAN MODE)  or name like '%".$keyword."%') ";
					$seo_title = $keyword." - ".$seo_title;
			}
			
			$result = search_event_list($limit,intval($cate_item['id']),$city_id,$condition,$sort_by);
			
			
	
			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			$GLOBALS['tmpl']->assign("cate_id",$cate_item['id']);
		
		//
				
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword);
		$GLOBALS['tmpl']->assign("page_description",$seo_description);		

		$GLOBALS['tmpl']->display("youhui_event.html");
			
	}	
}
?>