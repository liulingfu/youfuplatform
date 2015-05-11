<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class darenModule extends ShopBaseModule
{
	public function index()
	{
					
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);
		if (!$GLOBALS['tmpl']->is_cached('daren_index.html', $cache_id))
		{
				$title = $GLOBALS['lang']['DAREN_SHOW'];
				$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
				$site_nav[] = array('name'=>$title,'url'=>url("shop", "daren"));
				
				$GLOBALS['tmpl']->assign("site_nav",$site_nav);
				
				$GLOBALS['tmpl']->assign("page_title",$title);
				$GLOBALS['tmpl']->assign("page_keyword",$title.",");
				$GLOBALS['tmpl']->assign("page_description",$title.",");
							
				
			
					$user_id = intval($GLOBALS['user_info']['id']);			
					//输出所有达人推荐
					$rnd_daren_list = get_rand_user(42,1);
					$GLOBALS['tmpl']->assign("rnd_daren_list",$rnd_daren_list);	
					
					$daren_list = get_rand_user(8,1);			
					foreach($daren_list as $kk=>$vv)
					{			
							$focus_uid = intval($vv['id']);
							$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							if($focus_data)
							$daren_list[$kk]['focused'] = 1;
					}
					$GLOBALS['tmpl']->assign("daren_list",$daren_list);		
					
					$topic_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic as t where t.is_effect = 1 and t.is_delete = 0 and t.is_recommend = 1 order by t.create_time desc limit 6");
					foreach($topic_list as $kk=>$vv)
					{
							$vv['content'] = msubstr($vv['content'],0,30);
							$topic_list[$kk] = get_topic_item($vv);
							$topic_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$vv['user_id']." and is_effect =1 and is_delete = 0");
							$focus_uid = intval($topic_user_info['id']);
							$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							if($focus_data)
							$topic_user_info['focused'] = 1;
							$topic_list[$kk]['user_info'] = $topic_user_info;
					}
					$GLOBALS['tmpl']->assign("topic_list",$topic_list);	
					
					$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag_cate where showin_web = 1 order by sort desc limit 7");
					foreach($cate_list as $k=>$v)
					{
						//获取分类下的推荐分享
						$cate_list[$k]['topic_list'] = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic as t left join ".DB_PREFIX."topic_cate_link as l on l.topic_id = t.id where l.cate_id = ".$v['id']." and t.is_effect = 1 and t.is_delete = 0 and t.is_recommend = 1 order by t.create_time desc limit 4");
						foreach($cate_list[$k]['topic_list'] as $kk=>$vv)
						{
							$vv['content'] = msubstr($vv['content'],0,30);
							$cate_list[$k]['topic_list'][$kk] = get_topic_item($vv);
							$topic_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$vv['user_id']." and is_effect =1 and is_delete = 0");
							$focus_uid = intval($topic_user_info['id']);
							$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							if($focus_data)
							$topic_user_info['focused'] = 1;
							$cate_list[$k]['topic_list'][$kk]['user_info'] = $topic_user_info;
						}
						
						$cate_list[$k]['daren_list'] = $GLOBALS['db']->getAllCached("select u.* from ".DB_PREFIX."user as u left join ".DB_PREFIX."user_cate_link as l on l.user_id = u.id where l.cate_id = ".$v['id']." and u.is_effect = 1 and u.is_delete = 0 and u.is_daren = 1 order by rand() limit 8");				
						foreach($cate_list[$k]['daren_list'] as $kk=>$vv)
						{			
							$focus_uid = intval($vv['id']);
							$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							if($focus_data)
							$cate_list[$k]['daren_list'][$kk]['focused'] = 1;
						}
					}
					
					$GLOBALS['tmpl']->assign("cate_list",$cate_list);
					$daren_html = decode_topic($GLOBALS['tmpl']->fetch("inc/daren_show_item.html"));
			
				$GLOBALS['tmpl']->assign("daren_html", $daren_html);
			}
			$GLOBALS['tmpl']->display("daren_index.html",$cache_id);
	}	
	
	
	public function submit()
	{
					
			$title = $GLOBALS['lang']['DAREN_SUBMIT'];
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			$site_nav[] = array('name'=>$title,'url'=>url("shop", "daren#submit"));
			
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			
			$GLOBALS['tmpl']->display("daren_submit.html");
	}
	
	public function submit_daren()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 0;
			$result['info'] = "请先登录";
			ajax_return($result);
		}
		
		if($GLOBALS['user_info']['is_daren']==1)
		{
			$result['status'] = 2;
			$result['info'] = "您已经是达人了";
			ajax_return($result);
		}
		
		$submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."daren_submit where user_id = ".$user_id);
		if($submit)
		{
			$result['status'] = 2;
			$result['info'] = "您在".to_date($submit['create_time'])."已经提交过申请了";
			ajax_return($result);
		}
		else
		{
			$data['user_id'] = $user_id;
			$data['daren_title'] = addslashes(htmlspecialchars(trim($_REQUEST['daren_title'])));
			$data['reason'] = addslashes(htmlspecialchars(trim($_REQUEST['reason'])));
			$data['create_time'] =get_gmtime();
			$GLOBALS['db']->autoExecute(DB_PREFIX."daren_submit",$data);
			$result['status'] = 1;
			$result['info'] = "申请成功";
			ajax_return($result);
		}
	}
}
?>