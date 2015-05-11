<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
define("PAGE_SIZE",80);
define("SECTOR",10);
class discoverModule extends ShopBaseModule
{
	public function index()
	{					
		convert_req($_REQUEST);	
		$title = $GLOBALS['lang']['DISCOVER'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		$site_nav[] = array('name'=>$title,'url'=>url("shop", "discover"));
			
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
		
						
		$cid = intval($_REQUEST['cid']);
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."topic_tag_cate where id = ".$cid);
		$GLOBALS['tmpl']->assign("cid",$cid);
		
		$tag = addslashes(htmlspecialchars(trim($_REQUEST['tag'])));
		$GLOBALS['tmpl']->assign("tag",$tag);
		
		
		if($cate_name)
		$title = $title.$cate_name;
		
		if($tag)
		$title = $title.$tag;
		
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");

		if($cid==0)
		$tag_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag where is_recommend = 1 order by sort desc limit 10");
		else 
		$tag_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag as t left join ".DB_PREFIX."topic_tag_cate_link as l on l.tag_id = t.id where l.cate_id = ".$cid." order by t.sort desc limit 10");
		$GLOBALS['tmpl']->assign("tag_list",$tag_list);
		
		
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag_cate where showin_web = 1 order by sort desc limit 7");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);	

		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;		
		$GLOBALS['tmpl']->assign("page",$page);
		
		if($cid>0)
		{			
			if($cate_name)
			{			
				$unicode_cate_name = str_to_unicode_string($cate_name);
				$condition.=" and match(cate_match) against('".$unicode_cate_name."'  IN BOOLEAN MODE) ";
			}
		}
		if($tag!="")
		{
			$unicode_tag = str_to_unicode_string($tag);
			$condition.=" and match(keyword_match) against('".$unicode_tag."'  IN BOOLEAN MODE) ";
		}
		$sql = "select count(*) from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 and fav_id = 0 and relay_id = 0 and type in ('share','sharetuan','sharegoods','sharefyouhui','sharebyouhui','shareevent')  ".$condition;
		
		$count = $GLOBALS['db']->getOne($sql);		
		$page_size = PAGE_SIZE;
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$remain_count = $count-($page-1)*$page_size;  //从当前页算起剩余的数量
		$remain_page = ceil($remain_count/$page_size); //剩余的页数
		if($remain_page == 1)
		{
			//末页
			$step_size = ceil($remain_count/SECTOR);
		}
		else
		{
			$step_size = ceil(PAGE_SIZE/SECTOR);
		}
		$GLOBALS['tmpl']->assign('step_size',$step_size);
		$GLOBALS['tmpl']->display("discover_index.html");
	}	
	
	public function load_topic()
	{
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$cid = intval($_REQUEST['cid']);
		$tag = addslashes(htmlspecialchars(trim($_REQUEST['tag'])));
		$page = intval($_REQUEST['page']);
		if($page==0)$page = 1;	
		$step = intval($_REQUEST['step']);		
		$step_size = intval($_REQUEST['step_size']);		
		$limit = (($page - 1)*PAGE_SIZE + ($step - 1)*SECTOR).",".SECTOR;		
		if($step==0||$step>$step_size)
		{
			//超出
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			ajax_return($result);
		}
		
		if($cid>0)
		{
			$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."topic_tag_cate where id = ".$cid);
			if($cate_name)
			{			
				$unicode_cate_name = str_to_unicode_string($cate_name);
				$condition.=" and match(cate_match) against('".$unicode_cate_name."'  IN BOOLEAN MODE) ";
			}
		}
		if($tag!="")
		{
			$unicode_tag = str_to_unicode_string($tag);
			$condition.=" and match(keyword_match) against('".$unicode_tag."'  IN BOOLEAN MODE) ";
		}
		$sql = "select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0  and fav_id = 0 and relay_id = 0 and type in ('share','sharetuan','sharegoods','sharefyouhui','sharebyouhui','shareevent') ".$condition." order by create_time desc limit ".$limit;	
		$result_list = $GLOBALS['db']->getAll($sql);
		
		
		if($result_list)
		{
			$result['doms'] = array();
			foreach($result_list as $k=>$v)
			{
				$topic = get_topic_item($v);
				if(msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['content']))
				$topic['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50);
				else
				$topic['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['content']);
				
				if($topic['origin'])
				{
					if(msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['origin']['content']))
					$topic['origin']['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50);
					else
					$topic['origin']['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['origin']['content']);
				}
				$GLOBALS['tmpl']->assign("message_item",$topic);
				$result['doms'][] = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/discover_item.html"));
			}		
			
			if($step==0||$step>=$step_size)
			{
				//超出
				$result['step'] = 0;
				$result['status'] = 0;
				$result['info'] = 'end';
				ajax_return($result);
			}
			else
			{
				$result['status'] = 1;
				$result['step'] = $step + 1;
				$result['info'] = 'next';
				ajax_return($result);
			}
			
		}
		else
		{
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
//			$result['sql'] = $sql;
			ajax_return($result);
		}		
	}
	
}
?>