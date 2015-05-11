<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealCityAction extends CommonAction{
	public function index()
	{
		$condition['is_delete'] = 0;
		$condition['pid'] = 0;
		$this->assign("default_map",$condition);
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		//追加默认参数
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$list = $this->get("list");
		
		$result = array();
		$row = 0;
		foreach($list as $k=>$v)
		{
			$v['level'] = -1;
			$v['name'] = $v['name'];
			$result[$row] = $v;
			$row++;
			$sub_cate = M(MODULE_NAME)->where(array("id"=>array("in",D(MODULE_NAME)->getChildIds($v['id'])),'is_delete'=>0))->findAll();
			$sub_cate = D(MODULE_NAME)->toFormatTree($sub_cate,'name');
			foreach($sub_cate as $kk=>$vv)
			{
				$vv['name']	=	$vv['title_show'];
				$result[$row] = $vv;
				$row++;
			}
		}
		//dump($result);exit;
		$this->assign("list",$result);
		$this->display ();
		return;
	}
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$city_list = M("DealCity")->where('pid = 0')->findAll();
		$this->assign("city_list",$city_list);
		$this->assign("new_sort", M("DealCity")->where("is_delete=0")->max("sort")+1);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$city_list = M("DealCity")->where('pid = 0')->findAll();
		$this->assign("city_list",$city_list);
		
		$this->display ();
	}
	public function delete() {
	//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M("DealCity")->where(array ('id' => array ('in', explode ( ',', $id ) )))->getField("pid")==0)
				{
					$this->error (l("ALL_CANT_DELETE"),$ajax);
				}
				if(M("DealCity")->where(array ('pid' => array ('in', explode ( ',', $id ) ),'is_delete'=>0 ))->count()>0)
				{
					$this->error (l("SUB_CITY_EXIST"),$ajax);
				}
				if(M("Deal")->where(array ('city_id' => array ('in', explode ( ',', $id ) ),'is_delete'=>0 ))->count()>0)
				{
					$this->error (l("SUB_DEAL_EXIST"),$ajax);
				}
				if(M("Area")->where(array ('city_id' => array ('in', explode ( ',', $id ) ),'is_delete'=>0 ))->count()>0)
				{
					$this->error (l("SUB_AREA_EXIST"),$ajax);
				}
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					clear_auto_cache("city_list_result");
					clear_auto_cache("deal_city_belone_ids");
								clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
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
					$info[] = $data['name'];						
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					clear_auto_cache("city_list_result");
					clear_auto_cache("deal_city_belone_ids");
								clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
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
					clear_auto_cache("city_list_result");
					clear_auto_cache("deal_city_belone_ids");
					clear_auto_cache("byouhui_filter_nav_cache");
					clear_auto_cache("fyouhui_filter_nav_cache");
					clear_auto_cache("tuan_filter_nav_cache");
					clear_auto_cache("ytuan_filter_nav_cache");
					clear_auto_cache("store_filter_nav_cache");
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("CITY_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['uname']))
		{
			$this->error(L("CITY_UNAME_EMPTY_TIP"));
		}			
		
		if(M("DealCity")->where("is_default=1")->count()==0)
		{
			$data['is_default'] = 1;
		}
		$data['pid'] = 1;
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			clear_auto_cache("city_list_result");
			clear_auto_cache("deal_city_belone_ids");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$DBerr,0);
			$this->error(L("INSERT_FAILED").$DBerr);
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
	
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("CITY_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['uname'])&&$data['pid']>0)
		{
			$this->error(L("CITY_UNAME_EMPTY_TIP"));
		}			

		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			clear_auto_cache("city_list_result");
			clear_auto_cache("deal_city_belone_ids");
						clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$DBerr,0);
			$this->error(L("UPDATE_FAILED").$DBerr,0);
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
		clear_auto_cache("city_list_result");
					clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
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
		if(M("DealCity")->where(array ('id' => array ('in', explode ( ',', $id ) )))->getField("pid")!=0)
		{
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		clear_auto_cache("city_list_result");
		clear_auto_cache("deal_city_belone_ids");
					clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
		}
		else
		$this->ajaxReturn(1,l("SET_EFFECT_1"),1)	;	
	}
	
	public function set_default()
	{
		$id = intval($_REQUEST['id']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		M(MODULE_NAME)->setField("is_default",0);	
		M(MODULE_NAME)->where("id=".$id)->setField("is_default",1);		
		clear_auto_cache("city_list_result");	
		save_log($info.l("SET_DEFAULT"),1);
		$this->success(L("UPDATE_SUCCESS"));	
	}
}
?>