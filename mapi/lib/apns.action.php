<?php
class apns
{
	public function index()
	{		
		$root = array();
		$root['return'] = 1;
		
		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码		
		//检查用户,用户密码
		$user_info = user_check($email,$pwd);
		$user_id  = intval($user_info['id']);
		
		$appname = addslashes(trim($GLOBALS['request']['appname']));
		$appversion = addslashes(trim($GLOBALS['request']['appversion']));
		$deviceuid = addslashes(trim($GLOBALS['request']['deviceuid']));
		$devicetoken = addslashes(trim($GLOBALS['request']['devicetoken']));
		$devicename = addslashes(trim($GLOBALS['request']['devicename']));
		$devicemodel = addslashes(trim($GLOBALS['request']['devicemodel']));
		$deviceversion = addslashes(trim($GLOBALS['request']['deviceversion']));
		$pushbadge = addslashes(trim($GLOBALS['request']['pushbadge']));
		$pushalert = addslashes(trim($GLOBALS['request']['pushalert']));
		$pushsound = addslashes(trim($GLOBALS['request']['pushsound']));
		$clientid = $user_id;
	
		$root['info'] = '';
		if(strlen($appname)==0) $root['info'] = 'Application Name must not be blank.';
		else if(strlen($appversion)==0) $root['info'] = 'Application Version must not be blank.';
		else if(strlen($deviceuid)>40) $root['info'] = 'Device ID may not be more than 40 characters in length.';
		else if(strlen($devicetoken)!=64) $root['info'] = 'Device Token must be 64 characters in length.';
		else if(strlen($devicename)==0) $root['info'] = 'Device Name must not be blank.';
		else if(strlen($devicemodel)==0) $root['info'] = 'Device Model must not be blank.';
		else if(strlen($deviceversion)==0) $root['info'] = 'Device Version must not be blank.';
		else if($pushbadge!='disabled' && $pushbadge!='enabled') $root['info'] = 'Push Badge must be either Enabled or Disabled.';
		else if($pushalert!='disabled' && $pushalert!='enabled') $root['info'] = 'Push Alert must be either Enabled or Disabled.';
		else if($pushsound!='disabled' && $pushsound!='enabled') $root['info'] = 'Push Sount must be either Enabled or Disabled.';

		$now = get_gmtime();
		// store device for push notifications
		if ($root['info'] == ''){
			$sql = "INSERT INTO ".DB_PREFIX."apns_devices ".
					"VALUES (
						NULL,
						'{$clientid}',
						'{$appname}',
						'{$appversion}',
						'{$deviceuid}',
						'{$devicetoken}',
						'{$devicename}',
						'{$devicemodel}',
						'{$deviceversion}',
						'{$pushbadge}',
						'{$pushalert}',
						'{$pushsound}',
						'production',
						'active',
						'{$now}',
						'{$now}'
					)
					ON DUPLICATE KEY UPDATE
					`devicetoken`='{$devicetoken}',
					`devicename`='{$devicename}',
					`devicemodel`='{$devicemodel}',
					`deviceversion`='{$deviceversion}',
					`pushbadge`='{$pushbadge}',
					`pushalert`='{$pushalert}',
					`pushsound`='{$pushsound}',
					`status`='active',
					`modified`='{$now}';";
			$GLOBALS['db']->query($sql);
			$root['info'] = '注册成功';//.$sql;
			$root['return'] = 1;
		}else{
			$root['return'] = 0;
		}
		
		output($root);
	}
}
?>