<?php
//参数:event_id
class eventdetail
{
	public function index()
	{			
		require_once APP_ROOT_PATH."system/libs/user.php";

		if(strim($GLOBALS['request']['act_2'])=='bm')
		{
			$email = strim($GLOBALS['request']['email']);
			$pwd = strim($GLOBALS['request']['pwd']);		
			$result = do_login_user($email,$pwd);
			$GLOBALS['user_info'] = $user_data = es_session::get('user_info');			
			
			//报名
			if($GLOBALS['user_info'])
			{
				$event_id = intval($GLOBALS['request']['event_id']);
				$user_id = intval($GLOBALS['user_info']['id']);
				$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id." and is_effect = 1");
				if($event)
				{
					if($event['xpoint']=='')
					{
						$event['xpoint']=0;
					}
					if($event['ypoint']=='')
					{
						$event['ypoint']=0;
					}
					if($event['submit_begin_time']>get_gmtime())
					{
						$root['return'] = 0;
						$root['info'] = "活动未开始";
					}
					elseif($event['submit_end_time']<get_gmtime()&&$event['submit_end_time']!=0)
					{
						$root['return'] = 0;
						$root['info'] = "活动报名已结束";
					}
					else
					{
						//开始提交报名
						$user_id = intval($GLOBALS['user_info']['id']);
						$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_submit where event_id = ".$event_id." and user_id = ".$user_id);
						if(intval($count)>0)
						{
							$root['return'] = 0;
							$root['info'] = "您已经报过名了";
						}
						else
						{
							$submit_data = array();
							$submit_data['user_id'] = $user_id;
							$submit_data['event_id'] = $event_id;
							$submit_data['create_time'] = get_gmtime();
							$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit",$submit_data,"INSERT");
							$submit_id = $GLOBALS['db']->insert_id();
							if($submit_id)
							{
								$bm = $GLOBALS['request']['bm'];
								foreach($bm as $field_id=>$bm_result)
								{					
									$field_data = array();
									$field_data['submit_id'] = $submit_id;
									$field_data['field_id'] = $field_id;
									$field_data['event_id'] = $event_id;
									$field_data['result'] = strim($bm_result);
									$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit_field",$field_data,"INSERT");
								}
								$GLOBALS['db']->query("update ".DB_PREFIX."event set submit_count = submit_count+1 where id=".$event_id);
								
								
								//同步分享
								$title = "报名参加了".$event['name'];
								$content = "报名参加了".$event['name']." - ".$event['brief'];
								$url_route = array(
										'rel_app_index'	=>	'youhui',
										'rel_route'	=>	'edetail',
										'rel_param' => 'id='.$event['id']
								);							
								
								$tid = insert_topic($content,$title,$type="eventsubmit",$group="", $relay_id = 0, $fav_id = 0,$group_data ="",$attach_list=array(),$url_route);
								if($tid)
								{
									$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '".$GLOBALS['request']['source']."' where id = ".intval($tid));
								}
								
								$root['return'] = 1;
								$root['info'] = "报名成功";
							}
							else
							{
								$root['return'] = 0;
								$root['info'] = "报名失败";
							}
							
						}
					}
				}
				else
				{
					$root['return'] = 0;
					$root['info'] = "没有该活动数据";
				}
			}
			else
			{
				$root['return'] = 0;
				$root['info'] = "请先登录";
			}
			output($root);
			//报名
			

		}
		
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
		$page = 1;
		$event_id = intval($GLOBALS['request']['event_id']);
		if($page==1)
		{			
			$email = strim($GLOBALS['request']['email']);
			$pwd = strim($GLOBALS['request']['pwd']);		
			$result = do_login_user($email,$pwd);
			$GLOBALS['user_info'] = $user_data = es_session::get('user_info');			
			
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id." and is_effect = 1");
			
			if($event['xpoint']=='')
			{
				$event['xpoint']=0;
			}
			if($event['ypoint']=='')
			{
				$event['ypoint']=0;
			}
			
			//验证是否报名
			$is_submit = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_submit where user_id = ".intval($GLOBALS['user_info']['id'])." and event_id = ".$event['id']);
			
			
			$pattern = "/<img([^>]*)\/>/i";
			$replacement = "<img width=300 $1 />";


			$event['content'] = preg_replace($pattern, $replacement, get_abs_img_root($event['content']));
	
			
			$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_id." order by sort asc");
			foreach($event_fields as $k=>$v)
			{
				$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
			}
			$event['field_list'] = $event_fields;
			$event['is_submit'] = $is_submit;
		}
		$res = m_get_event_reply($event_id,$page);
		
		$event['comments'] =  $res['list'];
		$root['page'] = $res['page'];
		$root['return'] = 1;
		$root['item'] = $event;		
		output($root);
	}
}
?>