<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class MAdvAction extends CommonAction{
	public function add()
	{
		$cate_list = M("TopicTagCate")->findAll();
		$this->assign("cate_list",$cate_list);
		$this->assign("new_sort",intval(M(MODULE_NAME)->max("sort"))+1);
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
	foreach($city_list as $k=>$v)
		{
			if($v['pid']==0)$city_list[$k]['id'] = 0;
		}
		$this->assign("city_list",$city_list);
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		
		$_POST['data'] = "";
		switch($_POST['type'])
		{
			case 1:
				$adv_data['cid'] = (int)$_POST['cid'];
				$tags = str_replace('　',' ',$_POST['tags']);
				$tags = explode(' ',$tags);
				$adv_data['tags'] = array_unique($tags);
				$_POST['data'] = serialize($adv_data);
			break;

			case 2:
				$adv_data['url'] = trim($_POST['url']);
				$_POST['data'] = serialize($adv_data);
			break;

			case 8:
				$adv_data['share_id'] = (int)$_POST['share_id'];
				$_POST['data'] = serialize($adv_data);
			break;
			
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
				$adv_data['cate_id'] = (int)$_POST['cate_id'];
				$_POST['data'] = serialize($adv_data);
			break;
			
			case 14:
			case 15:
			case 16:
			case 17:
			case 18:
				$adv_data['data_id'] = (int)$_POST['data_id'];
				$_POST['data'] = serialize($adv_data);
			break;
		}
		
		$data = M(MODULE_NAME)->create ();
			
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("NAME_EMPTY_TIP"));
		}	

		$log_info = $data['name'];
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

	
	
	public function edit()
	{
		$id = intval($_REQUEST['id']);
		$vo = M("MAdv")->getById($id);
		$vo['data'] = unserialize($vo['data']);
		if(isset($vo['data']['tags']))
			$vo['data']['tags'] = implode(' ',$vo['data']['tags']);
		
		$this->assign ('vo', $vo);
		$cate_list = M("TopicTagCate")->findAll();
		$this->assign("cate_list",$cate_list);
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
	foreach($city_list as $k=>$v)
		{
			if($v['pid']==0)$city_list[$k]['id'] = 0;
		}
		$this->assign("city_list",$city_list);
		$this->display();
	}
	
	
	public function update() {
		B('FilterString');
		
		$_POST['data'] = "";
		switch($_POST['type'])
		{
			case 1:
				$adv_data['cid'] = (int)$_POST['cid'];
				$tags = str_replace('　',' ',$_POST['tags']);
				$tags = explode(' ',$tags);
				$adv_data['tags'] = array_unique($tags);
				$_POST['data'] = serialize($adv_data);
			break;

			case 2:
				$adv_data['url'] = trim($_POST['url']);
				$_POST['data'] = serialize($adv_data);
			break;

			case 8:
				$adv_data['share_id'] = (int)$_POST['share_id'];
				$_POST['data'] = serialize($adv_data);
			break;
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
				$adv_data['cate_id'] = (int)$_POST['cate_id'];
				$_POST['data'] = serialize($adv_data);
			break;
			
			case 14:
			case 15:
			case 16:
			case 17:
			case 18:
				$adv_data['data_id'] = (int)$_POST['data_id'];
				$_POST['data'] = serialize($adv_data);
			break;
		}
		
		$data = M(MODULE_NAME)->create ();	
		$log_info = $data['id'];
		
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("NAME_EMPTY_TIP"));
		}
		
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
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
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
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
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
}
?>