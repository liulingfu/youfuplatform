<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class YouhuiAction extends CommonAction{

	public function index()
	{		
		$map['publish_wait'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	function get_location_info()
	{
		$location_id= intval($_REQUEST['id']);
		$location = M("SupplierLocation")->where("id=".$location_id)->find();
		if($location)
		{
			$this->ajaxReturn($location,"",true);
		}
		else
		{
			$this->ajaxReturn($location,"",false);
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField('name');
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
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
		M(MODULE_NAME)->where("id=".$id)->setField("update_time",get_gmtime());	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$locations = M("YouhuiLocationLink")->where(array ('youhui_id' => $id ))->findAll();
		foreach($locations as $location)
		{
			recount_supplier_data_count($location['location_id'],"youhui");
		}
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	
	public function add()
	{
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Youhui")->max("sort")+1);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
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
			$this->error(L("YOUHUI_NAME_EMPTY_TIP"));
		}
		
		if(file_exists(get_real_path().$_REQUEST['image_3'])){
			list($image_3_w,$image_3_h) =getimagesize(get_real_path().$_REQUEST['image_3']);
			$data['image_3_w']=intval($image_3_w);
			$data['image_3_h']=intval($image_3_h);
		}
			
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['create_time'] = get_gmtime();
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {		
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['youhui_id'] = $list;
				M("DealCateTypeYouhuiLink")->add($link_data);
			}	
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['youhui_id'] = $list;
				M("YouhuiLocationLink")->add($link_data);
				recount_supplier_data_count($location_id,"youhui");
			}
			syn_youhui_match($list);
			clear_auto_cache("fyouhui_filter_nav_cache");
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	
	public function edit()
	{
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		$this->display();
	}
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));	
		if(!check_empty($data['name']))
		{
			$this->error(L("YOUHUI_NAME_EMPTY_TIP"));
		}	
		
		if(file_exists(get_real_path().$_REQUEST['image_3'])){
			list($image_3_w,$image_3_h) =getimagesize(get_real_path().$_REQUEST['image_3']);
			$data['image_3_w']=intval($image_3_w);
			$data['image_3_h']=intval($image_3_h);
		}
		
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$log_info = $data['name'];
		
		// 更新数据
		$data['publish_wait'] = 0;
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			M("DealCateTypeYouhuiLink")->where("youhui_id=".$data['id'])->delete();
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['youhui_id'] = $data['id'];
				M("DealCateTypeYouhuiLink")->add($link_data);
			}
			M("YouhuiLocationLink")->where("youhui_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['youhui_id'] = $data['id'];
				M("YouhuiLocationLink")->add($link_data);
				recount_supplier_data_count($location_id,"youhui");
			}
			//成功提示
			syn_youhui_match($data['id']);
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$DBerr,0);
			$this->error(L("UPDATE_FAILED").$DBerr,0);
		}
	}
	
	public function foreverdelete() {
	//删除指定记录
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
					clear_auto_cache("fyouhui_filter_nav_cache");
					$locations = M("YouhuiLocationLink")->where(array ('youhui_id' => array ('in', explode ( ',', $id ) ) ))->findAll();					
					M("DealCateTypeYouhuiLink")->where(array ('youhui_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					M("YouhuiLocationLink")->where(array ('youhui_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"youhui");
					}
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
	
	function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['cate_id']);
		$youhui_id = intval($_REQUEST['youhui_id']);
		$sub_cate_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_cate_type as c left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = c.id where l.cate_id = ".$cate_id);
		
		foreach($sub_cate_list as $k=>$v)
		{
			$sub_cate_list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate_type_youhui_link where deal_cate_type_id = ".$v['id']." and youhui_id = ".$youhui_id);
		}
		$this->assign("sub_cate_list",$sub_cate_list);
		
		if($sub_cate_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	function load_supplier_location()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$youhui_id = intval($_REQUEST['youhui_id']);
		
		$supplier_location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id);
		foreach($supplier_location_list as $k=>$v)
		{
			if($youhui_id>0)
			$supplier_location_list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_location_link where location_id = ".$v['id']." and youhui_id = ".$youhui_id);
			else 
			$supplier_location_list[$k]['checked'] = true;
			
		}
		$this->assign("supplier_location_list",$supplier_location_list);
		
		if($supplier_location_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	public function publish()
	{
		$map['publish_wait'] = 1;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
}
?>