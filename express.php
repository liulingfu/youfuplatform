<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

//快递查询
require './system/common.php';
require './app/Lib/tuan_init.php';

$express_id = intval($_REQUEST['express_id']);
$typeNu = addslashes(trim($_REQUEST["express_sn"]));
$express_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."express where is_effect = 1 and id = ".$express_id);
$express_info['config'] = unserialize($express_info['config']);
$typeCom = trim($express_info['config']["app_code"]);

if(isset($typeCom)&&isset($typeNu)){

	$AppKey = app_conf("KUAIDI_APP_KEY");//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
	$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';

	
	//优先使用curl模式发送数据
	//KUAIDI_TYPE : 1. API查询 2.页面查询
	if (app_conf("KUAIDI_TYPE")==1){
	  $get_content = file_get_contents($url);
	  	  
	  //请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
	  $powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';

	  $data['msg'] = $get_content . '<br/>' . $powered;
	  $data['status'] = 1;   //API查询
	  ajax_return($data);
	}else{
	  $data['msg'] = "http://www.kuaidi100.com/chaxun?com=".$typeCom."&nu=".$typeNu;
	  $data['status'] = 2;   //页面查询
	  ajax_return($data);
	}
	
}else{
	$data['msg'] = '查询失败，请重试';
	$data['status'] = 0;   //查询失败
	ajax_return($data);
}
exit();


?>