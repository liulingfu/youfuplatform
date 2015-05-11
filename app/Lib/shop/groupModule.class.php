<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class groupModule extends ShopBaseModule
{
	public function index()
	{	
		$title = $GLOBALS['lang']['GROUP_FORUM'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$title,'url'=>url("shop", "group"));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group_cate where id = ".intval($_REQUEST['id']));
		$cate_id = intval($cate_item['id']);
		$GLOBALS['tmpl']->assign("cate_id",$cate_id);
		
		//输出热门小组
		$hot_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group where is_effect = 1 order by topic_count desc,user_count desc limit 4");
		$GLOBALS['tmpl']->assign("hot_group",$hot_group);	
		
		//输出分类列表
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group_cate where is_effect = 1 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		//输出优秀小组长
		$group_adm_list = $GLOBALS['db']->getAll("select id,name,user_count,user_id from ".DB_PREFIX."topic_group  where is_effect = 1 and user_id <> 0 group by user_id order by topic_count desc limit 5");
		$GLOBALS['tmpl']->assign("group_adm_list",$group_adm_list);
		
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;	
		
		if($cate_id>0)
		$cate_condition = " and cate_id = ".$cate_id;
		$sql = " select * from ".DB_PREFIX."topic_group where is_effect = 1 $cate_condition order by sort desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."topic_group where is_effect = 1 $cate_condition ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		//输出最新主题
		$new_topic_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where group_id <> 0 and is_effect = 1 and is_delete = 0 order by create_time desc limit 10");
		foreach($new_topic_list as $k=>$v)
		{
			$new_topic_list[$k]['group_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."topic_group where id = ".$v['group_id']);
		}
		$GLOBALS['tmpl']->assign('new_topic_list',$new_topic_list);
		
		//输出推荐的主题
		$rec_topic_list = load_auto_cache("recommend_forum_topic");
		$GLOBALS['tmpl']->assign("rec_topic_list",$rec_topic_list);
		$GLOBALS['tmpl']->display("group_index.html");
	}
	
	
	public function forum()
	{
		$group_id = intval($_REQUEST['id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		if(!$group_item)
		showErr("不存在的小组");
		$GLOBALS['tmpl']->assign("group_info",$group_item);
		
		$title = $group_item['name'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("shop", "group"));
		$site_nav[] = array('name'=>$title,'url'=>url("shop", "group#forum",array("id"=>$group_id)));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		
		
		//输出是否加入组
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$is_join = 0;
		}
		else
		{
			$is_admin = 0;
			if($group_item['user_id']==$user_id)
			{
				$is_admin = 1;
			}
			if($is_admin==0)
			{
				$join_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_topic_group where user_id = ".$user_id." and group_id = ".$group_item['id']);
				if($join_data)
				{
					$is_join = 1;
					$is_admin = $join_data['type'];
				}
				else
				{
					$is_join = 0;
				}
			}
			else
			$is_join = 1;
		}
		$GLOBALS['tmpl']->assign('is_join',$is_join);
		$GLOBALS['tmpl']->assign('is_admin',$is_admin);
		
		//输出列表
		$page_size = app_conf("PAGE_SIZE");
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;	
		
		
		
		$condition = " and group_id = ".$group_item['id'];
		$sortby = "is_top desc,create_time desc";
		$sortkey = "ordery_sort";
		
		$filter = intval($_REQUEST['filter']); //0全部 1推荐
		$sort = intval($_REQUEST['sort']); //0创建时间 1回复时间
		$url_param = array("filter"=>$filter,"sort"=>$sort,"p"=>$page,"id"=>$group_id);
		
		if($filter==1)
		{
			$condition.=" and is_best = 1 ";			
		}
		if($sort==1)
		{
			$sortby = " is_top desc,last_time desc ";
			$sortkey = "last_time_sort";
		}
		
		$tmp_url_param = $url_param;
		$tmp_url_param['filter'] = 0;
		$urls['all'] = url("shop","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['filter'] = 1;
		$urls['is_best'] = url("shop","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['sort'] = 0;
		$urls['create_time'] = url("shop","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['sort'] = 1;
		$urls['last_time'] = url("shop","group#forum",$tmp_url_param);
		
		$GLOBALS['tmpl']->assign("urls",$urls);
		
		$sql = "select * from ".DB_PREFIX."topic use index($sortkey) where is_effect = 1 and is_delete = 0  $condition order by $sortby limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."topic use index($sortkey) where is_effect = 1 and is_delete = 0 $condition ";
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		//输出组员
		$user_list = $GLOBALS['db']->getAll("select user_id as id,type from ".DB_PREFIX."user_topic_group where group_id = ".$group_item['id']." order by type desc limit 10 ");
		$GLOBALS['tmpl']->assign('user_list',$user_list);
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$GLOBALS['tmpl']->display("group_forum.html");
	}
	
	public function addtopic()
	{
		$user_id =intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		$group_id = intval($_REQUEST['id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		if(!$group_item)
		showErr("不存在的小组");
		$GLOBALS['tmpl']->assign("group_info",$group_item);
		
		
		$title = $group_item['name']."发表主题";
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("shop", "group"));
		$site_nav[] = array('name'=>$group_item['name'],'url'=>url("shop", "group#forum",array("id"=>$group_id)));
		$site_nav[] = array('name'=>"发表主题",'url'=>url("shop", "group#addtopic",array("id"=>$group_id)));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		
		$GLOBALS['tmpl']->display("group_addtopic.html");
	}
	
	
	public function user_list()
	{
		$user_id =intval($GLOBALS['user_info']['id']);
		$group_id = intval($_REQUEST['id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		if(!$group_item)
		showErr("不存在的小组");
		$GLOBALS['tmpl']->assign("group_info",$group_item);
		
		
		$title = $group_item['name']."组员";
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("shop", "group"));
		$site_nav[] = array('name'=>$group_item['name'],'url'=>url("shop", "group#forum",array("id"=>$group_id)));
		$site_nav[] = array('name'=>"组员",'url'=>url("shop", "group#user_list",array("id"=>$group_id)));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
				
		$page_size = 24;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		
		//输出粉丝
		$user_list = $GLOBALS['db']->getAll("select user_id as id from ".DB_PREFIX."user_topic_group where group_id = ".$group_id."  limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_topic_group where group_id = ".$group_id);
		
		foreach($user_list as $k=>$v)
		{			
			$focus_uid = intval($v['id']);
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
			if($focus_data)
			$user_list[$k]['focused'] = 1;
		}
		$GLOBALS['tmpl']->assign("user_list",$user_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		
		$GLOBALS['tmpl']->display("group_user_list.html");
	}
	
	public function joingroup()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		$group_id = intval($_REQUEST['id']);
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$group_id);
		if($group['user_id']!=$user_id)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id)==0)
			{
				$data['group_id'] = $group_id;
				$data['user_id'] = $user_id;
				$data['create_time'] = get_gmtime();
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_topic_group",$data,"INSERT","","SILENT");
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count + 1 where id=".$group_id);
					$result['status']= 1;
					ajax_return($result);
				}
				else
				{
					$result['status']= 0;
					ajax_return($result);
				}
			}
			else
			{
				//已加入小组
				$result['status']= 0;
				ajax_return($result);
			}
		}
		else
		{
			//组长不用加入
			$result['status']= 0;
			ajax_return($result);
		}
	}
	
	public function exitgroup()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		$group_id = intval($_REQUEST['id']);
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$group_id);
		if($group['user_id']!=$user_id)
		{
			if($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id)>0)
			{
				$GLOBALS['db']->query("delete from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id);
				if($GLOBALS['db']->affected_rows()>0)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count - 1 where id=".$group_id);
					$result['status']= 1;
					ajax_return($result);
				}
				else
				{
					$result['status']= 0;
					ajax_return($result);
				}
			}
			else
			{
				//未加入小组
				$result['status']= 0;
				ajax_return($result);
			}
		}
		else
		{
			//组长不能退出
			$result['status']= 0;
			ajax_return($result);
		}
	}
	
	public function create()
	{
		$user_id =intval($GLOBALS['user_info']['id']);
		
		if($user_id==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
		
		$title = "申请创建小组";
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("shop", "group"));
		$site_nav[] = array('name'=>$title,'url'=>url("shop", "group#create"));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		
		$cate_list=  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group_cate where is_effect = 1 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->display("group_create.html");
	}
	
	public function do_create_group()
	{
		$user_id =intval($GLOBALS['user_info']['id']);
		
		if($user_id==0)
		{
			$result['status'] = 0;
			ajax_return($result);
		}
		
		$cate_id = intval($_REQUEST['cate_id']);
		$name = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$memo = addslashes(htmlspecialchars(trim($_REQUEST['memo'])));
		
		$group['name'] = $name;
		$group['memo'] = $memo;
		$group['cate_id'] = $cate_id;
		$group['user_id'] = $user_id;
		$group['create_time'] = get_gmtime();
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_group",$group);
		$group_id = intval($GLOBALS['db']->insert_id());
		if($group_id>0)
		{			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."user_auth where user_id = ".$user_id." and m_name = 'group' and rel_id = ".$group_id);
			
			//为组长加权限
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "del";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "replydel";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "settop";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "setbest";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "setmemo";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
			
			$result['status'] = 1;
			$result['url'] = url("shop","group");
			ajax_return($result);
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = "申请失败";
			ajax_return($result);
		}
	}
}
?>