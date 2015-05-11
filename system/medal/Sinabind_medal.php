<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

$medal_lang = array(
	'name'	=>	'新浪微博勋章',
	'description'	=>	'新浪微博认证勋章，点亮为新浪微博用户',
	'route'	=>	'绑定新浪微博即可获得该勋章',

	
);
$config = array(

);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Sinabind';
    $module['name']    = $medal_lang['name'];
	$module['description'] = $medal_lang['description'];
	$module['route'] = $medal_lang['route'];	
    $module['config'] = $config;    
    $module['lang'] = $medal_lang;
    $module['allow_check'] = 0;  //到期回收
    return $module;
}

// 新浪微博勋章
require_once(APP_ROOT_PATH.'system/libs/medal.php');
class Sinabind_medal implements medal {

	public function get_medal()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		$medal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."medal where class_name = 'Sinabind'");
		$medal['config'] = unserialize($medal['config']);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_medal where medal_id = ".$medal['id']." and user_id = ".$user_id);
		if($data)
		{
			//已经领取
			$result['status'] = 2;
			$result['info'] = "您已经领取过".$medal['name'];
		}
		else
		{	
			
			if(intval($user_info['sina_id'])!=0)
			{
				$link_data['user_id'] = $user_id;
				$link_data['medal_id'] = $medal['id'];
				$link_data['name'] = $medal['name'];
				$link_data['icon'] = $medal['icon'];
				$link_data['create_time'] = get_gmtime();
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_medal",$link_data);				
				$result['status'] = 1; //领取成功
				$result['info'] = "您已经成功领取".$medal['name'];
			}
			else
			{
				$result['status'] = 0;
				$result['info'] = "领取该勋章需要绑定新浪微博帐号";
				$result['jump'] = url("shop","uc_center#setweibo");
			}
		}
		return $result;
	}
	
	public function check_medal()
	{
		return array("status"=>1,"info"=>"");  //不会回收该勋章
		
	}
}
?>