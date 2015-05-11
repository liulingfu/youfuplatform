<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_inviteModule extends ShopBaseModule
{
	public function index()
	{
		 
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_invite_list($limit,$GLOBALS['user_info']['id']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$total_referral_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."referrals where user_id = ".$GLOBALS['user_info']['id']." and pay_time > 0");
		$total_referral_score = $GLOBALS['db']->getOne("select sum(score) from ".DB_PREFIX."referrals where user_id = ".$GLOBALS['user_info']['id']." and pay_time > 0");
		
		$GLOBALS['tmpl']->assign("total_referral_money",$total_referral_money);
		$GLOBALS['tmpl']->assign("total_referral_score",$total_referral_score);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_INVITE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_index.html");
			
		$share_url = get_domain().APP_ROOT."/";
		if($GLOBALS['user_info'])
		$share_url .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
		$GLOBALS['tmpl']->assign("share_url",$share_url);		
				
		$GLOBALS['tmpl']->display("uc.html");
	}
	
	
}
?>