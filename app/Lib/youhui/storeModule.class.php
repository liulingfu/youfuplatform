<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class storeModule extends YouhuiBaseModule
{
	public function index()
	{			

		convert_req($_REQUEST);	
		$_REQUEST['cid'] = intval($_REQUEST['cid']);
		$keyword = addslashes(htmlspecialchars(trim($_REQUEST['keyword'])));
		//获取当前页的团购商品列表
		require_once APP_ROOT_PATH.'app/Lib/page.php';
		$city_id = intval($GLOBALS['deal_city']['id']);
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");	
				
			
		$cate_list = load_auto_cache("cache_deal_cate");
		$cid = intval($_REQUEST['cid']);
		$uname = addslashes(trim($_REQUEST['cid']));
		//$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where id = ".$cid." or (uname = '".$uname."' and uname <> '')");

		$cate_item = $cate_list[$cid];
		
		$condition = " 1=1 ";  //条件
			
		$base_param = $url_param = array(
				"cid"	=> $_REQUEST['cid'],
				"aid"	=>	intval($_REQUEST['aid']),
				"tid"	=>	intval($_REQUEST['tid']),
				"qid"	=>	intval($_REQUEST['qid']),
				"keyword"	=>	$keyword,
				"minprice"	=>	$minprice = doubleval($_REQUEST['minprice'])>0?doubleval($_REQUEST['minprice']):0,
				"maxprice"	=>	$maxprice = doubleval($_REQUEST['maxprice'])>0?doubleval($_REQUEST['maxprice']):0
		);			
		//组装分组筛选
		if($_REQUEST['g']&&is_array($_REQUEST['g']))
		foreach ($_REQUEST['g'] as $k=>$v)
		{
			$url_param["g[".$k."]"] = addslashes(trim($v));
		}

		
		if(intval($_REQUEST['is_redirect'])==1)
		{
			app_redirect(url("youhui","store",$url_param));
		}
		$GLOBALS['tmpl']->assign("url_param",$url_param); //将变量输出到模板
			
		unset($url_param['keyword']);
		unset($url_param['minprice']);
		unset($url_param['maxprice']);
		$cache_param = $url_param;
		$cache_param['cid'] = $cate_item['id'];
		$cache_param['city_id'] = $city_id;
		$result = load_auto_cache("store_filter_nav_cache",$cache_param);
		
		
		$filter_url_param = $base_param;
		$filter_url_param['has_tuan'] = intval($_REQUEST['has_tuan']);
		$filter_url_param['has_daijin'] = intval($_REQUEST['has_daijin']);
		$filter_url_param['has_youhui'] = intval($_REQUEST['has_youhui']);
		$filter_url_param['has_event'] = intval($_REQUEST['has_event']);
		$filter_url_param['has_goods'] = intval($_REQUEST['has_goods']);
		$filter_url_param['is_verify'] = intval($_REQUEST['is_verify']);
		
		$GLOBALS['tmpl']->assign("filter_url_param",$filter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['has_tuan']==1)
		$tmpfilter_url_param['has_tuan'] = 0;
		else
		$tmpfilter_url_param['has_tuan'] = 1;
		$filter_url['has_tuan'] = url("youhui","store",$tmpfilter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['has_daijin']==1)
		$tmpfilter_url_param['has_daijin'] = 0;
		else
		$tmpfilter_url_param['has_daijin'] = 1;
		$filter_url['has_daijin'] = url("youhui","store",$tmpfilter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['has_youhui']==1)
		$tmpfilter_url_param['has_youhui'] = 0;
		else
		$tmpfilter_url_param['has_youhui'] = 1;
		$filter_url['has_youhui'] = url("youhui","store",$tmpfilter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['has_event']==1)
		$tmpfilter_url_param['has_event'] = 0;
		else
		$tmpfilter_url_param['has_event'] = 1;
		$filter_url['has_event'] = url("youhui","store",$tmpfilter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['has_goods']==1)
		$tmpfilter_url_param['has_goods'] = 0;
		else
		$tmpfilter_url_param['has_goods'] = 1;
		$filter_url['has_goods'] = url("youhui","store",$tmpfilter_url_param);
		
		$tmpfilter_url_param = $filter_url_param;
		if($filter_url_param['is_verify']==1)
		$tmpfilter_url_param['is_verify'] = 0;
		else
		$tmpfilter_url_param['is_verify'] = 1;
		$filter_url['is_verify'] = url("youhui","store",$tmpfilter_url_param);
		
		
		$GLOBALS['tmpl']->assign("filter_url",$filter_url);
		//输出大区	
		$seo_title = $GLOBALS['lang']['STORE_LIST'];
		$seo_keyword = $GLOBALS['lang']['STORE_LIST'];
		$seo_description = $GLOBALS['lang']['STORE_LIST'];
		$append_seo = "";
		$area_id = intval($_REQUEST['aid']);	
		$quan_id = intval($_REQUEST['qid']);
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['deal_city']['id']));
		if($area_id>0)
		{			
// 			$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area_id);	
			$area_name = $area_result[$area_id]['name'];
				$append_seo = $area_name;
			if($quan_id>0)
			{							
				$kw_unicode = str_to_unicode_string($area_name);
				//有筛选
				$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";					
			}
			else
			{
				$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
				//$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
				$unicode_quans = array();
				foreach($ids as $k=>$v){
					$unicode_quans[] = str_to_unicode_string($area_result[$v]['name']);
				}
				$kw_unicode = implode(" ", $unicode_quans);
				$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			}	
		}
		
		$GLOBALS['tmpl']->assign("area_list",$result['area_list']);
		
		if($area_id>0)
		{
			//输出商圈
	
			if($quan_id>0)
			{
// 					$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);				
					$area_name = $area_result[$quan_id]['name'];
					$kw_unicode = str_to_unicode_string($area_name);
					//有筛选
					$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";	
					$append_seo = $append_seo.$area_name;			
			}
			
			$GLOBALS['tmpl']->assign("quan_list",$result['quan_list']);
		}
		
		//输出分类
		$cate_id = $cate_item['id'];	
		
		$GLOBALS['tmpl']->assign("cate_list",$result['cate_list']);	
		
		//输出小分类
		$deal_type_id = intval($_REQUEST['tid']);	
		$deal_cate_id = $cate_id;
		if($deal_cate_id>0)
		{			
			$GLOBALS['tmpl']->assign("scate_list",$result['scate_list']);			
			$GLOBALS['tmpl']->assign("tag_group",$result['tag_group']);
			
			//$deal_cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$deal_cate_id);			
			$deal_cate_name  = $cate_list[$deal_cate_id]['name'];
			$deal_cate_name_unicode = str_to_unicode_string($deal_cate_name);
			$condition .= " and (match(deal_cate_match) against('".$deal_cate_name_unicode."' IN BOOLEAN MODE)) ";
			
			$cate_condition = " (match(deal_cate_match) against('".$deal_cate_name_unicode."' IN BOOLEAN MODE)) ";
			
			if($append_seo!="")
			$append_seo.=" - ";
			$append_seo.=$cate_item['name'];			
		}
			
		if($deal_type_id>0)
		{
			$type_list = load_auto_cache("cache_deal_cate_type",array("cate_id"=>$deal_cate_id));
			
// 			$deal_type_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate_type where id = ".$deal_type_id);
			$deal_type_name = $type_list[$deal_type_id]['name'];
			$deal_type_name_unicode = str_to_unicode_string($deal_type_name);
			$condition .= " and (match(deal_cate_match) against('".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
			$append_seo.=$deal_type_name;
		}
			
		$seo_title = $append_seo.$seo_title;
	    $seo_keyword = $append_seo.$seo_keyword;
	    $seo_description = $append_seo.$seo_keyword;	
		
	   $supplier_id = intval($_REQUEST['id']);
	  
	   if($supplier_id>0)
	   {
	   	   $supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
	   	   if($supplier_info)
	   	   {
	   	   	  $GLOBALS['tmpl']->assign("supplier_info",$supplier_info);
	   	   	  $condition.=" and supplier_id = ".$supplier_id;
	   	   	  
	   	   }
	   }
	   
	   $url_param['tag'] = addslashes($_REQUEST['tag']);
	   if($url_param['tag']){
	   		$kw_unicode = str_to_unicode_string($url_param['tag']);
			//有筛选
			$condition .=" and (match(tags_match) against('".$kw_unicode."' IN BOOLEAN MODE))";	
	   }
	   
	   if($_REQUEST['g']&&is_array($_REQUEST['g']))
	   {
	   		foreach($_REQUEST['g'] as $k=>$v)
	   		{
	   			if(trim($v)!="")
	   			{
		   			$kw_unicode = str_to_unicode_string($v);
		   			$condition .=" and (match(tags_match) against('".$kw_unicode."' IN BOOLEAN MODE))";	
	   			}
	   		}
	   }
	   
	   if($keyword){
	   		$GLOBALS['tmpl']->assign("keyword",$keyword);
	   		$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kw[$k] = str_to_unicode_string($item);
			}
			$kw_unicode = implode(" ",$kw);
			//有筛选
			$condition .=" and ((match(name_match,locate_match,deal_cate_match,tags_match) against('".$kw_unicode."' IN BOOLEAN MODE)) or name like '%".$keyword."%') ";	
	   }
	   
	   if($minprice>0)
	   {
	   		$condition .=" and ref_avg_price >= ".$minprice;
	   		$GLOBALS['tmpl']->assign("minprice",round($minprice));
	   }
	   
	 	if($maxprice>0)
	   {
	   		$condition .=" and ref_avg_price <= ".$maxprice;
	   		$GLOBALS['tmpl']->assign("maxprice",round($maxprice));
	   }
	
	   
		
		if($filter_url_param['has_tuan']>0)
		$condition.=" and tuan_count > 0 ";
		
		if($filter_url_param['has_daijin']>0)
		$condition.=" and daijin_count > 0 ";
		
		if($filter_url_param['has_youhui']>0)
		$condition.=" and youhui_count > 0 ";
		
		if($filter_url_param['has_event']>0)
		$condition.=" and event_count > 0 ";
		
		if($filter_url_param['has_goods']>0)
		$condition.=" and shop_count > 0 ";
		
		if($filter_url_param['is_verify']>0)
		$condition.=" and is_verify = 1 ";
		
		
	   	$sort_field = es_cookie::get("store_sort_field");
		$sort_type = es_cookie::get("store_sort_type");
		if($sort_type!="desc"&&$sort_type!="asc")$sort_type = "desc";
		
		if($sort_field!="default"&&$sort_field!="dp_count"&&$sort_field!="avg_point"&&$sort_field!="ref_avg_price")
		{
			$sort_field = "default";
		}
		if($sort_field=='default')
		{
			$sortby = " is_recommend desc,is_verify desc,dp_count desc ";
		}
		else
		{
			$sortby = $sort_field." ".$sort_type;
		}
		
		$GLOBALS['tmpl']->assign('sort_field',$sort_field);
		$GLOBALS['tmpl']->assign('sort_type',$sort_type);
			
	   $result = get_store_list($limit,0,$condition,$sortby,false);
		
	   $GLOBALS['tmpl']->assign("list",$result['list']);
	   $page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
	   $p  =  $page->show();
	   $GLOBALS['tmpl']->assign('pages',$p);
	   
		
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['STORE_LIST'],'url'=>url("youhui","store"));
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
		
		//输出最新加入的商家
		$new_stores = get_store_list(5,0,$cate_condition," id desc ",false,false);
		$GLOBALS['tmpl']->assign("new_stores",$new_stores['list']);
		if(trim($cate_condition)!='')
		$rec_stores = get_store_list(5,0, $cate_condition." and is_recommend = 1 "," id desc ",false,false);
		else
		$rec_stores = get_store_list(5,0, " is_recommend = 1 "," id desc ",false,false);
		$GLOBALS['tmpl']->assign("rec_stores",$rec_stores['list']);
			
		
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
	
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		
		$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		$GLOBALS['tmpl']->display("store_index.html");		
	}
	
	public function view()
	{
		$id = intval($_REQUEST['id']);
		
		if(check_ipop_limit(get_client_ip(),"recount_supplier_location",1200,$id))
		{
			recount_supplier_data_count($id,"tuan");
			recount_supplier_data_count($id,"youhui");
			recount_supplier_data_count($id,"daijin");
			recount_supplier_data_count($id,"event");
			recount_supplier_data_count($id,"shop");
		}
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$id);		
		if (!$GLOBALS['tmpl']->is_cached('store_view.html', $cache_id))	
		{	
			$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id." and is_effect = 1");
			if(!$store_info)
			{
				showErr($GLOBALS['lang']['NO_STORE_INFO']);
			}		
			
			$store_info['group_point'] = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."point_group as pg left join ".DB_PREFIX."point_group_link as pgl on pg.id = pgl.point_group_id  where pgl.category_id = ".$store_info['deal_cate_id']." order by sort asc" );
			foreach($store_info['group_point'] as $kk=>$vv)
			{
				$store_info['group_point'][$kk]['avg_point'] =  round(floatval($GLOBALS['db']->getOne("select avg_point from ".DB_PREFIX."supplier_location_point_result where supplier_location_id = ".$store_info['id']." and group_id = ".$vv['id'])),1);
			}
			
			
			$tags = array();
			if($store_info['tags']){
				$ttags = explode(" ",$store_info['tags']);
				foreach($ttags as $tv){
					$tags[$tv]['name'] =  $tv;
					$tags[$tv]['code'] = $tv;
				}
			}
			$store_info['tags_list'] = $tags;
			//标签分组
			$tag_group = $GLOBALS['db']->getAll("select g.id,g.name,g.allow_vote from ".DB_PREFIX."tag_group as g left join ".DB_PREFIX."tag_group_link as gl on g.id = gl.tag_group_id where gl.category_id = ".$store_info['deal_cate_id']." order by g.sort");
	
			foreach($tag_group as $k=>$v)
			{
				$tags = $GLOBALS['db']->getAll("select tag_name,total_count from ".DB_PREFIX."supplier_tag where group_id = ".$v['id']." and supplier_location_id =".$store_info['id']." order by total_count desc limit 30");
				$tags_arr = array();
				foreach($tags as $kk=>$vv)
				{
					$vv['tag_name'] = trim($vv['tag_name']);
					if($vv['tag_name']!='')
					{
						$tags_arr[$kk]['url'] = url("youhui","store#index",array("tag"=>$vv['tag_name']));
						$tags_arr[$kk]['name'] = $vv['tag_name'];
						$tags_arr[$kk]['total_count'] = $vv['total_count'];
					}
				}	
				$tag_group[$k]['tags'] = $tags_arr;
			}	
			
			$store_info['tag_group'] = $tag_group;

			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cate where id = ".$store_info['deal_cate_id']);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("youhui","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['STORE_LIST'],'url'=>url("youhui","store#index"));
			$site_nav[] = array('name'=>$cate_item['name'],'url'=>url("youhui","store#index",array("cid"=>$cate_item['id'])));
			$site_nav[] = array('name'=>$store_info['name'],'url'=>url("youhui","store#view",array("id"=>$store_info['id'])));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			
			
			$seo_title = $store_info['seo_title']?$store_info['seo_title']:$store_info['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $store_info['seo_keyword']?$store_info['seo_keyword']:$store_info['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $store_info['seo_description']?$store_info['seo_description']:$store_info['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			$GLOBALS['tmpl']->assign("store_info",$store_info);
			
			//输出最新加入的商家
			$new_stores = $GLOBALS['db']->getAll("select id,name,address from ".DB_PREFIX."supplier_location where city_id = ".$GLOBALS['deal_city']['id']." and is_effect = 1 order by id desc limit 5");
			$GLOBALS['tmpl']->assign("new_stores",$new_stores);
			$rec_stores = $GLOBALS['db']->getAll("select id,name,address,avg_point,dp_count from ".DB_PREFIX."supplier_location where city_id = ".$GLOBALS['deal_city']['id']." and is_recommend = 1 and is_effect = 1 order by is_recommend desc limit 5");
			$GLOBALS['tmpl']->assign("rec_stores",$rec_stores);
		}
		
		$GLOBALS['tmpl']->display("store_view.html",$cache_id);	
	}
	
	
	public function photos(){
		$id = intval($_REQUEST['id']);
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$id);	
		if (!$GLOBALS['tmpl']->is_cached('store_photos.html', $cache_id))	
		{
			$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if(!$store_info)
			{
				showErr($GLOBALS['lang']['NO_STORE_INFO']);
			}
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("youhui","index"));
			$site_nav[] = array('name'=>$store_info['name'],'url'=>url("youhui","store#view",array("id"=>$store_info['id'])));
			$site_nav[] = array('name'=>"店铺相册",'url'=>url("youhui","store#photos",array("id"=>$store_info['id'])));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			$GLOBALS['tmpl']->assign("store_info",$store_info);
		}
		$GLOBALS['tmpl']->display("store_photos.html",$cache_id);	
	}
	
	function load_store_photo_list($attr){
		$store_info=$attr['store_info'];
		
		//获取当前图片
 		$extWhere ="";
 		$extImgType ="";
 		if(intval($_REQUEST['pid']))
 			$extWhere = " and id=".intval($_REQUEST['pid']) ;
 		
 		if(isset($_REQUEST['images_group_id']))
 			$extImgType = " and images_group_id=".intval($_REQUEST['images_group_id']);
 		
 			
 		$sql = "select id,image,click_count,images_group_id,brief,user_id,create_time from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id=".intval($store_info['id'])." $extWhere $extImgType order by sort desc, id desc";
 		$pic_info = $GLOBALS['db']->getRow($sql);
 		
 		if(intval($pic_info['user_id'])> 0)
 			$pic_info['user_name'] = $GLOBALS['db']->getOne("select account_name from ".DB_PREFIX."supplier_account where id=".intval($pic_info['user_id']));

 		//更新统计
 		if(check_ipop_limit(get_client_ip(),"store_photos",10,$pic_info['id']))
		{
			$GLOBALS['db']->query("update  ".DB_PREFIX."supplier_location_images set click_count=click_count+1 where id=".intval($pic_info['id']));
 			$pic_info['click_count'] +=  1 ;
		}
 		
 		//上一张
 		$prev_pic = $GLOBALS['db']->getOne("select  min(id) from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id=".intval($store_info['id'])." and id > ".intval($pic_info['id'])." $extImgType order by sort desc, id desc");
 		$GLOBALS['tmpl']->assign("prev_pic",$prev_pic);
 		//下一张
 		$next_pic = $GLOBALS['db']->getOne("select  max(id) from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id=".intval($store_info['id'])." and id < ".intval($pic_info['id'])." $extImgType order by sort desc, id desc");
 		$GLOBALS['tmpl']->assign("next_pic",$next_pic);
 		
 		//获取全部图片数
 		$total_image =   $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id=".intval($store_info['id'])."");
 		$GLOBALS['tmpl']->assign("total_image",$total_image);
 		//获取店铺分类图片
 		$images_group_list = load_auto_cache("store_image_group_list",array("cate_id"=>intval($store_info['deal_cate_id'])));

 		foreach($images_group_list as $k=>$v){
	 		$images_group_list[$k]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_images where status = 1 and images_group_id=".intval($v['id'])." and supplier_location_id=".intval($store_info['id'])."");
	 	}	 	
 		$GLOBALS['tmpl']->assign("images_group_list",$images_group_list);
 		
 		//获取分类商户图片
 		$pic_list = $GLOBALS['db']->getAll("select id,image,click_count,images_group_id,brief,user_id,create_time from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id=".intval($store_info['id'])." $extImgType order by sort desc, id desc");
 		
 		$pic_idx  = 1;
 		$pic_tmp_idx = 1;
 		foreach($pic_list as $k => $v)
 		{
 			if($v['id'] == $pic_info['id']){
 				$pic_idx = $pic_tmp_idx;
 			}
 			$pic_tmp_idx ++;
 		}
 		
		$GLOBALS['tmpl']->assign("store_info",$store_info);
		$GLOBALS['tmpl']->assign("pic_idx",$pic_idx);
		$GLOBALS['tmpl']->assign("pic_info",$pic_info);
		$GLOBALS['tmpl']->assign("pic_list",$pic_list);
		$GLOBALS['tmpl']->assign("images_group_id",intval($_REQUEST['images_group_id']));
		return $GLOBALS['tmpl']->fetch("inc/store_photo_list.html");	
	}

	
	function get_reivew_form($arr){
		$store_info['id'] =  $arr['id'];
		$store_info['deal_cate_id'] =  $arr['deal_cate_id'];
		if(intval($_REQUEST['is_ajax'])==1){
			if(intval($_REQUEST['deal_cate_id'])> 0){
				$store_info['deal_cate_id'] = intval($_REQUEST['deal_cate_id']);
			}
			if(intval($_REQUEST['id'])>0){
				$store_info['id'] = intval($_REQUEST['id']);
			}
		}
		//点评的内容与分组
		$point_group = $GLOBALS['db']->getAll("select pg.name,pg.id from ".DB_PREFIX."point_group as pg left join ".DB_PREFIX."point_group_link as pgl on pg.id = pgl.point_group_id where pgl.category_id = ".intval($store_info['deal_cate_id'])." order by pg.sort asc");
		$store_info['point_group'] = $point_group;
		
		//点评标签分组
		$tag_group = $GLOBALS['db']->getAll("select tg.name,tg.id,tg.preset from ".DB_PREFIX."tag_group  as tg left join ".DB_PREFIX."tag_group_link as tgl on tg.id = tgl.tag_group_id where tgl.category_id = ".intval($store_info['deal_cate_id'])." and tg.allow_dp = 1 order by tg.sort asc");
		foreach($tag_group as $k=>$v)
		{
			$preset = $GLOBALS['db']->getOne("select mtg.preset from ".DB_PREFIX."supplier_tag_group_preset as mtg where mtg.supplier_location_id = ".$store_info['id']." and mtg.group_id = ".$v['id'] );
			if($preset)
			$tag_group[$k]['preset_tags'] = explode(" ",$preset);
			else
			$tag_group[$k]['preset_tags'] = explode(" ",$v['preset']);
		}
		$store_info['dp_tag_group'] = $tag_group;
		$GLOBALS['tmpl']->assign("store_info",$store_info);
		if(intval($_REQUEST['is_ajax'])==1)
			echo $GLOBALS['tmpl']->fetch("inc/review/review_form.html");
		else
			return $GLOBALS['tmpl']->fetch("inc/review/review_form.html");
	}
	
	function load_store_navs()
	{
		$location_id = intval($_REQUEST['id']);
		//与商户相关的菜单：团购,优惠,代金,活动
//		$t_sql = "select count(*) from ".DB_PREFIX."deal as d 
//					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
//					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 0 and d.time_status in (0,1) and l.location_id = ".$location_id;
//		
//		$d_sql = "select count(*) from ".DB_PREFIX."deal as d 
//					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
//					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 2 and d.time_status in (0,1) and l.location_id = ".$location_id;
//
//
//		$time_condition = '  and (y.end_time = 0 or y.end_time > '.get_gmtime().' ) ';
//		$y_sql = "select count(*) from ".DB_PREFIX."youhui as y 
//					left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id  = y.id 
//					where y.is_effect = 1 and l.location_id = ".$location_id.$time_condition;
//		
//		$e_sql = "select count(*) from ".DB_PREFIX."event as e 
//					left join ".DB_PREFIX."event_location_link as l on l.event_id  = e.id 
//					where e.is_effect = 1 and l.location_id = ".$location_id;
//		
//		$tcount = $GLOBALS['db']->getOne($t_sql);
//		$dcount = $GLOBALS['db']->getOne($d_sql);
//		$ycount = $GLOBALS['db']->getOne($y_sql);
//		$ecount = $GLOBALS['db']->getOne($e_sql);
//		
		$location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
		$tcount = $location_info['tuan_count'];
		$dcount = $location_info['daijin_count'];
		$ycount = $location_info['youhui_count'];
		$ecount = $location_info['event_count'];
		$scount = $location_info['shop_count'];
		//if($tcount>0)
		{
			$navs[] = array("name"=>"团购","location_id"=>$location_id,"type"=>"t","count"=>$tcount);
		}
		//if($ycount>0)
		{
			$navs[] = array("name"=>"优惠券","location_id"=>$location_id,"type"=>"y","count"=>$ycount);
		}
		//if($dcount>0)
		{
			$navs[] = array("name"=>"代金券","location_id"=>$location_id,"type"=>"d","count"=>$dcount);
		}
		//if($ecount>0)
		{
			$navs[] = array("name"=>"活动","location_id"=>$location_id,"type"=>"e","count"=>$ecount);
		}
		//if($scount>0)
		{
			$navs[] = array("name"=>"商品","location_id"=>$location_id,"type"=>"s","count"=>$scount);
		}
		$GLOBALS['tmpl']->assign("navs",$navs);
		return $GLOBALS['tmpl']->fetch("inc/store_navs.html");
	}
	
	public function ajax_get_content()
	{
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$type = addslashes($_REQUEST['type']);
		$location_id = intval($_REQUEST['id']);
		$page_size = app_conf("PAGE_SIZE"); 
		$upara = array(
			"type"=>$type,
			"id"=>$location_id
		);
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;		
		
		switch($type)
		{
			case "t":
				//团购
				$t_sql_count = "select count(*) from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 0 and d.time_status in (0,1) and l.location_id = ".$location_id;
				$t_sql = "select id,name,img,uname from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 0 and d.time_status in (0,1) and l.location_id = ".$location_id." order by d.sort desc limit ".$limit;
			
				$result_list = $GLOBALS['db']->getAll($t_sql);
				$count = $GLOBALS['db']->getOne($t_sql_count);
				foreach($result_list as $k=>$v)
				{
					$list[$k]['id'] = $v['id'];
					$list[$k]['name'] = $v['name'];
					$list[$k]['image'] = $v['img'];	
					if($v['uname']=='')				
					$list[$k]['url'] = url("tuan","deal",array("id"=>$v['id']));	
					else
					$list[$k]['url'] = url("tuan","deal",array("id"=>$v['uname']));		
				}				
				break;
			case "d":
				//代金
				$d_sql_count = "select count(*) from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 2 and d.time_status in (0,1) and l.location_id = ".$location_id;
				$d_sql = "select id,name,img,uname from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 2 and d.time_status in (0,1) and l.location_id = ".$location_id." order by d.sort desc limit ".$limit;
				$result_list = $GLOBALS['db']->getAll($d_sql);
				$count = $GLOBALS['db']->getOne($d_sql_count);
				foreach($result_list as $k=>$v)
				{
					$list[$k]['id'] = $v['id'];
					$list[$k]['name'] = $v['name'];
					$list[$k]['image'] = $v['img'];	
					if($v['uname']=='')				
					$list[$k]['url'] = url("youhui","ydetail",array("id"=>$v['id']));	
					else
					$list[$k]['url'] = url("youhui","ydetail",array("id"=>$v['uname']));		
				}		
				break;
			case "s":
				//商城
				$d_sql_count = "select count(*) from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 1 and d.time_status in (0,1) and l.location_id = ".$location_id;
				$d_sql = "select id,name,img,uname from ".DB_PREFIX."deal as d 
					left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id 
					where d.is_delete = 0 and d.is_effect = 1 and d.is_shop = 1 and d.time_status in (0,1) and l.location_id = ".$location_id." order by d.sort desc limit ".$limit;
				$result_list = $GLOBALS['db']->getAll($d_sql);
				$count = $GLOBALS['db']->getOne($d_sql_count);
				foreach($result_list as $k=>$v)
				{
					$list[$k]['id'] = $v['id'];
					$list[$k]['name'] = $v['name'];
					$list[$k]['image'] = $v['img'];	
					if($v['uname']=='')				
					$list[$k]['url'] = url("shop","goods",array("id"=>$v['id']));	
					else
					$list[$k]['url'] = url("shop","goods",array("id"=>$v['uname']));		
				}		
				break;
			case "y":				
				//优惠
				$time_condition = '  and (y.end_time = 0 or y.end_time > '.get_gmtime().' ) ';
				$y_sql_count = "select count(*) from ".DB_PREFIX."youhui as y 
					left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id  = y.id 
					where y.is_effect = 1 and l.location_id = ".$location_id.$time_condition;
				$y_sql = "select id,name,icon from ".DB_PREFIX."youhui as y 
					left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id  = y.id 
					where y.is_effect = 1 and l.location_id = ".$location_id.$time_condition." order by y.sort desc limit ".$limit;
				$result_list = $GLOBALS['db']->getAll($y_sql);
				$count = $GLOBALS['db']->getOne($y_sql_count);
				foreach($result_list as $k=>$v)
				{
					$list[$k]['id'] = $v['id'];
					$list[$k]['name'] = $v['name'];
					$list[$k]['image'] = $v['icon'];	
					$list[$k]['url'] = url("youhui","fdetail",array("id"=>$v['id']));		
				}		
				break;
			case "e":
				//活动
				$e_sql_count = "select count(*) from ".DB_PREFIX."event as e 
					left join ".DB_PREFIX."event_location_link as l on l.event_id  = e.id 
					where e.is_effect = 1 and l.location_id = ".$location_id;
				$e_sql = "select id,name,icon from ".DB_PREFIX."event as e 
					left join ".DB_PREFIX."event_location_link as l on l.event_id  = e.id 
					where e.is_effect = 1 and l.location_id = ".$location_id." order by e.sort desc limit ".$limit;
				$result_list = $GLOBALS['db']->getAll($e_sql);
				$count = $GLOBALS['db']->getOne($e_sql_count);
				foreach($result_list as $k=>$v)
				{
					$list[$k]['id'] = $v['id'];
					$list[$k]['name'] = $v['name'];
					$list[$k]['image'] = $v['icon'];	
					$list[$k]['url'] = url("youhui","edetail",array("id"=>$v['id']));		
				}				
				break;
		}
		
		$GLOBALS['current_url'] = url("youhui","store#ajax_get_content",$upara);		
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("inc/store_ajax_get_content.html");
	}
	
	function send_sms_view(){
		$return["status"]=0;
		if(!$GLOBALS['user_info']){
			$return["status"]=2;
			$return["message"]=$GLOBALS['LANG']["PLEASE_LOGIN_FIRST"];
			ajax_return($return);
			exit();
		}
		
		$id = intval($_REQUEST['id']);
		if($id==0){
			$return["message"]=$GLOBALS['LANG']["NO_SUPPLIER"];
			ajax_return($return);
			exit();
		}
		
		$store_info = $GLOBALS['db']->getRow("select `id`,`name`,`address`,`tel`,`sms_content` from ".DB_PREFIX."supplier_location where id = ".$id);
		if(!$store_info)
		{
			$return["message"]=$GLOBALS['LANG']["NO_SUPPLIER"];
			ajax_return($return);
			exit();
		}		
		
		$return["status"]=1;
		$GLOBALS['tmpl']->assign("store_info",$store_info);
		$return['html'] = $GLOBALS['tmpl']->fetch("inc/store/sms_view_form.html");
		ajax_return($return);
		exit();
	}
	function send_store_sms(){
		$return["status"]=0;
		if(!$GLOBALS['user_info']){
			$return["status"]=2;
			$return["message"]=$GLOBALS['LANG']["PLEASE_LOGIN_FIRST"];
			ajax_return($return);
			exit();
		}
		
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				$return["message"]=$GLOBALS['lang']['VERIFY_CODE_ERROR'];
				ajax_return($return);
				exit();
			}
		}
		
		es_session::delete("verify");
		
		$now = get_gmtime();
		$today_begin = to_timespan(to_date($now,"Y-m-d"));
		$today_end = $today_begin + 24*3600;
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where user_id = ".$GLOBALS['user_info']['id']." and is_youhui = 2 and create_time between ".$today_begin." and ".$today_end);

		if($count>=intval(app_conf("STORE_SEND_LIMIT")))
		{
			 $return['message'] = $GLOBALS['lang']['SMS_LIMIT_OVER'];
			 ajax_return($return);
			 exit();
		}
		
		$id = intval($_REQUEST['store_id']);
		$store_info = $GLOBALS['db']->getRow("select `name`,`address`,`tel`,`sms_content` from ".DB_PREFIX."supplier_location where id = ".$id);
		if(!$store_info)
		{
			$return["status"]=0;
			$return["message"]=$GLOBALS['LANG']["NO_SUPPLIER"];
			ajax_return($return);
			exit();
		}	
		
		if($store_info['sms_content']!="")
		$sms_content = $store_info['sms_content'];
		else
		{
			$sms_content = $store_info['name'].$store_info['tel'].$store_info['address'];
		}
		
		$msg_data['send_type'] = 0;
		$msg_data['content'] = $sms_content;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = get_gmtime();
		$msg_data['user_id'] = $GLOBALS['user_info']['id'];
		$msg_data['is_html'] = 0;
		$msg_data['is_youhui'] = 2;
		
		$msg_data['dest'] = trim($_REQUEST['mobile']);
		if(check_mobile($msg_data['dest'])&&$msg_data['dest']!="")
		/**2013-07-30@哥将添加**/
		require_once APP_ROOT_PATH."system/utils/es_sms.php";
		$sms = new sms_sender();
	    $result = $sms->sendSms($msg_data['dest'],$msg_data['content']);
		if($result['status']){$msg_data['is_success']=1;}
		/**2013-07-30@哥将添加结束**/
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入 
		
		$return["status"]=1;
		ajax_return($return);
		exit();
	}
	
	public function vote_tag()
	{
		if($GLOBALS['user_info'])
		{
			$tag_name = addslashes(htmlspecialchars(trim($_REQUEST['tag_name'])));
			$group_id = intval($_REQUEST['group_id']);
			$location_id = intval($_REQUEST['location_id']);
			$user_id = intval($GLOBALS['user_info']['id']);
			$rs = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tag_user_vote where user_id = ".$user_id." and group_id = ".$group_id." and location_id = ".$location_id." and tag_name = '".$tag_name."'"));
			if($rs)
			{
				$result['status'] = 0;
				$result['info'] = "您已经对该项投过票了";
				ajax_return($result);
			}
			else
			{
				$data['tag_name'] = $tag_name;
				$data['user_id'] = $user_id;
				$data['group_id'] = $group_id;
				$data['location_id'] = $location_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."tag_user_vote",$data);
				$GLOBALS['db']->query("update ".DB_PREFIX."supplier_tag set total_count = total_count +1 where tag_name = '".$tag_name."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);
				$count = intval($GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."supplier_tag where tag_name = '".$tag_name."' and supplier_location_id = ".$location_id." and group_id = ".$group_id));
				$result['status'] = 1;
				$result['info'] = $tag_name."(".$count.")";
				$cache_id  = md5("store"."view".$location_id);		
				$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
				ajax_return($result);
			}
		}
		else
		{
			$result['status'] = 2;
			ajax_return($result);
		}
	}
	
	
	function load_recent_sign($param)
	{
		$id = intval($param['id']);
		$time = get_gmtime();
		$begin_time = get_gmtime() - 3 * 24 * 3600;
		$end_time = get_gmtime() + 3 * 24 * 3600;

		$list = $GLOBALS['db']->getAll("select distinct(user_id) from ".DB_PREFIX."supplier_location_sign_log where location_id = $id and (sign_time between $begin_time and $end_time) order by sign_time desc limit 9 ");
		$count = $GLOBALS['db']->getOne("select count(distinct(user_id)) from ".DB_PREFIX."supplier_location_sign_log where location_id = $id and (sign_time between $begin_time and $end_time)  ");
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("count",$count);
		if($list)
		return $GLOBALS['tmpl']->fetch("inc/user_sign.html");
		else
		return ""; 
		
	}
	
	
	//品牌招商页面
	public function brand()
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('store_brand.html', $cache_id))	
		{	
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['STORE_BRAND'],'url'=>url("youhui","store#brand"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			$title = $GLOBALS['lang']['STORE_BRAND'];
				
			
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
			
			
			$city_id = intval($GLOBALS['deal_city']['id']);
			if($city_id>0)
			{			
					$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));				
					if($ids)
					$city_condition = " and city_id in (".implode(",",$ids).")";
	
			}
			foreach($cate_list as $k=>$v)
			{
				$cate_list[$k]['store_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where deal_cate_id = ".$v['id']." and is_verify = 1 and is_effect = 1 and is_recommend = 1 $city_condition limit 15");
			}
			
			
			$GLOBALS['tmpl']->assign("cate_list",$cate_list);	
			$GLOBALS['tmpl']->assign("page_title",$title);	
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");		
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
		}
		$GLOBALS['tmpl']->display("store_brand.html",$cache_id);		
	}
	
	
	public function sign_page()
	{
		$location_id = intval($_REQUEST['id']);
		$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id." and is_effect = 1");
		$GLOBALS['tmpl']->assign("store_info",$store_info);	
		$GLOBALS['tmpl']->display("store_sign_page.html");	
	}
	
	public function do_sign(){
		if($GLOBALS['user_info'])
		{
			$point = intval($_REQUEST['point']);
			$id = intval($_REQUEST['id']);
			$user_id = intval($GLOBALS['user_info']['id']);
			if($point<=0||$point>5)
			{
				ajax_return(array("status"=>false,"message"=>"请点击星星为商家打分，最高5颗星"));
			}
			$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id." and is_effect = 1");
			if($store_info)
			{
				$begin_time = get_gmtime() - 3 * 24 * 3600;
				$end_time = get_gmtime() + 3 * 24 * 3600;
				$sign_data_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_sign_log where user_id = ".$user_id." and location_id  = ".$id." and (sign_time between $begin_time and $end_time)");
				if($sign_data_count)
				{
					ajax_return(array("status"=>false,"message"=>"最近一周您已经签到过了"));
				}
				else
				{
					$sign_data['user_id'] = $user_id;
					$sign_data['location_id'] = $id;
					$sign_data['point'] = $point;
					$sign_data['sign_time'] = get_gmtime();
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_sign_log",$sign_data);
					
					
					syn_supplier_locationcount($store_info);
					$cache_id  = md5("store"."view".$store_info['id']);		
					$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
					ajax_return(array("status"=>true,"message"=>"签到成功"));
				}
			}
			else
			{
				ajax_return(array("status"=>false,"message"=>"商户不存在"));
			}
		}
		else
		{
			ajax_return(array("status"=>false,"message"=>"请先登录"));
		}
	}
}
?>