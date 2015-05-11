<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class loginModule extends BizBaseModule
{
	public function index()
	{				
		global $tmpl;
		$s_account_info = es_session::get("account_info");
		if(intval($s_account_info['id'])!=0)
		{
			$account_data = $GLOBALS['db']->getRow("select s.name as name,a.account_name as account_name,a.login_ip as login_ip ,a.login_time as login_time ,a.update_time as create_time, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".intval($s_account_info['id']));
			if($account_data)
			{
				app_redirect(url("biz","index"));
			}
			else
			{
				$GLOBALS['tmpl']->display("biz/biz_login.html");
			}			
		}
		else
		{
			$GLOBALS['tmpl']->display("biz/biz_login.html");
		}
	}
	
	public function dologin()
	{
		if(check_ipop_limit(get_client_ip(),"supplier_dologin",intval(app_conf("SUBMIT_DELAY"))))
		{
			$account_name = htmlspecialchars(addslashes(trim($_REQUEST['account_name'])));
			$account_password = htmlspecialchars(addslashes(trim($_REQUEST['account_password'])));
			$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and account_password = '".md5($account_password)."' and is_effect = 1 and is_delete = 0");
			if($account)
			{
				if(intval($_REQUEST['save_password'])==1)
				{
					es_cookie::set("sp_account_name",$account_name,3600*24*30);
					es_cookie::set("sp_account_password",md5($account_password),3600*24*30);			
				}
			
				$account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$account['id']);
				$account_location_ids = array(0);
				foreach($account_locations as $row)
				{
					$account_location_ids[] = $row['location_id'];	
				}
				$account['location_ids'] =  $account_location_ids;
				es_session::set("account_info",$account);
				$result['status'] = 1;
				$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set login_time = ".get_gmtime().",login_ip = '".get_client_ip()."' where id = ".$account['id']);
				ajax_return($result);
			}
			else
			{
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['SUPPLIER_LOGIN_FAILED'];
				ajax_return($result);
			}
		}
		else
		{
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
			ajax_return($result);
		}
	}
	
	public function loginout()
	{
		es_cookie::delete("sp_account_name");
		es_cookie::delete("sp_account_password");
		es_session::delete("account_info");
		app_redirect(url("biz","index"));
	}
}
?>