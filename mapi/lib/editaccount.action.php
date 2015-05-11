<?php
class editaccount
{		
    public function index()
	{

        $root = array();
        $root['return'] = 1;
       
        $email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
        $pwd = md5(addslashes($GLOBALS['request']['pwd']));//原始密码

        $user_info = user_check($email,$pwd);        
        $user_id  = intval($user_info['id']);
        
        
        if(!$user_info)
        {
        	$root['status'] = 0;
        	$root['message'] = "用户密码错误";     
        	output($root);   	
        }
        else
        {        	
        	
        	$new_password = addslashes($GLOBALS['request']['new_password']);
        	
        	if (strlen($new_password) < 4){
				$root['status'] = 0;
				$root['message'] = "注册密码不能少于4位";
				output($root);
			}

			$sql = "update ".DB_PREFIX."user set is_account = 1, user_pwd = '".md5($new_password)."' where id = {$user_id}";
			$GLOBALS['db']->query($sql);
			$rs = $GLOBALS['db']->affected_rows();
			
						
			$root['status'] = 1;
			$root['uid'] = $user_id;
	        $root['user_name'] = $email;
	        $root['password'] = md5($new_password);    
	        $root['is_account'] = 1;   
	        output($root); 
        }        
	}
}
?>