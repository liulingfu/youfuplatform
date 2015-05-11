<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class MessageTypeAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	public function add()
	{
		$this->assign("new_sort", M(MODULE_NAME)->max("sort")+1);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		if($vo['is_fix']==1)
		{
			$this->error(l("MESSAGE_FIX_CANNT_EDIT"));
		}
		$this->assign ( 'vo', $vo );
		$this->display ();
	}

	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M(MODULE_NAME)->where(array ('id' => array ('in', explode ( ',', $id ) ) ,'is_fix'=>1))->count()>0)
				{
					$this->error(l("FOREVER_DELETE_FAILED_FIX"),1);
				}
				
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['type_name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					foreach($rel_data as $data)
					{
						M("Message")->where("rel_table='".$data['type_name']."'")->delete();
					}
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
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['type_name']))
		{
			$this->error(L("TYPE_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['show_name']))
		{
			$this->error(L("SHOW_NAME_EMPTY_TIP"));
		}			
		if(M(MODULE_NAME)->where("type_name='".$data['type_name']."'")->count()>0)
		{
			$this->error(L("TYPE_NAME_EXIST_TIP"));
		}
		// 更新数据
		$log_info = $data['type_name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("type_name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['show_name']))
		{
			$this->error(L("SHOW_NAME_EMPTY_TIP"));
		}
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			M("Message")->where("rel_table='".$data['type_name']."'")->setField("is_effect",$data['is_effect']);
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("type_name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		if(M(MODULE_NAME)->where("id=".$id)->getField("is_fix")==1)
		{
			$this->error(l("SORT_FAILED_FIX"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
}
?>