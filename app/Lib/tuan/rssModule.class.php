<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/message.php';
require_once APP_ROOT_PATH.'system/libs/rss.php';
require_once APP_ROOT_PATH.'app/Lib/side.php';


class rssModule extends TuanBaseModule
{
	public function index()
	{				
		$rss = new UniversalFeedCreator();   
		 $rss->useCached(); // use cached version if age<1 hour  
		 $rss->title = app_conf("SHOP_TITLE")." - ".app_conf("SHOP_SEO_TITLE");   
		 $rss->description = app_conf("SHOP_SEO_TITLE"); 
		   
		 //optional  
		 $rss->descriptionTruncSize = 500;  
		 $rss->descriptionHtmlSyndicated = true;  
		   
		 $rss->link = get_domain().APP_ROOT;   
		 $rss->syndicationURL = get_domain().APP_ROOT;   
		   
		
		   
		 //optional  
		 $image->descriptionTruncSize = 500;  
		 $image->descriptionHtmlSyndicated = true;  
		   
		
		 $domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().$GLOBALS['IMG_APP_ROOT']:app_conf("PUBLIC_DOMAIN_ROOT");
		 	
		        
		        
		 $city = get_current_deal_city();
		 $city_id = $city['id'];
		 $deal_list = get_deal_list(app_conf("DEAL_PAGE_SIZE"),0,0,array(DEAL_ONLINE)," buy_type <> 1 or ( is_shop = 1 and is_effect =1 and is_delete = 0 and buy_type <> 1)");
		 $deal_list = $deal_list['list'];
		
		 foreach($deal_list as $data) {   
		     $item = new FeedItem();   
		     if($data['uname']!='')
		     $gurl = url("shop","goods",array("id"=>$data['uname']));
		     else
		     $gurl = url("shop","goods",array("id"=>$data['id']));
		     $data['url'] = $gurl;
		     $item->title = msubstr($data['name'],0,30);   
		     $item->link = get_domain().$data['url'];  
		
		     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
		     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
		        
		     $data['img'] = str_replace("./public/",$domain."/public/",$data['img']);
		     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
		       
		     //optional  
		     $item->descriptionTruncSize = 500;  
		     $item->descriptionHtmlSyndicated = true;  
		
		     if($data['end_time']!=0)
		     $item->date = date('r',$data['end_time']);   
		     $item->source = $data['url'];   
		     $item->author = app_conf("SHOP_TITLE");   
		  
		        
		     $rss->addItem($item);   
		 }
		 
		 
		 $rss->saveFeed($format="RSS0.91", $filename=APP_ROOT_PATH."public/runtime/app/tpl_caches/rss.xml");	
	}
}
?>