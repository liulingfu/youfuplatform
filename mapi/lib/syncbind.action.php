<?php
class syncbind
{
	public function index()
	{		
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['return'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		
		//$func_name = strim($GLOBALS['request']['type'])."_".strim($GLOBALS['request']['login_type']);
		$func_name = strim($GLOBALS['request']['login_type']);
		$func_name();
	}	
}

function Tencent()
{
		es_session::start();		
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
		OAuth::init($GLOBALS['m_config']['tencent_app_key'],$GLOBALS['m_config']['tencent_app_secret']);

		$openid = trim($GLOBALS['request']['openid']);
		$openkey = trim($GLOBALS['request']['openkey']);
		
		if($GLOBALS['m_config']['tencent_bind_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $GLOBALS['m_config']['tencent_bind_url'];
		}
        $access_token =  trim($GLOBALS['request']['access_token']);

		es_session::set("t_access_token",$access_token);
		es_session::set("t_openid",$openid);
		es_session::set("t_openkey",$openkey);
		
		if (es_session::get("t_access_token")|| (es_session::get("t_openid")&&es_session::get("t_openkey"))) 
		{
  			
			$r = Tencent::api('user/info');
			$r = json_decode($r,true);
    		$name =  $r['data']['name'];
			
    		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where tencent_id = '".$name."'")==0)
    		$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', tencent_id = '".$name."' where id =".intval($GLOBALS['user_info']['id']));				
			elseif(intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where tencent_id = '".$name."'"))==intval($GLOBALS['user_info']['id']))
   			{
   				$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', tencent_id = '".$name."' where id =".intval($GLOBALS['user_info']['id']));							
   			}
   			else
   			{
   				$root['return'] = 0;	
				$root['info'] = "该微博帐号已被其他会员绑定";	
				output($root);
   			}
		}

		$root['return'] = 1;	
		$root['info'] = "绑定成功";	
		$root['login_type'] = "Tencent";
		output($root);
}

function USSina()
{
	es_session::start();		

		$sina_id = trim($GLOBALS['request']['sina_id']);
		$access_token = trim($GLOBALS['request']['access_token']);
		

		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where sina_id = '".$sina_id."'")==0)
    	$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_token ='".$access_token."', sina_id = '".$sina_id."' where id =".intval($GLOBALS['user_info']['id']));				
		elseif(intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where sina_id = '".$sina_id."'"))==intval($GLOBALS['user_info']['id']))
   			{
   				$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_token ='".$access_token."', sina_id = '".$sina_id."' where id =".intval($GLOBALS['user_info']['id']));							
   			}
   		else
   			{
   				$root['return'] = 0;	
				$root['info'] = "该微博帐号已被其他会员绑定";	
				output($root);
   			}
   		$root['return'] = 1;	
		$root['info'] = "绑定成功";	
		$root['login_type'] = "Sina";
		output($root);
}
?>