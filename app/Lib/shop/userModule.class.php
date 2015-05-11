<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class userModule extends ShopBaseModule
{
	public function register()
	{		
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_register.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
			
			$field_list =load_auto_cache("user_field_list");
		
			$api_uinfo = es_session::get("api_user_info");
			$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
			
			$GLOBALS['tmpl']->assign("field_list",$field_list);
		}
		$GLOBALS['tmpl']->display("user_register.html",$cache_id);
	}
	
	public function doregister()
	{
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],0,url("shop","user#register"));
			}
		}
        
		//ip限制
		$ip=get_client_ip();
	    $ip_nums=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where login_ip = '".$ip."'");
	    if($ip_nums>app_conf("IP_LIMIT_NUM"))
	    {
	    	showErr($GLOBALS['lang']['IP_LIMIT_ERROR'],0,url("shop","user#register"));
	    }
	    
		//print_r($ip_limit_nums);die();
		require_once APP_ROOT_PATH."system/libs/user.php";
		$user_data = $_POST;
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		
		if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
		{
			showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
		}
		if(trim($user_data['user_pwd'])=='')
		{
			showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		
		$user_data['pid'] = $GLOBALS['ref_uid'];
		
		
		$res = save_user($user_data);
	
		if($_REQUEST['subscribe']==1)
		{
			//订阅
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address = '".$user_data['email']."'")==0)
			{
				$mail_item['city_id'] = intval($_REQUEST['city_id']);
				$mail_item['mail_address'] = $user_data['email'];
				$mail_item['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item,'INSERT','','SILENT');
			}
			if($user_data['mobile']!=''&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_list where mobile = '".$user_data['mobile']."'")==0)
			{
				$mobile['city_id'] = intval($_REQUEST['city_id']);
				$mobile['mobile'] = $user_data['mobile'];
				$mobile['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile,'INSERT','','SILENT');
			}
		}
		if($res['status'] == 1)
		{
			$user_id = intval($res['data']);
			//更新来路
			$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
			if($user_info['is_effect']==1)
			{
				//在此自动登录
				do_login_user($user_data['email'],$user_data['user_pwd']);
				//原来为直接挑战 现改为 完善资料
				showSuccess($GLOBALS['lang']['REGISTER_SUCCESS'],0,APP_ROOT."/");
				//app_redirect(url("shop","user#stepone"));
			}
			else
			{
				if(app_conf("MAIL_ON")==1)
				{
					//发邮件
					send_user_verify_mail($user_id);
					$user_email = $GLOBALS['db']->getOne("select email from ".DB_PREFIX."user where id =".$user_id);
					//开始关于跳转地址的解析
					$domain = explode("@",$user_email);
					$domain = $domain[1];
					$gocheck_url = '';
					switch($domain)
					{
						case '163.com':
							$gocheck_url = 'http://mail.163.com';
							break;
						case '126.com':
							$gocheck_url = 'http://www.126.com';
							break;
						case 'sina.com':
							$gocheck_url = 'http://mail.sina.com';
							break;
						case 'sina.com.cn':
							$gocheck_url = 'http://mail.sina.com.cn';
							break;
						case 'sina.cn':
							$gocheck_url = 'http://mail.sina.cn';
							break;
						case 'qq.com':
							$gocheck_url = 'http://mail.qq.com';
							break;
						case 'foxmail.com':
							$gocheck_url = 'http://mail.foxmail.com';
							break;
						case 'gmail.com':
							$gocheck_url = 'http://www.gmail.com';
							break;
						case 'yahoo.com':
							$gocheck_url = 'http://mail.yahoo.com';
							break;
						case 'yahoo.com.cn':
							$gocheck_url = 'http://mail.cn.yahoo.com';
							break;
						case 'hotmail.com':
							$gocheck_url = 'http://www.hotmail.com';
							break;
						default:
							$gocheck_url = "";
							break;					
					}

					 
					$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['REGISTER_MAIL_SEND_SUCCESS']);
					$GLOBALS['tmpl']->assign("user_email",$user_email);
					$GLOBALS['tmpl']->assign("gocheck_url",$gocheck_url);
					//end 
					$GLOBALS['tmpl']->display("user_register_email.html");
				}
				else
				showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
			}
		}
		else
		{
			$error = $res['data'];		
			if(!$error['field_show_name'])
			{
					$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
			}
			if($error['error']==EMPTY_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==FORMAT_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==EXIST_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
			}
			showErr($error_msg);
		}
	}
	
	public function login()
	{
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
				
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_login.html', $cache_id))	
		{
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_LOGIN']);
			$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER']);
			 
		}
		$GLOBALS['tmpl']->display("user_login.html",$cache_id);
	}
	public function api_login()
	{		
		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$s_api_user_info['name'].$GLOBALS['lang']['HELLO'].",".$GLOBALS['lang']['USER_LOGIN_BIND']);
			$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER_BIND']);
			$GLOBALS['tmpl']->assign("api_callback",true);
			$GLOBALS['tmpl']->display("user_login.html");
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}	
	public function dologin()
	{
		if(!$_POST)
		{
			app_redirect(APP_ROOT."/");
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$ajax = intval($_REQUEST['ajax']);
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax,url("shop","user#login"));
			}
		}
		
		require_once APP_ROOT_PATH."system/libs/user.php";
		if(check_ipop_limit(get_client_ip(),"user_dologin",intval(app_conf("SUBMIT_DELAY"))))
		$result = do_login_user($_POST['email'],$_POST['user_pwd']);
		else
		showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],$ajax,url("shop","user#login"));
		if($result['status'])
		{	
			$s_user_info = es_session::get("user_info");
			//更新购物车
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($s_user_info['id'])." where session_id = '".es_session::id()."'");
			if(intval($_POST['auto_login'])==1)
			{
				//自动登录，保存cookie
				$user_data = $s_user_info;
				es_cookie::set("user_name",$user_data['email'],3600*24*30);			
				es_cookie::set("user_pwd",md5($user_data['user_pwd']."_EASE_COOKIE"),3600*24*30);
			}
			if($ajax==0&&trim(app_conf("INTEGRATE_CODE"))=='')
			{
				$redirect = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
				app_redirect($redirect);
			}
			else
			{			
				$jump_url = get_gopreview();
				if($ajax==1)
				{
					$return['status'] = 1;
					$return['info'] = $GLOBALS['lang']['LOGIN_SUCCESS'];
					$return['data'] = $result['msg'];
					$return['jump'] = $jump_url;
					ajax_return($return);
				}
				else
				{
					$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);					
					showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],$ajax,$jump_url);
				}
			}
		}
		else
		{
			if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
			{
				$err = $GLOBALS['lang']['USER_NOT_EXIST'];
			}
			if($result['data'] == ACCOUNT_PASSWORD_ERROR)
			{
				$err = $GLOBALS['lang']['PASSWORD_ERROR'];
			}
			if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
			{
				$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
				if(app_conf("MAIL_ON")==1&&$ajax==0)
				{				
					$GLOBALS['tmpl']->assign("page_title",$err);
					$GLOBALS['tmpl']->assign("user_info",$result['user']);
					$GLOBALS['tmpl']->display("verify_user.html");
					exit;
				}
				
			}
			showErr($err,$ajax);
		}
	}
	
	
	
	public function stepone(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
		//扩展字段
		$field_list =load_auto_cache("user_field_list");
		
		foreach($field_list as $k=>$v)
		{
			$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$GLOBALS['user_info']['id']." and field_id=".$v['id']);
		}
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		
		//地区列表
		
		$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 2");  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == intval($GLOBALS['user_info']['province_id']))
			{
				$region_lv2[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where pid = ".intval($GLOBALS['user_info']['province_id']));  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == intval($GLOBALS['user_info']['city_id']))
			{
				$region_lv3[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."user set `step` = 1 where id = ".intval($GLOBALS['user_info']['id']));
		$user_info = es_session::get("user_info");
		$user_info['step'] = 1;
		es_session::set('user_info',$user_info);
		
		$GLOBALS['tmpl']->display("user_step_one.html");
		exit;
	}
	public function steptwo(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		$GLOBALS['tmpl']->display("user_step_two.html");
		exit;
	}
	public function stepthree(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
		//获取会员列表
		$user_list = get_rand_user(24,0,intval($GLOBALS['user_info']['id']));
		foreach($user_list as $k => $v){
			$user_list[$k]['province'] = $GLOBALS['db']->getOne("SELECT `name` FROM ".DB_PREFIX."region_conf WHERE id ='".intval($v['province_id'])."' ");
			$user_list[$k]['city'] = $GLOBALS['db']->getOne("SELECT `name` FROM ".DB_PREFIX."region_conf WHERE id ='".intval($v['city_id'])."' ");
		}
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		$GLOBALS['tmpl']->display("user_step_three.html");
		exit;
	}
	
	public function stepsave(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		$user_id=intval($GLOBALS['user_info']['id']);
		$focus_list = explode(",",$_REQUEST['user_ids']);
		foreach($focus_list as $k=>$focus_uid)
		{
			if(intval($focus_uid) > 0){
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".intval($focus_uid));
				if(!$focus_data)
				{
						$focused_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$focus_uid);
						$focus_data = array();
						$focus_data['focus_user_id'] = $user_id;
						$focus_data['focused_user_id'] = $focus_uid;
						$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
						$focus_data['focused_user_name'] = $focused_user_name;
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
				}
			}
		}		
		showSuccess($GLOBALS['lang']['REGISTER_SUCCESS'],0,url("shop","uc_center"));
	}
	
	public function loginout()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$result = loginout_user();
		if($result['status'])
		{
			$s_user_info = es_session::get("user_info");
			//更新购物车
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($s_user_info['id'])." where session_id = '".es_session::id()."'");
			es_cookie::delete("user_name");
			es_cookie::delete("user_pwd");
			$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);
			$before_loginout = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
			if(trim(app_conf("INTEGRATE_CODE"))=='')
			{
				app_redirect($before_loginout);
			}
			else
			showSuccess($GLOBALS['lang']['LOGINOUT_SUCCESS'],0,$before_loginout);
		}
		else
		{
			app_redirect(url("index"));		
		}
	}
	
	public function getpassword()
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_get_password.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		}
		$GLOBALS['tmpl']->display("user_get_password.html",$cache_id);
	}
	
	public function send_password()
	{
		$email = addslashes(trim($_REQUEST['email']));
		if(!check_email($email))
		{
			showErr($GLOBALS['lang']['MAIL_FORMAT_ERROR']);
		}
		elseif($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email ='".$email."'") == 0)
		{
			showErr($GLOBALS['lang']['NO_THIS_MAIL']);
		}
		else 
		{
			$user_info = $GLOBALS['db']->getRow('select * from '.DB_PREFIX."user where email='".$email."'");
			send_user_password_mail($user_info['id']);
			showSuccess($GLOBALS['lang']['SEND_HAS_SUCCESS']);
		}
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		$GLOBALS['tmpl']->display("user_get_password.html");
	}
	
	public function modify_password()
	{
		 
		$id = intval($_REQUEST['id']);
		$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['NO_THIS_USER']);
		}
		$verify = $_REQUEST['code'];
		if($user_info['password_verify'] == $verify&&$user_info['password_verify']!='')
		{
			//成功	
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SET_NEW_PASSWORD']);				
			$GLOBALS['tmpl']->assign("user_info",$user_info);
			$GLOBALS['tmpl']->display("user_modify_password.html");
		}
		else
		{
			showErr($GLOBALS['lang']['VERIFY_FAILED'],0,APP_ROOT."/");
		}	
	}
	
	public function do_modify_password()
	{
		$id = intval($_REQUEST['id']);
		$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['NO_THIS_USER']);
		}
		$verify = $_REQUEST['code'];
		if($user_info['password_verify'] == $verify&&$user_info['password_verify']!='')
		{
			if(trim($_REQUEST['user_pwd'])!=trim($_REQUEST['user_pwd_confirm']))
			{
				showErr($GLOBALS['lang']['PASSWORD_VERIFY_FAILED']);
			}
			else
			{			
				$password = addslashes(trim($_REQUEST['user_pwd']));
				$user_info['user_pwd'] = $password;
				$password = md5($password.$user_info['code']);
				$result = 1;  //初始为1
				//载入会员整合
				$integrate_code = trim(app_conf("INTEGRATE_CODE"));
				if($integrate_code!='')
				{
					$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
					if(file_exists($integrate_file))
					{
						require_once $integrate_file;
						$integrate_class = $integrate_code."_integrate";
						$integrate_obj = new $integrate_class;
					}	
				}
				
				if($integrate_obj)
				{
					$result = $integrate_obj->edit_user($user_info,$user_info['user_pwd']);				
				}
				if($result>0)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$password."',password_verify='' where id = ".$user_info['id'] );
					showSuccess($GLOBALS['lang']['NEW_PWD_SET_SUCCESS'],0,APP_ROOT."/");
				}
				else
				{
					showErr($GLOBALS['lang']['NEW_PWD_SET_FAILED']);
				}
			}
			//成功	
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SET_NEW_PASSWORD']);				
			$GLOBALS['tmpl']->assign("user_info",$user_info);
			$GLOBALS['tmpl']->display("user_modify_password.html");
		}
		else
		{
			showErr($GLOBALS['lang']['VERIFY_FAILED'],0,APP_ROOT."/");
		}	
	}
	
	public function send()
	{
		$id = intval($_REQUEST['id']);
		$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['NO_THIS_USER']);
		}
		if($user_info['is_effect']==1)
		{
			showErr($GLOBALS['lang']['HAS_VERIFIED']);
		}
		send_user_verify_mail($user_info['id']);
		showSuccess($GLOBALS['lang']['SEND_HAS_SUCCESS'],0,APP_ROOT."/");	
	}
	
	public function verify()
	{
		$id = intval($_REQUEST['id']);
		$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['NO_THIS_USER']);
		}
		$verify = addslashes(trim($_REQUEST['code']));
		if($user_info['verify']!=''&&$user_info['verify'] == $verify)
		{
			//成功
			es_session::set("user_info",$user_info);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",verify = '',is_effect = 1 where id =".$user_info['id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."mail_list set is_effect = 1 where mail_address ='".$user_info['email']."'");	
			$GLOBALS['db']->query("update ".DB_PREFIX."mobile_list set is_effect = 1 where mobile ='".$user_info['mobile']."'");								
			showSuccess($GLOBALS['lang']['VERIFY_SUCCESS'],0,get_gopreview());
		}
		elseif($user_info['verify']=='')
		{
			showErr($GLOBALS['lang']['HAS_VERIFIED'],0,get_gopreview());
		}
		else
		{
			showErr($GLOBALS['lang']['VERIFY_FAILED'],0,get_gopreview());
		}
	}
	
	public function api_create()
	{
		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			if($s_api_user_info['field'])
			{
				$module = str_replace("_id","",$s_api_user_info['field']);
				$module = strtoupper(substr($module,0,1)).substr($module,1);
				require_once APP_ROOT_PATH."system/api_login/".$module."_api.php";
				$class = $module."_api";
				$obj = new $class();
				$obj->create_user();
				app_redirect(APP_ROOT."/");
				exit;
			}			
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}
}
?>