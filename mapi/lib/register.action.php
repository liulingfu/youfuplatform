<?php
/**
	 * 生成会员数据
	 * @param $user_data  提交[post或get]的会员数据
	 * @param $mode  处理的方式，注册或保存
	 * 返回：data中返回出错的字段信息，包括field_name, 可能存在的field_show_name 以及 error 错误常量
	 * 不会更新保存的字段为：score,money,verify,pid
*/
function add_user($user_data)
{		
	
		//开始数据验证
		$res = array('status'=>1,'info'=>'','data'=>''); //用于返回的数据
		if(trim($user_data['user_name'])=='')
		{
			$field_item['field_name'] = 'user_name';
			$field_item['error']	=	EMPTY_ERROR;
			$res['status'] = 0;
			$res['data'] = $field_item;
			return $res;
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where user_name = '".trim($user_data['user_name'])."' and id <> ".intval($user_data['id']))>0)
		{
			$field_item['field_name'] = 'user_name';
			$field_item['error']	=	EXIST_ERROR;
			$res['status'] = 0;
			$res['data'] = $field_item;
			return $res;
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email = '".trim($user_data['email'])."' and id <> ".intval($user_data['id']))>0)
		{
			$field_item['field_name'] = 'email';
			$field_item['error']	=	EXIST_ERROR;
			$res['status'] = 0;
			$res['data'] = $field_item;
			return $res;
		}
		if(trim($user_data['email'])=='')
		{
			$field_item['field_name'] = 'email';
			$field_item['error']	=	EMPTY_ERROR;
			$res['status'] = 0;
			$res['data'] = $field_item;
			return $res;
		}
		if(!check_email(trim($user_data['email'])))
		{
			$field_item['field_name'] = 'email';
			$field_item['error']	=	FORMAT_ERROR;
			$res['status'] = 0;
			$res['data'] = $field_item;
			return $res;
		}
		

		//验证结束开始插入数据
		$user['user_name'] = $user_data['user_name'];
		$user['create_time'] = get_gmtime();
		$user['update_time'] = get_gmtime();
		//自动获取会员分组
		$user['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
		$user['is_effect'] = 1; //手机注册自动生效
		
		$user['email'] = $user_data['email'];

		$user['user_pwd'] = md5($user_data['user_pwd']);
		
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
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user,"INSERT","");
			$user_id = $GLOBALS['db']->insert_id();	
			if($user_id > 0)
			{
					$register_money = doubleval(app_conf("USER_REGISTER_MONEY"));
					$register_score = intval(app_conf("USER_REGISTER_SCORE"));
					if($register_money>0||$register_score>0)
					{
						$user_get['score'] = $register_score;
						$user_get['money'] = $register_money;
						modify_account($user_get,intval($user_id),"在".to_date(get_gmtime())."注册成功");
					}
			}
			$res['data'] = $user_id;
		}

			if(strim($GLOBALS['request']['sina_id'])!='')
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where sina_id = '".strim($GLOBALS['request']['sina_id'])."'")==0)
				{
					$access_token =  trim($GLOBALS['request']['access_token']);
					$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_id = '".strim($GLOBALS['request']['sina_id'])."',sina_token = '".$access_token."' where id = ".$user_id);				
				}
				
				
			}
			if(strim($GLOBALS['request']['tencent_id'])!='')
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where tencent_id = '".strim($GLOBALS['request']['tencent_id'])."'")==0)
				{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set tencent_id = '".strim($GLOBALS['request']['tencent_id'])."' where id = ".$user_id);
			
				$openid = trim($GLOBALS['request']['openid']);
				$openkey = trim($GLOBALS['request']['openkey']);
		 		$access_token =  trim($GLOBALS['request']['access_token']);
				$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', login_ip = '".get_client_ip()."',login_time= ".get_gmtime()." where id =".$user_id);				
				
				}
				
			}
		
		return $res;
}

class register{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);//邮箱
		$pwd = strim($GLOBALS['request']['password']);//密码
		$user_name = strim($GLOBALS['request']['user_name']);//用户名
		$gender = intval($GLOBALS['request']['gender']);
			/*
		$data = array(
			'email'            => $_FANWE['requestData']['email'],
			'user_name'        => $_FANWE['requestData']['user_name'],
			'password'         => $_FANWE['requestData']['password'],
			'gender'           => intval($_FANWE['requestData']['gender']),
		);
		*/
		
		if(strlen($pwd)<4)
		{
			$root['return'] = 0;
			$root['info']	=	"密码不能低于四位";
		}
		else
		{		
			$user_data['email'] = $email;
			$user_data['user_name'] = $user_name;
			$user_data['user_pwd'] = $pwd;
			$user_data['sex'] = $gender;
			$res = add_user($user_data);
			if($res['status'] == 1)
			{
				//$result = do_login_user($email,$pwd);				
				$root['return'] = 1;
				$root['info']	=	"注册成功";
				$root['uid'] = $res['data'];
				$root['user_name'] = $user_name;
				$root['user_email'] = $email;	
				$root['user_avatar'] = get_abs_img_root(get_muser_avatar($root['uid'],"big"));		
				$root['user_pwd'] = $pwd;		
				
				
				
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
				$root['return'] = 0;
				$root['info']	=	$error_msg;
			}
		}
		
		
	if(strim($GLOBALS['request']['sina_id'])!='')
		{
			$root['login_type'] = "Sina";
		}
		if(strim($GLOBALS['request']['tencent_id'])!='')
		{
			$root['login_type'] = "Tencent";
		}
		output($root);
		
	}
}
?>