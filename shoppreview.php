<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/shop_init.php';
function insert_load_goods_time_status()
{
			//获取当前页的团购商品
			$id = intval($_REQUEST['id']);
			$uname = addslashes(trim($_REQUEST['id']));
			
			if($id==0&&$uname=='')
			{
				app_redirect(url("shop","index"));
			}
			elseif($id==0&&$uname!='')
			{
				$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
			}
			//获取当前页的团购商品
			
			$goods = get_goods($id);
			$GLOBALS['tmpl']->assign("goods",$goods);
			return $GLOBALS['tmpl']->fetch("inc/goods_detail_time_status.html");
}

//后台管理员验证
$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));
$adm_name = $adm_session['adm_name'];
$adm_id = intval($adm_session['adm_id']);
if($adm_id == 0)
{
	app_redirect(APP_ROOT."/admin.php?m=Public&a=login");
}		

//输出导航菜单
		$nav_list = get_shop_nav_list();

		foreach($nav_list as $k=>$v)
		{
			if($v['url']!='')
			{
				if(substr($v['url'],0,7)!="http://")
				{		
					//开始分析url
					$nav_list[$k]['url'] = APP_ROOT."/".$v['url'];
				}
			}
			else
			{
				$route = $v['u_module'];
				if($v['u_action']!='')
				$route.="#".$v['u_action'];
	
				
				if(in_array($v['u_module'],array("deal","deals","order","second")))
				$app_index = "tuan";
				else if($v['u_module']=="index")
				$app_index = "index";
				else
				$app_index = "shop";
				
				$str = "u:".$app_index."|".$route."|".$v['u_param'];
				
				$nav_list[$k]['url'] =  parse_url_tag($str);
				
				if(MODULE_NAME==$v['u_module'])
				{
					if($v['u_id']==0)
					{
						$nav_list[$k]['current'] = 1;
					}
					else 
					{
						if($v['u_id']==intval($_REQUEST['id']))
						{
							$nav_list[$k]['current'] = 1;
						}
						
					}
				}
				elseif($v['u_module']=='article')
				{
					if(MODULE_NAME=="sys"||MODULE_NAME=="article"||MODULE_NAME=="help"||MODULE_NAME=="notice")
					{
						if($v['u_id']==0)
						{
							$nav_list[$k]['current'] = 1;
						}
						else 
						{
							if($v['u_id']==intval($_REQUEST['id']))
							{
								$nav_list[$k]['current'] = 1;
							}
							
						}
					}
				}
					
			}
		}
		$GLOBALS['tmpl']->assign("nav_list",$nav_list);
		
		$now = get_gmtime();
		$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
		$GLOBALS['tmpl']->assign("vote",$vote);
		
		//输出在线客服与时间
		$qq = explode("|",app_conf("ONLINE_QQ"));
		$msn = explode("|",app_conf("ONLINE_MSN"));
		$GLOBALS['tmpl']->assign("online_qq",$qq);
		$GLOBALS['tmpl']->assign("online_msn",$msn);
		
		//输出页面的标题关键词与描述
		$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
		
		//输出系统文章
		$system_article = get_article_list(8,0,"ac.type_id = 3","",true);
		$GLOBALS['tmpl']->assign("system_article",$system_article['list']);
		
		//输出帮助
		$deal_help = get_help();
		$GLOBALS['tmpl']->assign("deal_help",$deal_help);
		
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
		$GLOBALS['tmpl']->assign("f_link_data",$f_link_group);
		
		//输出热门关键词
		$hot_kw = app_conf("SHOP_SEARCH_KEYWORD");
		$hot_kw = preg_split("/[ ,]/i",$hot_kw);
		$GLOBALS['tmpl']->assign("hot_kw",$hot_kw);
		
		//输出商城分类
		$cate_tree = get_cate_tree();
		$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);
		
		//获取当前页的团购商品
		$id = intval($_REQUEST['id']);
		$uname = addslashes(trim($_REQUEST['id']));
		
		if($id==0&&$uname=='')
		{
			app_redirect(APP_ROOT."/");
		}
		elseif($id==0&&$uname!='')
		{
			$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
		}
		//获取当前页的团购商品
		
		$goods = get_goods($id,1);


		if(!$goods||$goods['buy_type']==1)
		{
			//输出规格库存的配置
		$attr_stock = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."attr_stock where deal_id = ".$goods['id']." order by id asc");
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$GLOBALS['tmpl']->assign("attr_cfg_json",$attr_cfg_json);	
		$GLOBALS['tmpl']->assign("attr_stock_json",$attr_stock_json);
		
		
		$GLOBALS['tmpl']->assign("goods",$goods);
		
		//开始输出当前的site_nav
		$cates = array();
		$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where id = ".$goods['shop_cate_id']);
		do
		{
			$cates[] = $cate;
			$pid = intval($cate['pid']);
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where is_effect =1 and is_delete =0 and id = ".$pid);			
			
		}while($pid!=0);

		$page_title = substr($page_title,0,-3);
		krsort($cates);
		
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
		
		if($cates)
		{
			foreach($cates as $cate_row)
			{
				if($cate_row['uname']!='')
				$curl = url("shop","score",array("id"=>$cate_row['uname']));
				else
				$curl = url("shop","score",array("id"=>$cate_row['id']));
				
				$site_nav[] = array('name'=>$cate_row['name'],'url'=>$curl);
			}
		}		
		
		if($goods['uname']!='')
		$eurl = url("shop","exchange",array("id"=>$goods['uname']));
		else
		$eurl = url("shop","exchange",array("id"=>$goods['id']));
		
		$site_nav[] = array('name'=>$goods['name'],'url'=>$eurl);
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		//输出当前的site_nav
		
		$seo_title = $goods['seo_title']!=''?$goods['seo_title']:$goods['name'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $goods['seo_keyword']!=''?$goods['seo_keyword']:$goods['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $goods['seo_description']!=''?$goods['seo_description']:$goods['name'];
		$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
		
		$GLOBALS['tmpl']->display("score_info.html");
		}
		else
		{
		
			
			//输出规格库存的配置
			$attr_stock = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."attr_stock where deal_id = ".$goods['id']." order by id asc");
			$attr_cfg_json = "{";
			$attr_stock_json = "{";
			
			foreach($attr_stock as $k=>$v)
			{
				$attr_cfg_json.=$k.":"."{";
				$attr_stock_json.=$k.":"."{";
				foreach($v as $key=>$vvv)
				{
					if($key!='attr_cfg')
					$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
				}
				$attr_stock_json = substr($attr_stock_json,0,-1);
				$attr_stock_json.="},";	
				
				$attr_cfg_data = unserialize($v['attr_cfg']);	
				foreach($attr_cfg_data as $attr_id=>$vv)
				{
					$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
				}	
				$attr_cfg_json = substr($attr_cfg_json,0,-1);
				$attr_cfg_json.="},";		
			}
			if($attr_stock)
			{
				$attr_cfg_json = substr($attr_cfg_json,0,-1);
				$attr_stock_json = substr($attr_stock_json,0,-1);
			}
			
			$attr_cfg_json .= "}";
			$attr_stock_json .= "}";
			
			
			$GLOBALS['tmpl']->assign("attr_cfg_json",$attr_cfg_json);	
			$GLOBALS['tmpl']->assign("attr_stock_json",$attr_stock_json);
			
			$buy_comment = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table = 'deal' and rel_id = ".$goods['id']." and is_buy = 1");
			$good_comment = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where rel_table = 'deal' and rel_id = ".$goods['id']." and is_buy = 1 and point = 5");
			$percent_comment = round($good_comment/$buy_comment*100);
			$GLOBALS['tmpl']->assign("buy_comment",$buy_comment);
			$GLOBALS['tmpl']->assign("good_comment",$good_comment);
			$GLOBALS['tmpl']->assign("percent_comment",$percent_comment);
			
			$GLOBALS['tmpl']->assign("goods",$goods);
			
			
			//开始输出当前的site_nav
			$cates = array();
			$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where id = ".$goods['shop_cate_id']);
			do
			{
				$cates[] = $cate;
				$pid = intval($cate['pid']);
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."shop_cate where is_effect =1 and is_delete =0 and id = ".$pid);			
				
			}while($pid!=0);
	
			$page_title = substr($page_title,0,-3);
			krsort($cates);
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			
			if($cates)
			{
				foreach($cates as $cate_row)
				{
					if($cate_row['uname']!='')
					$curl = url("shop","cate",array("id"=>$cate_row['uname']));
					else
					$curl = url("shop","cate",array("id"=>$cate_row['id']));
					
					$site_nav[] = array('name'=>$cate_row['name'],'url'=>$curl);
				}
			}		
			if($goods['uname']!='')
			$gurl = url("shop","goods",array("id"=>$goods['uname']));
			else
			$gurl = url("shop","goods",array("id"=>$goods['id']));
					
			$site_nav[] = array('name'=>$goods['name'],'url'=>$gurl);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
			
			$seo_title = $goods['seo_title']!=''?$goods['seo_title']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $goods['seo_keyword']!=''?$goods['seo_keyword']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $goods['seo_description']!=''?$goods['seo_description']:$goods['name'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
			if(!$GLOBALS['user_info'])
			{
				$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
			}
			
			$GLOBALS['tmpl']->display("goods_info.html");
		}
?>