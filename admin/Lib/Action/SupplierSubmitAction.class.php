<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class SupplierSubmitAction extends CommonAction{

	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$cate_config = unserialize($vo['cate_config']);
		$vo['deal_cate_id'] = $cate_config['deal_cate_id'];
		$vo['deal_cate'] = M("DealCate")->where("id=".$vo['deal_cate_id'])->getField("name");
		
		$vo['deal_cate_type'] = M("DealCateType")->where(array("id"=>array("in",$cate_config['deal_cate_type_id'])))->findAll();
		
		$location_config = unserialize($vo['location_config']);
		$location_config[] = 0;
		$vo['area_list'] = M("Area")->where(array("id"=>array("in",$location_config)))->order("pid asc")->findAll();

		$vo['city'] = M("DealCity")->where("id=".$vo['city_id'])->getField("name");
		
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	public function update() {
		B('FilterString');
		$data = M("SupplierSubmit")->getById(intval($_REQUEST['id']));
		if(!$data)
		{
			$this->error("非法的数据");
		}
		
		//已有商家，只同步一份管理员
		$user_info = M("User")->getById($data['user_id']);
		$account = M("SupplierAccount")->where("account_name='".$user_info['merchant_name']."'")->find();			
		$location_info = M("SupplierLocation")->getById($data['location_id']);	
		if($account)
		{
			//已经绑定过商户
			if($account['supplier_id']!=$location_info['supplier_id'])
			{
				$this->error("该会员已经是其他商户的管理员，请手动创建");
			}
		}	
			
		$cate_config = unserialize($data['cate_config']);
		$data['deal_cate_id'] = $cate_config['deal_cate_id'];
		$data['deal_cate_type_list'] = $cate_config['deal_cate_type_id'];		
		$data['area_list'] = unserialize($data['location_config']);
		
		
		if($data['location_id']==0)
		{
			$supplier_id = intval($_REQUEST['supplier_id']);
			if($supplier_id == 0)
			{
				//先创建商户
				$supplier_info['name'] = $data['name'];
				$supplier_info['bank_info'] = $data['h_bank_info'];
				$supplier_info['bank_user'] = $data['h_bank_user'];
				$supplier_info['bank_name'] = $data['h_bank_name'];
				$supplier_info['preview'] = $data['h_supplier_logo'];
				$supplier_id = M("Supplier")->add($supplier_info);
				$location_info['is_main'] = 1;
			}
			
			$location_info['name'] = $data['name'];			
			$location_info['address'] = $data['address'];
			$location_info['tel'] = $data['tel'];
			$location_info['xpoint'] = $data['xpoint'];
			$location_info['ypoint'] = $data['ypoint'];
			$location_info['supplier_id'] = $supplier_id;
			$location_info['open_time'] = $data['open_time'];
			$location_info['city_id'] = $data['city_id'];
			$location_info['deal_cate_id'] = $data['deal_cate_id'];
			$location_info['preview'] = $data['h_supplier_image'];
			$location_info['is_effect'] = 1;
			$data['location_id'] = M("SupplierLocation")->add($location_info);
			
			
			foreach($data['deal_cate_type_list'] as $deal_cate_type_id)
			{
				$link = array();
				$link['location_id'] = $data['location_id'];
				$link['deal_cate_type_id'] = $deal_cate_type_id;
				M("DealCateTypeLocationLink")->add($link);
			}
			
			foreach($data['area_list'] as $area_id)
			{
				$link = array();
				$link['location_id'] = $data['location_id'];
				$link['area_id'] = $area_id;
				M("SupplierLocationAreaLink")->add($link);
			}
			syn_supplier_location_match($data['location_id']);
		}
		
		if($data['location_id']>0)
		{

			if($account)
			{

				$id = $account['id'];
				$link = array();
				$link['account_id'] = $id;
				$link['location_id'] = $data['location_id'];
				$rs = M("SupplierAccountLocationLink")->add($link);
				if($rs)
				{
					//认领成功，并同步营业执照
					$location_info['biz_license'] = $data['h_license'];
					$location_info['biz_other_license'] = $data['h_other_license'];			
					M("SupplierLocation")->save($location_info);
					
					$this->assign("jumpUrl",u("SupplierLocation/edit",array("id"=>$data['location_id'])));
					M("SupplierSubmit")->where("id=".intval($_REQUEST['id']))->setField("is_publish",1);
					save_log($data['name']."审核成功",1);
					$this->success("审核成功");
				}
				else
				{
					$this->error("该会员已经是该商户的管理员");
				}
			}
			else
			{
				//会员未绑定商户，或绑定的不是同名商户管理员，创建一个商户管理员
				$account['account_name'] = $user_info['user_name'];
				$account['account_password'] = $user_info['user_pwd'];
				$account['supplier_id'] = $location_info['supplier_id'];
				$account['is_effect'] = 1;
				$account['description'] = $data['h_faren']."电话：".$data['h_tel'];
				$account['update_time'] = get_gmtime();				
				$id = M("SupplierAccount")->add($account);
				
				if($id)
				{
					//添加成功
					$link = array();
					$link['account_id'] = $id;
					$link['location_id'] = $data['location_id'];
					M("SupplierAccountLocationLink")->add($link);
					
					//认领成功
					$location_info['biz_license'] = $data['h_license'];
					$location_info['biz_other_license'] = $data['h_other_license'];				
					M("SupplierLocation")->save($location_info);
					
					$this->assign("jumpUrl",u("SupplierLocation/edit",array("id"=>$data['location_id'])));
					M("SupplierSubmit")->where("id=".intval($_REQUEST['id']))->setField("is_publish",1);
					save_log($data['name']."审核成功",1);
					$user_info['is_merchant'] = 1;
					$user_info['merchant_name'] = $account['account_name'];
					M("User")->save($user_info);
					$this->success("审核成功");
				}
				else
				{
					$account = M("SupplierAccount")->where("account_name='".$user_info['user_name']."'")->find();
					if($account['supplier_id']==$location_info['supplier_id'])
					{
						$link = array();
						$link['account_id'] = $account['id'];
						$link['location_id'] = $data['location_id'];
						M("SupplierAccountLocationLink")->add($link);
						if(M()->getDbError()!='')
						{
							$this->error("该会员已经认领了");
						}
						else
						{
							$location_info['biz_license'] = $data['h_license'];
							$location_info['biz_other_license'] = $data['h_other_license'];				
							M("SupplierLocation")->save($location_info);
							$this->assign("jumpUrl",u("SupplierLocation/edit",array("id"=>$data['location_id'])));
							M("SupplierSubmit")->where("id=".intval($_REQUEST['id']))->setField("is_publish",1);
							save_log($data['name']."审核成功",1);
							$this->success("审核成功");
						}
					}
					$this->error("该会员已有同名的商户管理员，请手动创建");
				}	
			}					
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
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
				//删除相关预览图
//				foreach($rel_data as $data)
//				{
//					@unlink(get_real_path().$data['preview']);
//				}			
				if ($list!==false) {
										
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
	
	
}
?>