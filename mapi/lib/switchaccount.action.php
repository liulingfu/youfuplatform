<?php
class switchaccount
{
    public function index()
	{

        $root = array();
        $root['return'] = 1;

        $email = addslashes($GLOBALS['request']['email2']);//用户名或邮箱
        $pwd = md5(addslashes($GLOBALS['request']['pwd2']));//原始密码

        if($email == '' || empty($email)) {
            $email = addslashes($GLOBALS['request']['user_name']);//用户名或邮箱
            $pwd = md5(addslashes($GLOBALS['request']['password']));//原始密码
        }

        $user_info = user_check($email,$pwd);
        $user_id  = intval($user_info['id']);


        if(!$user_info)
        {
        	$root['status'] = 0;
        	$root['message'] = "用户已失效，无法登录";
        	output($root);
        }
        else
        {
			$root['status'] = 1;
			$root['uid'] = intval($user_info['id']);
	        $root['user_name'] = $user_info['user_name'];
	        $root['password'] = $user_info['user_pwd'];
	        $root['is_account'] = intval($user_info['is_account']);
	        output($root);
        }

	}
}
?>