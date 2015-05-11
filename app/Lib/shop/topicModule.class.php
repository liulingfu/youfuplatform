<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class topicModule extends ShopBaseModule
{
	public function index()
	{
			$id = intval($_REQUEST['id']);			
			
			$message_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$id." and is_effect= 1 and is_delete=0");
			if(!$message_item)
			{
				app_redirect(url("shop"));
				//showErr($GLOBALS['lang']['TOPIC_NULL']);
			}
			if(check_ipop_limit(get_client_ip(),"topic",intval(app_conf("SUBMIT_DELAY")),$id))
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set click_count = click_count + 1 where id = ".$id);
			}
			
			$message_item = get_topic_item($message_item);
			$group_item = $message_item['topic_group'];
			$GLOBALS['tmpl']->assign("group_info",$group_item);
			
			if($group_item)
			{
				$user_join_group = $GLOBALS['db']->getOne("select group_concat(group_id) from ".DB_PREFIX."user_topic_group where user_id = ".intval($message_item['user_id'])." limit 6");
				if(!$user_join_group)
				$user_join_group = "0";
				$user_joing_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group where is_effect = 1 and (id in (".$user_join_group.") or user_id = ".intval($message_item['user_id']).") limit 6");
				$GLOBALS['tmpl']->assign("user_joing_group",$user_joing_group);
				
				//小组其他主题
				$group_topic_rec_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where id <> ".$message_item['id']." and group_id = ".$message_item['group_id']." and is_best = 1 order by create_time desc limit 5 ");
				$GLOBALS['tmpl']->assign("group_topic_rec_list",$group_topic_rec_list);
				$message_item['group_name'] = $group_item['name'];
			}
			
			
			$message_item['content'] = decode_topic($message_item['content']);
			if($message_item['origin'])
			$message_item['origin']['content'] = decode_topic($message_item['origin']['content']);			
			$title = $message_item['title'] == ''?$GLOBALS['lang']['TOPIC_SHOW']: $message_item['title'];

		
			$muid = intval($message_item['user_id']);
			$muser_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$muid." and is_effect=  1 and is_delete = 0");
			
			$uid = intval($GLOBALS['user_info']['id']);
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$uid." and focused_user_id = ".intval($muser_info['id']));
			if($focus_data)
			$muser_info['focused'] = 1;
			
			$region_list = load_auto_cache("cache_region_conf");
// 			$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($muser_info['province_id']));
			$province_str = $region_list[intval($muser_info['province_id'])]['name'];
			//$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".intval($muser_info['city_id']));
			$city_str = $region_list[intval($muser_info['city_id'])]['name'];
			if($province_str.$city_str=='')
			$user_location = $GLOBALS['lang']['LOCATION_NULL'];
			else 
			$user_location = $province_str.$city_str;
			
			$muser_info['user_location'] = $user_location;
			$GLOBALS['tmpl']->assign("muser_info",$muser_info);
			
			
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			if($message_item['group_id']>0)
			$site_nav[] = array('name'=>$message_item['group_name'],'url'=>url("shop","group#forum",array("id"=>$message_item['group_id'])));
			$site_nav[] = array('name'=>$title,'url'=>url("shop", "topic", array("id"=>$message_item['id'])));
			
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			$GLOBALS['tmpl']->assign("message_item",$message_item);
			

			if(trim($message_item['group_name'])!="") $title = $message_item['group_name']." - ".$title;
			if(trim($message_item['title'])!="") $title = $message_item['title']." - ".$title;
			if(trim($message_item['forum_title'])!="") $title = $message_item['forum_title']." - ".$title;
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
			$GLOBALS['tmpl']->display("topic_index.html");
	}
	
	public function reply()
	{

		$no_verify = intval($_REQUEST['no_verify']);
		$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info'])
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		//验证码
		if(app_conf("VERIFY_IMAGE")==1&&$no_verify==0)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax);
			}
		}
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		$topic_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['topic_id']));
		if(!$topic_info)
		showErr("主题不存在",$ajax);
		
		$reply_data = array();
		$reply_data['topic_id'] = intval($_REQUEST['topic_id']);
		$reply_data['user_id'] = intval($GLOBALS['user_info']['id']);
		$reply_data['user_name'] = $GLOBALS['user_info']['user_name'];
		$reply_data['reply_id'] = intval($_REQUEST['reply_id']);
		if($reply_data['reply_id']>0)
		{
			$reply_reply_data = $GLOBALS['db']->getRow("select id,user_id,user_name from ".DB_PREFIX."topic_reply where id = ".$reply_data['reply_id']);
			$reply_data['reply_user_id'] = $reply_reply_data['user_id'];
			$reply_data['reply_user_name'] = $reply_reply_data['user_name'];
		}
		$reply_data['create_time'] = get_gmtime();
		$reply_data['is_effect'] = 1;
		$reply_data['is_delete'] = 0;
		$reply_data['content'] = htmlspecialchars(valid_str(addslashes($_REQUEST['content'])));
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_reply",$reply_data);
		$id = $GLOBALS['db']->insert_id();
		
		if($id>0)
		{
			increase_user_active(intval($GLOBALS['user_info']['id']),"回应了一则分享");
			$attach_list = get_topic_attach_list();
			foreach($attach_list as $attach)
			{
				if($attach['type']=='image')
				{
					//插入图片
					$GLOBALS['db']->query("update ".DB_PREFIX."topic_image set topic_id = ".$id.",topic_table='topic_reply' where id = ".$attach['id']);			
				}
			}
		}
		
		//删除所有创建超过一小时，且未被使用过的图片
		$del_list = $GLOBALS['db']->getAll("select id,path from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		foreach($del_list as $k=>$v)
		{
			@unlink(APP_ROOT_PATH.$v['path']);
			@unlink(APP_ROOT_PATH.$v['o_path']);
		}
		
		
		
		$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count + 1,last_time = ".get_gmtime().",last_user_id = ".intval($GLOBALS['user_info']['id'])." where id = ".$reply_data['topic_id']);
		showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS'],$ajax);
	}
	
	public function search()
	{		
		//获取可以相关的用户		
		$user_list = get_rand_user(4);		
		$user_id = intval($GLOBALS['user_info']['id']);
		$ids = array(0);
		foreach($user_list as $k=>$v)
		{
			$ids[] = $v['id'];
		}
		$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id in (".implode(",", $ids).")");
		foreach($user_list as $k=>$v)
		{						
			foreach($focus_data as $kk=>$vv)
			{
				if($vv['focused_user_id']==$v['id'])
				{
					$user_list[$k]['focused'] = 1;
					break;
				}
			}
			
			if($v['city_id']!=0&&$v['city_id']==$GLOBALS['deal_city']['id'])
			{
				$user_list[$k]['same_city'] = 1;
			}
			else 
				$user_list[$k]['same_city'] = 0;
		}		
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		
		convert_req($_REQUEST);	
		$type = intval($_REQUEST['type']);
		$filter = intval($_REQUEST['filter']);
		$keyword = addslashes(htmlspecialchars(trim($_REQUEST['keyword'])));
		$GLOBALS['tmpl']->assign("keyword",$keyword);	
		$GLOBALS['tmpl']->assign("type",$type);	
		$GLOBALS['tmpl']->assign("filter",$filter);	
		
		$url_param = array(
			"type" => $type,
			"filter"	=>	$filter,
			"keyword"  => $keyword
		);
		
		if($type==0)unset($url_param['type']);
		if($filter==0)unset($url_param['filter']);
		
		if(intval($_REQUEST['is_redirect'])==1)
		{
			if($type>0)
			app_redirect(url("shop","topic#search",$url_param));
			else
			app_redirect(url("shop","topic#search",$url_param));
		}
		
		
		
		$type_navs = array(
			array("type"=>0,"name"=>$GLOBALS['lang']['TOPIC_TYPE_ALL']),  //所有分享
			array("type"=>1,"name"=>$GLOBALS['lang']['TOPIC_TYPE_DEAL']),  //购物分享
			array("type"=>2,"name"=>$GLOBALS['lang']['TOPIC_TYPE_STORE']),  //商家点评
			array("type"=>3,"name"=>$GLOBALS['lang']['TOPIC_TYPE_RECOMMEND']),  //热点推荐
		);
		foreach($type_navs as $k=>$v)
		{
			$tmp_url_param = $url_param;
			if($v['type']!=0)
			$tmp_url_param['type'] = $v['type'];
			else
			unset($tmp_url_param['type']);
			$type_navs[$k]['url'] = url("shop","topic#search",$tmp_url_param);
			if($v['type']==$type)
			{
				$type_navs[$k]['act'] = true;
			}
		}
		
		$filter_navs = array(
			array("filter"=>0,"name"=>$GLOBALS['lang']['TOPIC_FILTER_ALL']),  //全部
			array("filter"=>1,"name"=>$GLOBALS['lang']['TOPIC_FILTER_MYFOCUS']),  //我关注的
			array("filter"=>2,"name"=>$GLOBALS['lang']['TOPIC_FILTER_DAREN']),  //达人
			array("filter"=>3,"name"=>$GLOBALS['lang']['TOPIC_FILTER_MERCHANT']),  //商户
		);
		foreach($filter_navs as $k=>$v)
		{
			$tmp_url_param = $url_param;
			if($v['filter']!=0)
			$tmp_url_param['filter'] = $v['filter'];
			else
			unset($tmp_url_param['filter']);
			$filter_navs[$k]['url'] = url("shop","topic#search",$tmp_url_param);
			if($v['filter']==$filter)
			{
				$filter_navs[$k]['act'] = true;
			}
		}
		
		$condition = " 1=1 ";
		if($type==0)
		{
			//所有分享
			$condition .= "";
			$search_title = $GLOBALS['lang']['TOPIC_TYPE_ALL'];
		}
		elseif($type==1)
		{
			$condition.=" and (t.type='tuancomment' or t.type='shopcomment' or t.type='youhuicomment' ) ";
			$search_title = $GLOBALS['lang']['TOPIC_TYPE_DEAL'];
		}
		elseif($type==2)
		{
			$condition.=" and t.type='slocationcomment' ";
			$search_title = $GLOBALS['lang']['TOPIC_TYPE_STORE'];
		}
		elseif($type==3)
		{
			$condition.=" and t.title like '".$keyword."' ";
			$search_title = $GLOBALS['lang']['TOPIC_TYPE_RECOMMEND'];
		}
		
		if($filter==0)
		{
			//全部
			//$search_title.= " - ".$GLOBALS['lang']['TOPIC_FILTER_ALL'];
		}
		elseif($filter==1)
		{
			$search_title.= " - ".$GLOBALS['lang']['TOPIC_FILTER_MYFOCUS'];
			$condition.=" and uf.focus_user_id =  ".intval($GLOBALS['user_info']['id']);
			
		}
		elseif($filter==2)
		{
			$search_title.= " - ".$GLOBALS['lang']['TOPIC_FILTER_DAREN'];
			$condition.=" and u.is_daren = 1 ";
		}
		elseif($filter==3)
		{
			$search_title.= " - ".$GLOBALS['lang']['TOPIC_FILTER_MERCHANT'];
			$condition.=" and u.is_merchant = 1 ";
		}
		

		if($keyword)
		{			
			$search_title.=" - ".$keyword;
			$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kw[$k] = str_to_unicode_string($item);
			}
			$ukeyword = implode(" ",$kw);
			$condition.=" and match(t.keyword_match) against('".$ukeyword."'  IN BOOLEAN MODE) ";
		}
		$GLOBALS['tmpl']->assign("kws_div",$kws_div);
		$GLOBALS['tmpl']->assign("page_title",$search_title);
		$GLOBALS['tmpl']->assign("page_keyword",$search_title.",");
		$GLOBALS['tmpl']->assign("page_description",$search_title.",");
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");	
		
//		$result = get_topic_list($limit,$condition,"",$kws_div);
		
		
		$orderby='t.create_time desc';
		$condition = " and ".$condition;
		$list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic as t left join ".DB_PREFIX."user as u on t.user_id = u.id left join ".DB_PREFIX."user_focus as uf on uf.focused_user_id = t.user_id where t.is_effect = 1 and t.is_delete = 0 ".$condition." group by t.id order by ".$orderby." limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(distinct(t.id)) from ".DB_PREFIX."topic as t left join ".DB_PREFIX."user as u on t.user_id = u.id left join ".DB_PREFIX."user_focus as uf on uf.focused_user_id = t.user_id where t.is_effect = 1 and t.is_delete = 0  ".$condition);
	
		foreach($list as $k=>$v)
		{
			$list[$k] =	get_topic_item($v,$kws_div);
			if(msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['content']),0,50)!=preg_replace("/<[^>]+>/i","",$list[$k]['content']))
			$list[$k]['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['content']),0,50);
			else
			$list[$k]['short_content'] = preg_replace("/<br[^>]+>/i","",$list[$k]['content']);
			
			if($list[$k]['origin'])
			{
				if(msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']),0,50)!=preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']))
				$list[$k]['origin']['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']),0,50);
				else
				$list[$k]['origin']['short_content'] = preg_replace("/<br[^>]+>/i","",$list[$k]['origin']['content']);
			}
		}
		
		$result = array('list'=>$list,'total'=>$total);
		
		
		if($result['total']>0)
		{
			if(check_ipop_limit(get_client_ip(),"topic_search",10,$keyword))
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_tag set count = count + 1 where name = '".$keyword."'");
		}
		//$result['list'] = div_to_col($result['list']);
		$GLOBALS['tmpl']->assign("topic_list",$result['list']);
		$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$list_html = load_topic_list();		
		//$list_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
		$GLOBALS['tmpl']->assign("list_html",$list_html);
	
		$GLOBALS['tmpl']->assign("type_navs",$type_navs);	
		$GLOBALS['tmpl']->assign("filter_navs",$filter_navs);	

		$res = load_dynamic_cache("topic_search_hot");
		if($res===false)
		{
			$res['hot_tag_list'] =$GLOBALS['db']->getAll("select name,color from ".DB_PREFIX."topic_tag where is_recommend = 1 order by sort desc, count desc limit 10");
			$res['hot_title_list'] =$GLOBALS['db']->getAll("select name,color from ".DB_PREFIX."topic_title where is_recommend = 1 order by sort desc,count desc limit 10");
			set_dynamic_cache("topic_search_hot", $res);
		}
		
		//输出搜索热词
		
		$GLOBALS['tmpl']->assign("hot_tag_list",$res['hot_tag_list']);
		$GLOBALS['tmpl']->assign("hot_title_list",$res['hot_title_list']);
		
		//输出推荐分享
		$recommend_topic = load_auto_cache("recommend_uc_topic");
		$GLOBALS['tmpl']->assign("recommend_topic",$recommend_topic);
		
		
		$GLOBALS['tmpl']->display("topic_search.html");
	}
	
//	public function all()
//	{
//
//		$title = $GLOBALS['lang']['USER_POST'];
//			
//			
//		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
//		$site_nav[] = array('name'=>$title,'url'=>url("shop","topic#all"));
//			
//		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
//	
//		//分页
//			$page = intval($_REQUEST['p']);
//			if($page==0)
//			$page = 1;
//			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");		
//			
//			$result = get_topic_list($limit);
//			
//			
//			$GLOBALS['tmpl']->assign("topic_list",$result['list']);
//			$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
//			$p  =  $page->show();
//			$GLOBALS['tmpl']->assign('pages',$p);
//			
//			
//			
//			$GLOBALS['tmpl']->assign("page_title",$title);
//			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
//			$GLOBALS['tmpl']->assign("page_description",$title.",");
//			$GLOBALS['tmpl']->display("topic_all.html");
//	}
	
}
?>