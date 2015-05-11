<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DeliveryAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	public function add()
	{
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		$this->assign("new_sort", M("Delivery")->max("sort")+1);
		$this->display();
	}
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("DELIVERY_NAME_EMPTY_TIP"));
		}	
		
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		
		require_once APP_ROOT_PATH."system/utils/child.php";
		$child = new child("delivery_region");
		
		if (false !== $list) {
			//开始处理配送地区
			$delivery_regions = $_REQUEST['region_support_region'];
			foreach($delivery_regions as $k=>$v)
			{
				if($v!='')
				{
					$id_arr = explode(",",$v);					
					$sub_ids = array();
					foreach($id_arr as $vv)
					{
						if(!in_array($vv,$sub_ids))
						{
						$tmp_ids = $child->getChildIds($vv);
						$tmp_ids[] = $vv;
						$sub_ids = array_merge($sub_ids,$tmp_ids);
						}
					}
					
					
					//添加相应的支持地区
					$delivery_fee_item = array();
					$delivery_fee_item['delivery_id'] = $list;
					$delivery_fee_item['region_ids'] = implode(",",$sub_ids);
					$delivery_fee_item['first_weight'] = $_REQUEST['region_first_weight'][$k];
					$delivery_fee_item['first_fee'] = $_REQUEST['region_first_fee'][$k];
					$delivery_fee_item['continue_weight'] = $_REQUEST['region_continue_weight'][$k];
					$delivery_fee_item['continue_fee'] = $_REQUEST['region_continue_fee'][$k];
					M("DeliveryFee")->add($delivery_fee_item);
				}
			}
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			clear_auto_cache("cache_support_delivery");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		//开始输出配送地区列表
		$regions_list = M("DeliveryFee")->where("delivery_id=".$id)->findAll();
		foreach($regions_list as $k=>$v)
		{
			$names = '';
			$regions = M("DeliveryRegion")->where("id in(".$v['region_ids'].")")->findAll();
			foreach($regions as $kk=>$vv)
			{
				$names.=$vv['name'].",";
			}
			$names = substr($names,0,-1);
			$regions_list[$k]['names'] = $names;
		}
		$this->assign("regions_list",$regions_list);
		$this->display ();
	}
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();

		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DELIVERY_NAME_EMPTY_TIP"));
		}	
		require_once APP_ROOT_PATH."system/utils/child.php";
		$child = new child("delivery_region");
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			M("DeliveryFee")->where("delivery_id=".$data['id'])->delete();
			//开始处理配送地区
			$delivery_regions = $_REQUEST['region_support_region'];
			foreach($delivery_regions as $k=>$v)
			{
				if($v!='')
				{
					$id_arr = explode(",",$v);					
					$sub_ids = array();
					foreach($id_arr as $vv)
					{
						if(!in_array($vv,$sub_ids))
						{
						$tmp_ids = $child->getChildIds($vv);
						$tmp_ids[] = $vv;
						$sub_ids = array_merge($sub_ids,$tmp_ids);
						}
					}
					
					//添加相应的支持地区
					$delivery_fee_item = array();
					$delivery_fee_item['delivery_id'] = $data['id'];
					$delivery_fee_item['region_ids'] = implode(",",$sub_ids);
					$delivery_fee_item['first_weight'] = $_REQUEST['region_first_weight'][$k];
					$delivery_fee_item['first_fee'] = $_REQUEST['region_first_fee'][$k];
					$delivery_fee_item['continue_weight'] = $_REQUEST['region_continue_weight'][$k];
					$delivery_fee_item['continue_fee'] = $_REQUEST['region_continue_fee'][$k];
					M("DeliveryFee")->add($delivery_fee_item);
				}
			}
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			clear_auto_cache("cache_support_delivery");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
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
		
				if ($list!==false) {
					M("DeliveryFee")->where(array ('delivery_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					M("FreeDelivery")->where(array ('delivery_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					clear_auto_cache("cache_support_delivery");
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
		
	
	
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		clear_auto_cache("cache_support_delivery");
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		clear_auto_cache("cache_support_delivery");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	//选取配送地区
	public function selectRegions()
	{
		$delivery_regions = M("DeliveryRegion")->where('region_level = 1')->findAll();
		$region_conf_id = intval($_REQUEST['region_conf_id']);
		$delivery_fee = M("DeliveryFee")->where("id=".$region_conf_id)->find();
		$delivery_fee['region_ids'] = explode(",",$delivery_fee['region_ids']);
		foreach($delivery_regions as $k=>$v)
		{
			$delivery_regions[$k]['delivery_regions'] = M("DeliveryRegion")->where('pid = '.$v['id'])->findAll();
		}
		$this->assign("delivery_regions",$delivery_regions);
		$this->assign("delivery_fee",$delivery_fee);
		$this->display();
	}
	public function getSubRegion()
	{
		$id = intval($_REQUEST['id']);
		$region_conf_id = intval($_REQUEST['delivery_fee_id']);
		$delivery_fee = M("DeliveryFee")->where("id=".$region_conf_id)->find();
		$delivery_fee['region_ids'] = explode(",",$delivery_fee['region_ids']);
		
		$delivery_regions = M("DeliveryRegion")->where('pid = '.$id)->findAll();
		$this->assign("delivery_regions",$delivery_regions);
		$this->assign("delivery_fee",$delivery_fee);
		$this->display();
	}
}
?>