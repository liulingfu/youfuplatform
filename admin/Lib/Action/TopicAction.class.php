<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class TopicAction extends CommonAction{
	public function index()
	{
		$reminder = M("RemindCount")->find();
		$reminder['topic_count_time'] = get_gmtime();
		M("RemindCount")->save($reminder);
		$map = $this->_search ();
		if(trim($_REQUEST['keyword'])!='')
		{
			$where['content'] = array('like','%'.trim($_REQUEST['keyword']).'%');		
			$where['title'] = array('like','%'.trim($_REQUEST['keyword']).'%');		
			$where['_logic'] = 'or';
			$map['_complex'] = $where;			
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$map['user_name'] = array('like','%'.trim($_REQUEST['user_name']).'%');		
		}		
		
		if(trim($_REQUEST['type'])=='all'||trim($_REQUEST['type'])=='')
		{
			unset($map['type']); 
			$map['fav_id'] = 0;
			$map['relay_id'] = 0;
		}		
		if(trim($_REQUEST['type'])=='fav')
		{
			unset($map['type']);
			$map['fav_id'] = array("neq",0);
		}
		if(trim($_REQUEST['type'])=='relay')
		{
			unset($map['type']);
			$map['relay_id'] = array("neq",0);
		}
		//列表过滤器，生成查询Map对象		
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
		$this->display ();
		return;
	}
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		//输出分类
		$cate_list = M("TopicTagCate")->findAll();
		foreach($cate_list as $k=>$v)
		{
			$cate_list[$k]['checked'] = M("TopicCateLink")->where("topic_id=".$id." and cate_id = ".$v['id'])->count();
		}
		$this->assign("cate_list",$cate_list);
		
		//输出图片
		$image_list = M("TopicImage")->where("topic_table='topic' and topic_id=".$vo['id'])->findAll();
		$this->assign("image_list",$image_list);
		$this->display ();
	}
	
	public function update() {
			B('FilterString');
			$data = M(MODULE_NAME)->create ();			
			$log_info = $data['id'].l("TOPIC_DATA");
			//开始验证有效性
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
	
			// 更新数据
			$list=M(MODULE_NAME)->save ($data);
			if (false !== $list) {
				rm_auto_cache("recommend_forum_topic");
				M("TopicCateLink")->where("topic_id=".$data['id'])->delete();
				foreach($_REQUEST['cate_id'] as $cate_id)
				{
					$link_data = array();
					$link_data['cate_id'] = $cate_id;
					$link_data['topic_id'] = $data['id'];
					M("TopicCateLink")->add($link_data);
				}
				syn_topic_match($data['id']);
				//成功提示
				save_log($log_info.L("UPDATE_SUCCESS"),1);
				$this->success(L("UPDATE_SUCCESS"));
			} else {
				//错误提示
				save_log($log_info.L("UPDATE_FAILED"),0);
				$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
			}
		}

	
	public function delete()
	{
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				
				$topic_condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$condition = array ('topic_id' => array ('in', explode ( ',', $id ) ) );
				
				if(M("TopicReply")->where ( $condition )->count()>100)
				{
					$this->error (l("DATA_TOO_BIG"),$ajax);
				}
				
				$rel_data = M(MODULE_NAME)->where($topic_condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $topic_condition )->delete();	
						
				if ($list!==false) {
					rm_auto_cache("recommend_forum_topic");
					foreach($rel_data as $topic)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set topic_count = topic_count - 1 where id = ".$topic['group_id']);
						$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_cate_link where topic_id = ".$topic['id']);						
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = count - 1 where name = '".$topic['title']."'");
						$GLOBALS['db']->query("update ".DB_PREFIX."user set topic_count = topic_count - 1 where id = ".intval($topic['user_id']));
						if($topic['fav_id']>0)
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."user set fav_count = fav_count - 1 where id = ".intval($topic['user_id']));
							$fav_topic = $GLOBALS['db']->getRow("select user_id,id,origin_id from ".DB_PREFIX."topic where id = ".$topic['fav_id']);
							
							$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count - 1 where id = ".intval($fav_topic['user_id']));
							if($fav_topic['id']!=$fav_topic['origin_id'])
							{
								$fav_origin_topic = $GLOBALS['db']->getRow("select user_id,id,origin_id from ".DB_PREFIX."topic where id = ".$fav_topic['origin_id']);
								$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count - 1 where id = ".intval($fav_origin_topic['user_id']));
							}
						}
						if($topic['group']=='Fanwe')
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."user set insite_count = insite_count - 1 where id = ".intval($topic['user_id']));
						}
					}
					
					//删除相关的其他数据，如回复，图片
					$reply_ids = $GLOBALS['db']->getOne("select group_concat(id) from ".DB_PREFIX."topic_reply where topic_id in(".$id.")");
					$reply_images = M("TopicImage")->where("topic_table = 'topic_reply' and topic_id in (".$reply_ids.")")->findAll();
					foreach($reply_images as $image_data)
					{
						@unlink(APP_ROOT_PATH.$image_data['path']);
						@unlink(APP_ROOT_PATH.$image_data['o_path']);
					}
					M("TopicImage")->where("topic_table = 'topic_reply' and topic_id in (".$reply_ids.")")->delete();
					M("TopicReply")->where ( $condition )->delete();
					M("TopicCateLink")->where($condition)->delete();
					$topic_images = M("TopicImage")->where("topic_table = 'topic' and topic_id in (".$id.")")->findAll();
					foreach($topic_images as $image_data)
					{
						@unlink(APP_ROOT_PATH.$image_data['path']);
						@unlink(APP_ROOT_PATH.$image_data['o_path']);
					}
					M("TopicImage")->where("topic_table = 'topic' and topic_id in (".$id.")")->delete();					
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
	
	public function toogle_status()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$field = $_REQUEST['field'];
		$info = $id."_".$field;
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField($field);  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField($field,$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		rm_auto_cache("recommend_forum_topic");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
}


?>