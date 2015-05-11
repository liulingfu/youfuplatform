<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class SupplierAccountAction extends CommonAction{
	public function index()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$condition['is_delete'] = 0;
		$condition['supplier_id'] = intval($_REQUEST['supplier_id']);
		$this->assign("default_map",$condition);
		$this->assign("supplier_info",$supplier_info);
		parent::index();
	}
	public function trash()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$condition['is_delete'] = 1;
		$condition['supplier_id'] = intval($_REQUEST['supplier_id']);
		$this->assign("default_map",$condition);
		$this->assign("supplier_info",$supplier_info);
		parent::index();
	}
	public function add()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$location_list = M("SupplierLocation")->where("supplier_id=".$supplier_id)->findAll();
		$this->assign("location_list",$location_list);
		$this->assign("supplier_info",$supplier_info);
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add",array("supplier_id"=>$data['supplier_id'])));
		if(!check_empty($data['account_name']))
		{
			$this->error(L("ACCOUNT_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['account_password']))
		{
			$this->error(L("ACCOUNT_PASSWORD_EMPTY_TIP"));
		}		
		if($data['account_password']!=$_REQUEST['account_confirm_password'])
		{
			$this->error(L("PASSWORD_NOT_MATCH"));
		}	
		else
		{
			$data['account_password'] = md5($data['account_password']);
		}
		if($data['supplier_id']==0)
		{
			$this->error(L("SUPPLIER_NOT_EXIST"));
		}
		$data['update_time'] = get_gmtime();
		// 更新数据
		$log_info = $data['account_name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['account_id'] = $list;
				M("SupplierAccountLocationLink")->add($link_data);
			}
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$supplier_info = M("Supplier")->getById($vo['supplier_id']);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$this->assign("supplier_info",$supplier_info);
		
		$this->assign ( 'vo', $vo );
		$location_list = M("SupplierLocation")->where("supplier_id=".$vo['supplier_id'])->findAll();
		foreach($location_list as $k=>$v)
		{
			$location_list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account_location_link where account_id = ".$vo['id']." and location_id = ".$v['id']);
		}
		$this->assign("location_list",$location_list);
		$this->display ();
	}
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("account_name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['account_name']))
		{
			$this->error(L("ACCOUNT_NAME_EMPTY_TIP"));
		}			
		if($data['supplier_id']==0)
		{
			$this->error(L("SUPPLIER_NOT_EXIST"));
		}
		if($data['account_password']=='')
		{
			//不改密码
			unset($data['account_password']);
		}
		else
		{
			if($data['account_password']!=$_REQUEST['account_confirm_password'])
			{
				$this->error(L("PASSWORD_NOT_MATCH"));
			}
			else
			{
				$data['account_password'] = md5($data['account_password']);
			}
		}
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			M("SupplierAccountLocationLink")->where("account_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['account_id'] = $data['id'];
				M("SupplierAccountLocationLink")->add($link_data);
			}
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['account_name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
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
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['account_name'];						
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['account_name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
				//删除相关预览图
//				foreach($rel_data as $data)
//				{
//					@unlink(get_real_path().$data['preview']);
//				}			
				if ($list!==false) {
					M("SupplierAccountLocationLink")->where(array ('account_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("account_name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
}
?>