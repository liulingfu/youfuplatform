<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/message.php';
require APP_ROOT_PATH.'app/Lib/page.php';
class searchModule extends TuanBaseModule
{
	public function index()
	{				
		//用于处理团购搜索的处理程序
		$se_name = trim(addslashes($_POST['se_name']));
		$se_begin = trim(addslashes($_POST['se_begin']));
		$se_end = trim(addslashes($_POST['se_end']));
		
		$se_begin = to_timespan($se_begin,'Y-m-d');
		$se_end = to_timespan($se_end,'Y-m-d');
		$se_end = $se_end!=0?($se_end+24*3600-1):$se_end;
		
		$search['se_name'] = $se_name;
		$search['se_begin'] = $se_begin;
		$search['se_end'] = $se_end;
		
		$se_module =  trim(addslashes($_POST['se_module']));
		$se_action =  trim(addslashes($_POST['se_action']));
		$se_id =  intval(addslashes($_POST['se_id']));
		
		
		$search_code = urlencode(base64_encode(serialize($search)));
		$url = APP_ROOT."/tuan.php?ctl=".$se_module."&act=".$se_action."&id=".$se_id."&search=".$search_code;
		app_redirect($url);		
	}
}
?>