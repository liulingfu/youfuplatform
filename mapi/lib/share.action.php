<?php
class share
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);
		$id = intval($GLOBALS['request']['share_id']);
		
		$result = do_login_user($email,$pwd);
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		$act2 =  strim($GLOBALS['request']['act_2']);
		$source = strim($GLOBALS['request']['source']);
		$source = str_replace("来自","",$source);
		
		$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$id);
		if($topic)
		{
			switch($act2)
			{
				case 'follow':
					//开始关注
					$user_id = intval($user_data['id']);
					$focus_uid = intval($topic['user_id']);
					if($user_id!=$focus_uid)
					{					
						$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
						if(!$focus_data&&$user_id>0&&$focus_uid>0)
						{
								$focused_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$focus_uid);
								$focus_data = array();
								$focus_data['focus_user_id'] = $user_id;
								$focus_data['focused_user_id'] = $focus_uid;
								$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
								$focus_data['focused_user_name'] = $focused_user_name;
								$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
								$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
								$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
	
						}
						elseif($focus_data&&$user_id>0&&$focus_uid>0)
						{
							$GLOBALS['db']->query("delete from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
							$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count - 1 where id = ".$user_id);
							$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count - 1 where id = ".$focus_uid);		

						}
					}
					//开始关注
				break;

				case 'collect':
					//开始喜欢					
										
							if($topic['user_id']!=intval($user_data['id']))
							{													
								$fav_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where fav_id = ".$id." and user_id = ".intval($user_data['id']));
								if($fav_topic)
								{
									$GLOBALS['db']->query("delete from ".DB_PREFIX."topic where id = ".$fav_topic['id']);
									$GLOBALS['db']->query("update ".DB_PREFIX."topic set fav_count = fav_count - 1 where id = ".$id);
                                                                        if( $id != $topic['origin_id'])
                                                                        {
									$GLOBALS['db']->query("update ".DB_PREFIX."topic set fav_count = fav_count - 1 where id = ".$topic['origin_id']);
                                                                        }
									$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_image where topic_id = ".$fav_topic['id']);
									$topic['fav_count']-=1;
								}
								else
								{						
									$tid = insert_topic($cnt="",$title="",$type="",$group="",$r=0,$f=$id);	
									if($tid)
									{
										$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '".$source."' where id = ".intval($tid));
									}	
									$topic['fav_count']+=1;			
								}
							}
						
					
					//end喜欢
				break;
			}	
		
		
		
		$share_item = m_get_topic_item($topic);		
        if($topic['user_id']==$user_data['id'])
        {
        	$share_item['is_follow_user'] = -1;
        }
        else
        {
			$focus_uid = intval($topic['user_id']);
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_data['id']." and focused_user_id = ".$focus_uid);
			if($focus_data)
			$share_item['is_follow_user'] = 1;
			else
			$share_item['is_follow_user'] = 0;			
			$share_item['is_collect_share'] = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."topic where fav_id = ".$topic['id']." and user_id = ".$user_data['id']);
        }
        
		$share_item['comments'] = m_get_topic_reply($topic['id'],1);
		$share_item['collects'] = m_get_topic_fav($topic['id']);
		$share_item['imgs'] = m_get_topic_img($topic);
        
		$root = array();
		$root['return'] = 1;
		$root['item'] = $share_item;
		}
		else
		$root['return'] = 0;
		
		output($root);
	}
}
?>