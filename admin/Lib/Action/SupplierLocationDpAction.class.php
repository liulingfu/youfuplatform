<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +-----------------------------------------

class SupplierLocationDpAction extends CommonAction
{
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ("SupplierLocationDp");
		if (! empty ( $model )) {
			$this->_list($model,$map);
		}
		
		M("SupplierLocation")->where("id=".intval($_REQUEST['supplier_location_id']))->setField("new_dp_count_time",get_gmtime());
		$new_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".intval($_REQUEST['supplier_location_id'])." and create_time > ".get_gmtime())); 
		
		M("SupplierLocation")->where("id=".intval($_REQUEST['supplier_location_id']))->setField("new_dp_count",$new_count);
		
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
			$ids = explode ( ',', $id );
			$condition = array ($pk => array ('in', $ids ) );
			$condition_link = array ("dp_id" => array ('in', $ids ) );
			$dp_list = $model->where($condition)->findAll();
			
			if(M("SupplierLocationDpReply")->where($condition_link)->count()>0)
			{
				$this->error ("请先清空点评回应",$ajax);
			}
			
			if(false !== $model->where ( $condition )->delete ())
			{
				foreach($dp_list as $k=>$v)
				{
					if($v['status']==1)
					{
						$merchant_info = M("SupplierLocation")->getById($v['supplier_location_id']);
						syn_supplier_locationcount($merchant_info);
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count - 1 where id = ".intval($v['user_id']));
				}
				
				M("SupplierLocationDpPointResult")->where($condition_link)->delete();
				M("SupplierLocationDpTagResult")->where($condition_link)->delete();
				save_log($ids.l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
			}
			else
			{
				save_log($ids.l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
			}
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
		
	}
	
	
	function edit() {
		
		$name = $this->getActionName();
		$model = D($name);
		
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById($id);
		$vo = sys_get_dp_detail($vo);
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	
	
	public function removePhoto()
	{
		$photo_id = intval($_REQUEST['photo_id']);
		$photo_data = D("SupplierLocationImages")->where("id=".$photo_id)->find();
		D("SupplierLocationImages")->where("id=".$photo_id)->delete();
		//开始同步data
		m()->query("update ".DB_PREFIX."supplier_location set image_count = image_count - 1 where id = ".$photo_data['supplier_location_id']);
		$image_count = M("SupplierLocationImages")->where("dp_id = ".$photo_data['dp_id'])->count();
		if($image_count == 0 )
		M("SupplierLocationDp")->where("id=".$photo_data['dp_id'])->setField("is_img",0);		
        @unlink(APP_ROOT.$photo_data['image']);
		$result['isErr'] = 0;
		
		die(json_encode($result));
	}
	
	function update() {
		//B('FilterString');
		$name=$this->getActionName();
		$model = D ( $name );
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		$id = $data[$model->getPk()];
		
		
		// 更新数据
		$list=$model->save ();
		$dp = $model->getById($id);
		
		if (false !== $list) {
			//成功提示
			$group_points = $_REQUEST['group_point'];
			foreach($group_points as $group_id=>$point)
			{
				$model->query("update ".DB_PREFIX."supplier_location_dp_point_result set point = ".$point." where group_id = ".$group_id." and dp_id = ".$id." and supplier_location_id = ".$dp['supplier_location_id']);
			}
			
			$group_tags = $_REQUEST['group_tag'];
			foreach($group_tags as $group_id=>$tags)
			{
				$model->query("update ".DB_PREFIX."supplier_location_dp_tag_result set tags = '".$tags."' where group_id = ".$group_id." and dp_id = ".$id." and supplier_location_id = ".$dp['supplier_location_id']);
			}
			$count = M("SupplierLocationDpReply")->where("dp_id=".$id)->count();
			$model->where("id=".$id)->setField("reply_count",$count);
			$supplier_info['id'] = $dp['supplier_location_id'];
			syn_supplier_locationcount($supplier_info);		
			save_log($dp.L("UPDATE_SUCCESS"),1);
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('UPDATE_SUCCESS'));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($dp.L("UPDATE_FAILED").$dbErr,0);
			$this->error (L('EDIT_ERROR'));
		}
	}
	


}
function getUNAME($id)
{
	return 	M("User")->where("id=".$id)->getField("user_name");
}
function getMerchantName($id)
{
	return M("SupplierLocation")->where("id=".$id)->getField("name");
}
function getIsImg($tag)
{
	if($tag)return "是";
	else
	return "否";
}
?>