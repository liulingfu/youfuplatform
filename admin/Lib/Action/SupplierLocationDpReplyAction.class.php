<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +-----------------------------------------
class SupplierLocationDpReplyAction extends CommonAction
{	
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ("SupplierLocationDpReply");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		$this->display ();
		return;
	}
	
	public function foreverdelete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$name=$this->getActionName();
			$model = D($name);
			$pk = $model->getPk ();
			$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
			$res = $model->where($condition)->findAll();
			if(false !== $model->where ( $condition )->delete ())
			{
				foreach($res as $k=>$v)
				{
					$count = $model->where("dp_id=".$v['dp_id'])->count();
					M("SupplierLocationDp")->where("id=".$v['dp_id'])->setField("reply_count",$count);
				}
				
				save_log($res.l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
			}
			else
			{
				save_log($res.l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
			}
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}

}
function getUNAME($id)
{
	return 	M("User")->where("id=".$id)->getField("user_name");
}
?>