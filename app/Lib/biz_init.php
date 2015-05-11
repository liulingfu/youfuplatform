<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

//用于商家部分的初始化
require_once 'app_init.php';
$IMG_APP_ROOT = APP_ROOT;
define("APP_INDEX","biz");
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_caches/',0777);
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/'))  //商家系统使用商城系统的模板
	mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/',0777);   //商家系统使用商城系统的模板
$GLOBALS['tmpl']->cache_dir      = APP_ROOT_PATH . 'public/runtime/app/tpl_caches';
$GLOBALS['tmpl']->compile_dir    = APP_ROOT_PATH . 'public/runtime/app/tpl_compiled';   //商家系统使用商城系统的模板
$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH . 'app/Tpl/' . app_conf("TEMPLATE");   //商家系统使用商城系统的模板
$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH."app/Tpl/".app_conf("TEMPLATE")); 
//定义当前语言包
$GLOBALS['tmpl']->assign("LANG",$lang);
//定义模板路径
$tmpl_path = get_domain().APP_ROOT."/app/Tpl/";
$GLOBALS['tmpl']->assign("TMPL",$tmpl_path.app_conf("TEMPLATE"));   //优惠券系统使用商城系统的模板
$GLOBALS['tmpl']->assign("APP_INDEX",APP_INDEX);
$account_name =  htmlspecialchars(trim(addslashes(es_cookie::get("sp_account_name"))));
$account_password =  htmlspecialchars(trim(addslashes(es_cookie::get("sp_account_password"))));
$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and account_password = '".$account_password."' and is_effect = 1 and is_delete = 0");
if($account)
{
				$account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$account['id']);
				$account_location_ids = array(0);
				foreach($account_locations as $row)
				{
					$account_location_ids[] = $row['location_id'];	
				}
				$account['location_ids'] =  $account_location_ids;
				es_session::set("account_info",$account);
}

$top10 = $GLOBALS['cache']->get("STORE_TOP10_IN_BIZ");
if($top10===false)
{
	$top10 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location  use index(search_idx6) where is_verify = 1 and is_effect = 1 order by good_rate desc limit 10");
	$GLOBALS['cache']->set("STORE_TOP10_IN_BIZ",$top10,3600);
}
$GLOBALS['tmpl']->assign("top10",$top10);


//输出管理大菜单
$biz_nav[] = array("name"=>"首页","url"=>url("biz","index"),"ctl"=>"index");   //首页
$biz_nav[] = array("name"=>"资料修改","url"=>url("biz","profile"),"ctl"=>"profile");   //修改门店资料
$biz_nav[] = array("name"=>"店铺管理","url"=>url("biz","publish"),"ctl"=>"publish");   //上传图片以及发布公告
$biz_nav[] = array("name"=>"订单","url"=>url("biz","order"),"ctl"=>"order");    //管理相关用户的购买订单
$biz_nav[] = array("name"=>"产品","url"=>url("biz","tuan"),"ctl"=>"tuan");   //查看相关的团购列表，以及相应的团购券列表
$biz_nav[] = array("name"=>"优惠券","url"=>url("biz","youhui"),"ctl"=>"youhui");    //查看优惠券的下载记录
$biz_nav[] = array("name"=>"活动","url"=>url("biz","event"),"ctl"=>"event");    //查看活动的报名情况
$biz_nav[] = array("name"=>"验证","url"=>url("biz","verify"),"ctl"=>"verify");    //验证团购券，消费券，优惠券
$biz_nav[] = array("name"=>"点评","url"=>url("biz","dp"),"ctl"=>"dp");    //点评的查看与回复
$biz_nav[] = array("name"=>"结算","url"=>url("biz","balance"),"ctl"=>"balance");    //结算

if(app_conf("SHOP_OPEN")==0)
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SHOP_CLOSE']);
	$GLOBALS['tmpl']->assign("html",app_conf("SHOP_CLOSE_HTML"));
	$GLOBALS['tmpl']->display("shop_close.html");
	exit;
}
?>