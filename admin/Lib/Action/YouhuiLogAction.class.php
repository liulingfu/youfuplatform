<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class YouhuiLogAction extends CommonAction{	
	public function foreverdelete() {
	//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}	
	}

	public function send_sms()
	{
		$ajax = intval($_REQUEST['ajax']);
		$log_id = intval($_REQUEST['id']);
		if(M("YouhuiLog")->where("id=".$log_id)->count()>0)
		{
		 	 if(send_youhui_log_sms($log_id))
		 	 {
		 		$this->success (l("SEND_SUCCESS"),$ajax);
		 	 }
		 	 else
		 	 {
		 	 	$this->error (l("SEND_FAILED"),$ajax);
		 	 }
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
}
?>