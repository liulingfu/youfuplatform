<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class TopicGroupAction extends CommonAction{

	public function add()
	{
		$cate_list = M("TopicGroupCate")->findAll();
		$this->assign("cate_list",$cate_list);
		$this->assign("new_sort", M(MODULE_NAME)->max("sort")+1);		
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);		
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['user_name'] = M("User")->where("id=".$vo['user_id'])->getField("user_name");
		$this->assign ( 'vo', $vo );	
		$cate_list = M("TopicGroupCate")->findAll();	
		$this->assign("cate_list",$cate_list);
		$this->display ();
	}

	public function insert() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error("请输入小组名称");
		}			
		$data['create_time'] = get_gmtime();	
		$data['user_id'] = intval(M("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField("id"));	
		
		
		
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			
			if($data['user_id']>0)
			{
				//为组长加权限
				$auth_data = array();
				$auth_data['m_name'] = "group";
				$auth_data['a_name'] = "del";
				$auth_data['user_id'] = $data['user_id'];
				$auth_data['rel_id'] = $list;
				M("UserAuth")->add($auth_data);
				
				$auth_data = array();
				$auth_data['m_name'] = "group";
				$auth_data['a_name'] = "replydel";
				$auth_data['user_id'] = $data['user_id'];
				$auth_data['rel_id'] = $list;
				M("UserAuth")->add($auth_data);
				
				$auth_data = array();
				$auth_data['m_name'] = "group";
				$auth_data['a_name'] = "settop";
				$auth_data['user_id'] = $data['user_id'];
				$auth_data['rel_id'] = $list;
				M("UserAuth")->add($auth_data);
				
				$auth_data = array();
				$auth_data['m_name'] = "group";
				$auth_data['a_name'] = "setbest";
				$auth_data['user_id'] = $data['user_id'];
				$auth_data['rel_id'] = $list;
				M("UserAuth")->add($auth_data);
				
				$auth_data = array();
				$auth_data['m_name'] = "group";
				$auth_data['a_name'] = "setmemo";
				$auth_data['user_id'] = $data['user_id'];
				$auth_data['rel_id'] = $list;
				M("UserAuth")->add($auth_data);
			}
			
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_group_cate set group_count = group_count + 1 where id = ".intval($data['cate_id']));
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
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
    public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		
		$o_user_id = intval(M("TopicGroup")->where("id=".intval($data['id']))->getField("user_id"));
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error("请输入小组名称");
		}			
		$data['user_id'] = intval(M("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField("id"));
		
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			
			if($o_user_id!=$data['user_id']) //组长变更
			{
				if(M("UserTopicGroup")->where("group_id=".$data['id']." and user_id = ".$o_user_id." and type = 1")->count()>0)
				{
					//管理员的话只删除设置说明的权限
					M("UserAuth")->where("user_id=".$o_user_id." and m_name = 'group' and a_name = 'setmemo' and rel_id = ".$data['id'])->delete();
				}
				else
				M("UserAuth")->where("user_id=".$o_user_id." and m_name = 'group' and rel_id = ".$data['id'])->delete();
				if($data['user_id']>0)
				{
					//为组长加权限
					//管理员只续加设置说明的权限
					if(M("UserTopicGroup")->where("group_id=".$data['id']." and user_id = ".$data['user_id']." and type = 1")->count()==0)
					{
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "del";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "replydel";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "settop";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "setbest";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['id'];
						M("UserAuth")->add($auth_data);
					}
					else
					{
						M("UserTopicGroup")->where("group_id=".$data['id']." and user_id = ".$data['user_id']." and type = 1")->delete(); //删除管理员记录
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count - 1 where id = ".$data['id']);
					}
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "setmemo";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
				}
			}
			else
			{
				if($data['user_id']>0)
				{
					//同步权限
					M("UserAuth")->where("user_id=".$data['user_id']." and m_name = 'group' and rel_id = ".$data['id'])->delete();
					//为组长加权限
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "del";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
					
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "replydel";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
					
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "settop";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
					
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "setbest";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
					
					$auth_data = array();
					$auth_data['m_name'] = "group";
					$auth_data['a_name'] = "setmemo";
					$auth_data['user_id'] = $data['user_id'];
					$auth_data['rel_id'] = $data['id'];
					M("UserAuth")->add($auth_data);
				}
			}
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
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();

				if ($list!==false) {
					M("Topic")->where(array ('group_id' => array ('in', explode ( ',', $id ) ) ))->setField("group_id",0);
					M("UserAuth")->where("rel_id in (".explode(",",$id).") and m_name = 'group'")->delete();
					foreach($rel_data as $data)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_group_cate set group_count = group_count - 1 where id = ".intval($data['cate_id']));	
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
	
	public function user_index()
	{
		//列表过滤器，生成查询Map对象
		$group_info = M("TopicGroup")->getById(intval($_REQUEST['group_id']));
		if($group_info)
		{
			$this->assign("group_info",$group_info);
			$map['group_id'] = $group_info['id'];
			if (method_exists ( $this, '_filter' )) {
				$this->_filter ( $map );
			}
			$model = D ("UserTopicGroup");
			if (! empty ( $model )) {
				$this->_list ( $model, $map );
			}
			$this->display ();
			return;
		}
		else
		{
			$this->error("不存在的小组");
		}
	}
	
	public function user_add()
	{	
		$group_info = M("TopicGroup")->getById(intval($_REQUEST['group_id']));	
		if($group_info)
		{	
			$this->assign("group_info",$group_info);
			$this->display();
		}
		else
		{
			$this->error("不存在的小组");
		}
	}
	
	public function user_insert() {
		B('FilterString');
		$group_info = M("TopicGroup")->getById(intval($_REQUEST['group_id']));	
		if($group_info)
		{
			$data = M("UserTopicGroup")->create ();
			//开始验证有效性
			$this->assign("jumpUrl",u(MODULE_NAME."/user_add",array("group_id"=>$data['group_id'])));
			if(!check_empty($_REQUEST['user_name']))
			{
				$this->error("请输入用户名");
			}			
		
			$data['user_id'] = intval(M("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField("id"));
			if($data['user_id']==$group_info['user_id'])
			{
				$this->error("该会员是本组组长");
			}
			if($data['user_id']==0)
			{
				$this->error("不存在该会员");
			}
			$data['create_time'] = get_gmtime();	
			
			// 更新数据
			$log_info = $_REQUEST['user_name'];
			$list=M("UserTopicGroup")->add($data);
			if (false !== $list) {
				//成功提示
				
				//为管理员加权限
					if($data['type']>0)
					{
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "del";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "replydel";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "settop";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "setbest";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);

					}
				
				
				
				$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count + 1 where id = ".$group_info['id']);
				save_log($log_info.L("INSERT_SUCCESS"),1);
				$this->success(L("INSERT_SUCCESS"));
			} else {
				//错误提示
				save_log($log_info.L("INSERT_FAILED"),0);
				$this->error("该会员已经是本组组员");
			}
		}
		else
		{
			$this->error("不存在的小组");
		}
	}
	
	public function user_edit()
	{	
		$id = intval($_REQUEST ['id']);		
		$condition['id'] = $id;		
		$vo = M("UserTopicGroup")->where($condition)->find();
		$vo['user_name'] = M("User")->where("id=".$vo['user_id'])->getField("user_name");
		$this->assign ( 'vo', $vo );	
		$group_info = M("TopicGroup")->getById(intval($vo['group_id']));	
		$this->assign("group_info",$group_info);
		$this->display ();
	}
	
	public function user_update() {
		B('FilterString');
		$group_info = M("TopicGroup")->getById(intval($_REQUEST['group_id']));	
		if($group_info)
		{
			$data = M("UserTopicGroup")->create ();
			
			//开始验证有效性
			$this->assign("jumpUrl",u(MODULE_NAME."/user_edit",array("id"=>$data['id'])));
			if(!check_empty($_REQUEST['user_name']))
			{
				$this->error("请输入用户名");
			}			
			$data['user_id'] = intval(M("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField("id"));
			if($data['user_id']==0)
			{
				$this->error("不存在该会员");
			}	
			if($data['user_id']==$group_info['user_id'])
			{
				$this->error("该会员是本组组长");
			}			
			// 更新数据
			$log_info = $_REQUEST['user_name'];
			$o_data = M("UserTopicGroup")->getById($data['id']);
			$list=M("UserTopicGroup")->save($data);
			if (false !== $list) {
				//成功提示
				M("UserAuth")->where("user_id=".$o_data['user_id']." and m_name = 'group' and rel_id = ".$data['group_id'])->delete();
				M("UserAuth")->where("user_id=".$data['user_id']." and m_name = 'group' and rel_id = ".$data['group_id'])->delete();
				if($data['type']>0)
					{
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "del";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "replydel";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "settop";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);
						
						$auth_data = array();
						$auth_data['m_name'] = "group";
						$auth_data['a_name'] = "setbest";
						$auth_data['user_id'] = $data['user_id'];
						$auth_data['rel_id'] = $data['group_id'];
						M("UserAuth")->add($auth_data);

					}
				
				save_log($log_info.L("UPDATE_SUCCESS"),1);
				$this->success(L("UPDATE_SUCCESS"));
			} else {
				//错误提示
				save_log($log_info.L("UPDATE_FAILED"),0);
				$this->error("该会员已经是本组组员");
			}
		}
		else
		{
			$this->error("不存在的小组");
		}
	}
	
	
	public function user_del() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("UserTopicGroup")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("UserTopicGroup")->where ( $condition )->delete();

				if ($list!==false) {
					
					foreach($rel_data as $data)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count - 1 where id = ".$data['group_id']);	
						$GLOBALS['db']->query("delete from ".DB_PREFIX."user_auth where user_id=".$data['user_id']." and m_name ='group' and rel_id <> 0");  //权限
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

}
?>