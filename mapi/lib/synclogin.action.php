<?php
class synclogin
{
	public function index()
	{	
		
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
    		
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where tencent_id = '".$name."' and tencent_id <> ''");	
			
			if($user_data)
			{
				if($user_data['is_effect']==0||$user_data['is_delete']==1)
				{
					$result['resulttype'] = 0;
				}
				else
				{
					$user_current_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_data['group_id']));
					$user_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where score <=".intval($user_data['score'])." order by score desc");
					if($user_current_group['score']<$user_group['score'])
					{
						$user_data['group_id'] = intval($user_group['id']);
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",group_id=".intval($user_data['group_id'])." where id =".$user_data['id']);				
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($user_data['id'])." where session_id = '".es_session::id()."'");
					
					$result['user_pwd'] = $user_data['user_pwd'];
					$result['uid'] = $user_data['id'];
					$result['email'] = $user_data['email'];
					$result['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
					$name = $user_data['user_name'];
					$result['resulttype'] = 1;
				}					
			}
			else
			{
				$result['email'] = "";
				$result['user_pwd'] = md5(get_gmtime());
				$result['user_name'] = $name;;
				$result['t_access_token'] = $access_token;
				$result['t_openkey'] = $openkey;
				$result['t_openid'] = $openid;
				$result['tencent_id'] = $name;
				$result['uid'] = bind_add_user($result);
				if($result['uid'] > 0){
					$result['user_avatar']='';
					$result['resulttype'] = 1;
				}
				else
					$result['resulttype'] = -1;
			}
   			
		}
		
		$result['openid'] = $openid;
		$result['openkey'] = $openkey;
		$result['access_token'] = $access_token;
		
		
		$result['tencent_id'] = $name;
		$result['user_name'] = $name;
		$result['act'] = "synclogin";
		$result['login_type'] = "Tencent";
		output($result);
}



function USSina()
{
		es_session::start();		

		$sina_id = trim($GLOBALS['request']['sina_id']);
		$access_token = trim($GLOBALS['request']['access_token']);
		$r = $GLOBALS['request']['user_info'];
		$r = json_decode($r,true);
		
	
		$name = $r['screen_name'];

			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where sina_id = '".$sina_id."' and sina_id <> '' and sina_id <> 0");	
			
			if($user_data)
			{
				if($user_data['is_effect']==0||$user_data['is_delete']==1)
				{
					$result['resulttype'] = 0;
				}
				else
				{
					$user_current_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_data['group_id']));
					$user_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where score <=".intval($user_data['score'])." order by score desc");
					if($user_current_group['score']<$user_group['score'])
					{
						$user_data['group_id'] = intval($user_group['id']);
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_token ='".$access_token."', login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",group_id=".intval($user_data['group_id'])." where id =".$user_data['id']);				
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($user_data['id'])." where session_id = '".es_session::id()."'");
					
					$result['user_pwd'] = $user_data['user_pwd'];
					$result['uid'] = $user_data['id'];
					$result['email'] = $user_data['email'];
					$result['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
					$name = $user_data['user_name'];
					$result['resulttype'] = 1;
				}					
			}
			else
			{
				$result['email'] = "";
				$result['user_pwd'] = md5(get_gmtime());
				$result['user_name'] = $name;
				$result['sina_token'] = $access_token;
				$result['sina_id'] = $sina_id;
				$result['uid'] = bind_add_user($result);
				if($result['uid'] > 0){
					$result['user_avatar']='';
					$result['resulttype'] = 1;
				}
				else
					$result['resulttype'] = -1;
			}
   			
		
		

		$result['access_token'] = $access_token;
		
		
		$result['sina_id'] = $sina_id;
		$result['user_name'] = $name;
		$result['act'] = "synclogin";
		$result['login_type'] = "Sina";
		output($result);
}

function bind_add_user($user_data){
	$res['status'] = 1;
	
	if(trim($user_data['user_name'])=='')
	{
		return 0;
	}
	if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where user_name = '".trim($user_data['user_name'])."' and id <> ".intval($user_data['id']))>0)
	{
		return 0;
	}
	
	//自动获取会员分组
	$user_data['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
	$user_data['is_effect'] = 1; //手机注册自动生效
	$user_data['login_ip'] = get_client_ip();
	$user_data['create_time'] = get_gmtime();
	$user_data['update_time'] = get_gmtime();
	
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
	//同步整合
	if($integrate_obj)
	{
		$res = $integrate_obj->add_user($user_data['user_name'],$user_data['user_pwd'],$user_data['email']);
		$user['integrate_id'] = intval($res['data']);		
		if(intval($res['status'])==0) //整合注册失败
		{
			//return $res;  //不处理
		}
	}
	
	if($res['status']>0)
	{
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","");
		$user_id = $GLOBALS['db']->insert_id();	
	}
	return $user_id;
}
?>