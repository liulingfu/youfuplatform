<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class spaceModule extends ShopBaseModule
{
	private $space_user;
	public function init()
	{
			$id = intval($_REQUEST['id']);
			$this->space_user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id." and is_effect=  1 and is_delete = 0");
			
			$user_id = intval($GLOBALS['user_info']['id']);
			if(!($this->space_user))
			{
				showErr($GLOBALS['lang']['USER_NOT_EXISTS']);
			}
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$this->space_user['id']);
			if($focus_data)
			$this->space_user['focused'] = 1;
			
			$region_list = load_auto_cache("cache_region_conf");
			
// 			$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->space_user['province_id']);
			$province_str = $region_list[$this->space_user['province_id']]['name'];
// 			$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->space_user['city_id']);
			$city_str = $region_list[$this->space_user['city_id']]['name'];
			if($province_str.$city_str=='')
			$user_location = $GLOBALS['lang']['LOCATION_NULL'];
			else 
			$user_location = $province_str.$city_str;
			
			$this->space_user['fav_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$this->space_user['id']." and fav_id <> 0");
			$this->space_user['user_location'] = $user_location;
			$GLOBALS['tmpl']->assign("space_user",$this->space_user);
			
			//输出粉丝
			$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$this->space_user['id']." order by rand() limit 5");
			$ids = array(0);
			foreach($fans_list as $k=>$v)
			{
				$ids[] = $v['id'];
			}
			$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id in (".implode(",", $ids).")");			
			foreach($fans_list as $k=>$v)
			{						
				foreach($focus_data as $kk=>$vv)
				{
					if($vv['focused_user_id']==$v['id'])
					{
						$fans_list[$k]['focused'] = 1;
						break;
					}
				}				
			}
			$GLOBALS['tmpl']->assign("rfans_list",$fans_list);	
			
			
			//输出我的关注
			$focus_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$this->space_user['id']." order by rand() limit 5");
			$ids = array(0);
			foreach($focus_list as $k=>$v)
			{
				$ids[] = $v['id'];
			}
			$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id in (".implode(",", $ids).")");
				
			foreach($focus_list as $k=>$v)
			{			
				foreach($focus_data as $kk=>$vv)
				{
					if($vv['focused_user_id']==$v['id'])
					{
						$focus_list[$k]['focused'] = 1;
						break;
					}
				}		
			}
			$GLOBALS['tmpl']->assign("rfocus_list",$focus_list);

			
			
	}
	public function index()
	{			
			$this->init();
			$title = sprintf($GLOBALS['lang']['WHOS_SPACE'],$this->space_user['user_name']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$title,'url'=>url("shop","space", array("id" => $this->space_user['id'])));	
			$site_nav[] = array('name'=>$GLOBALS['lang']['SPACE_HOME'],'url'=>url("shop","space", array("id"=>$this->space_user['id'])));			
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
			
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
			$result = get_topic_list($limit, "user_id = ".$this->space_user['id']);
			//$result['list'] = div_to_col($result['list']);
			
			$GLOBALS['tmpl']->assign("topic_list",$result['list']);
			$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
			$GLOBALS['tmpl']->assign("inc_file","inc/space/space_index.html");
			$GLOBALS['tmpl']->display("space.html");
	}
	
	public function fav()
	{			
			$this->init();
			$title = sprintf($GLOBALS['lang']['WHOS_SPACE'],$this->space_user['user_name']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$title,'url'=>url("shop","space", array("id" => $this->space_user['id'])));	
			$site_nav[] = array('name'=>$GLOBALS['lang']['SPACE_FAV'],'url'=>url("shop","space#fav", array("id"=>$this->space_user['id'])));			
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
			
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

			$result = get_topic_list($limit, "user_id = ".$this->space_user['id']." and fav_id <> 0");
			//$result['list'] = div_to_col($result['list']);
			
			$GLOBALS['tmpl']->assign("topic_list",$result['list']);
			$page = new Page($result['total'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			$GLOBALS['tmpl']->assign("inc_file","inc/space/space_index.html");
			$GLOBALS['tmpl']->display("space.html");
	}
	
	public function fans()
	{
		$this->init();
			$title = sprintf($GLOBALS['lang']['WHOS_SPACE'],$this->space_user['user_name']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$title,'url'=>url("shop","space", array("id"=>$this->space_user['id'])));	
			$site_nav[] = array('name'=>$GLOBALS['lang']['SPACE_FANS'],'url'=>url("shop","space#fans", array("id"=>$this->space_user['id'])));		
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
			
		$page_size = 25;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($this->space_user['id']);
		
		//输出粉丝
		$fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id." order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ".$user_id);
		
		$ids = array(0);
		foreach($fans_list as $k=>$v)
		{
			$ids[] = $v['id'];
		}
		$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".intval($GLOBALS['user_info']['id'])." and focused_user_id in (".implode(",", $ids).")");
		foreach($fans_list as $k=>$v)
		{
			foreach($focus_data as $kk=>$vv)
			{
				if($vv['focused_user_id']==$v['id'])
				{
					$fans_list[$k]['focused'] = 1;
					break;
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("fans_list",$fans_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/space/space_fans.html");
		$GLOBALS['tmpl']->display("space.html");
	}
	
	
	public function focus()
	{
		$this->init();
			$title = sprintf($GLOBALS['lang']['WHOS_SPACE'],$this->space_user['user_name']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$title,'url'=>url("shop","space", array("id"=> $this->space_user['id'])));	
			$site_nav[] = array('name'=>$GLOBALS['lang']['SPACE_FOCUS'],'url'=>url("shop","space#focus",array("id"=> $this->space_user['id'])));		
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
			
		$page_size = 25;
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = intval($this->space_user['id']);
		
		//输出粉丝
		$focus_list = $GLOBALS['db']->getAll("select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." order by id desc limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id);
		
		
		$ids = array(0);
		foreach($focus_list as $k=>$v)
		{
			$ids[] = $v['id'];
		}
		$focus_data =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_focus where focus_user_id = ".intval($GLOBALS['user_info']['id'])." and focused_user_id in (".implode(",", $ids).")");
		
		foreach($focus_list as $k=>$v)
		{
			foreach($focus_data as $kk=>$vv)
			{
				if($vv['focused_user_id']==$v['id'])
				{
					$focus_list[$k]['focused'] = 1;
					break;
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("focus_list",$focus_list);	

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/space/space_focus.html");
		$GLOBALS['tmpl']->display("space.html");
	}
	

}
?>