<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


require APP_ROOT_PATH.'app/Lib/page.php';
class couponModule extends TuanBaseModule
{	
	public function supplier_login()
	{
		app_redirect(url("biz","index"));
	}
}

?>