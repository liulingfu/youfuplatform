<?php
class updateaccount
{
    public function index()
	{

        $root = array();
        $root['return'] = 1;

        $email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
        $pwd = addslashes($GLOBALS['request']['pwd']);//密码

        $user_info = user_check($email,$pwd);
        $user_id  = intval($user_info['id']);

        if(!$user_info)
        {
        	$root['status'] = 0;
        	$root['message'] = "用户已失效，无法升级";
        	output($root);
        }
        else
        {
        	$upd_user_name = addslashes($GLOBALS['request']['upd_user_name']);
        	$upd_password  = addslashes($GLOBALS['request']['upd_password']);
        	$user_data = array('id'=>$user_id,'user_name'=>$upd_user_name,'user_pwd'=>$upd_password,'email'=>$upd_user_name);

        	$res = update_user($user_id, $user_data);
        	//print_r($res);
        	if($res['status'] == 1)
        	{

        		$root['status'] = 1;
        		$root['uid'] = $user_id;
        		$root['user_name'] = $upd_user_name;
        		$root['password'] = md5($upd_password);
        		$root['is_account'] = 1;
        		output($root);
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
        		$root['status'] = 0;
        		$root['message']	=	$error_msg;
        		output($root);
        	}
        }
	}
}


/**
* 生成会员数据
* @param $user_data  提交[post或get]的会员数据
* @param $mode  处理的方式，注册或保存
* 返回：data中返回出错的字段信息，包括field_name, 可能存在的field_show_name 以及 error 错误常量
* 不会更新保存的字段为：score,money,verify,pid
*/
function update_user($user_id, $user_data)
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
	//$user['create_time'] = get_gmtime();
	$user['update_time'] = get_gmtime();
	//自动获取会员分组
	$user['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
	$user['is_effect'] = 1; //手机注册自动生效
	$user['is_account'] = 1;
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
		//$integrate_id = intval($res['data']);
		$user['integrate_id'] = intval($res['data']);
		//if($integrate_id>0){
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set integrate_id = ".$integrate_id." where id = ".$user_id);
		//}
	}

	if($GLOBALS['db']->autoExecute(DB_PREFIX."user",$user,"UPDATE","id=".$user_id))
	{
		//$user_id = $GLOBALS['db']->insert_id();

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
	return $res;
}
?>