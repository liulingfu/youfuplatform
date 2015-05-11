<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class TopicReplyAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		//输出图片
		$image_list = M("TopicImage")->where("topic_table='topic_reply' and topic_id=".$vo['id'])->findAll();
		$this->assign("image_list",$image_list);
		$this->display ();
	}
	
	public function update() {
			B('FilterString');
			$data = M(MODULE_NAME)->create ();
	
			
			$log_info = $data['id'].l("TOPIC_REPLY_DATA");
			//开始验证有效性
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
			
			if(!check_empty($data['content']))
			{
				$this->error(L("TOPIC_CONTENT_EMPTY_TIP"));
			}			
	
			// 更新数据
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

	
	public function delete()
	{
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				
				$topic_condition = array ('id' => array ('in', explode ( ',', $id ) ) );
			
				$rel_data = M(MODULE_NAME)->where($topic_condition)->findAll();	
				$count =  M(MODULE_NAME)->where($topic_condition)->count();	
				$topic_id =  M(MODULE_NAME)->where($topic_condition)->getField("topic_id");		
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $topic_condition )->delete();	
						
				if ($list!==false) {
					//删除相关的其他数据，如回复，图片										 
					$topic_images = M("TopicImage")->where("topic_table = 'topic_reply' and topic_id in (".$id.")")->findAll();
					foreach($topic_images as $image_data)
					{
						@unlink(APP_ROOT_PATH.$image_data['path']);
						@unlink(APP_ROOT_PATH.$image_data['o_path']);
					}
					M("TopicImage")->where("topic_table = 'topic_reply' and topic_id in (".$id.")")->delete();		

					$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count - ".$count." where id = ".$topic_id);
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