<?php
class fasongduanxin
{
	public function index()
	{
		
		$mobile=strim($GLOBALS['request']['user_phoneNum']);
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);
		//print_r($email);echo"<br />";print_r($pwd);exit;
		
		//检查用户,用户密码
		$user_return = user_check($email,$pwd);
		$user = $user_return;
		$user_id  = intval($user['id']);
		//print_r($user_id);exit;
		if($user_id==0)
		{
			require_once APP_ROOT_PATH."app/Lib/insert_libs.php";
			$root['status']=0;
			$root['html']= insert_load_login_form();	
		}
		else
		{
			$root['status'] = 1;
			$root['info']='';
			$root['send_limit']=app_conf("YOUHUI_SEND_LIMIT");
			$now = get_gmtime();
			$today_begin = to_timespan(to_date($now,"Y-m-d"));
			$today_end = $today_begin + 24*3600;
			
			$youhui_id = intval($GLOBALS['request']['id']);
			$integral=$GLOBALS['db']->getOne("select return_score from ".DB_PREFIX."youhui where id=".$youhui_id);
                    
			//print_r($youhui_id);exit;
			//$score_num=$GLOBALS['db']->getOne("select score_num  from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);
			//$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where user_id = ".$user_id." and  create_time between ".$today_begin." and ".$today_end);
			//print_r($count);exit;
			//$root['send_count']=$count;
			//$num=intval(app_conf("YOUHUI_SEND_LIMIT"));
			//print_r($num);exit;
			//if($count>=$num)
			//{
				// $root['info'] = "您今天已经超出下载限额";
			//}
			//else
			//{
				//if($score_num>0){
					//$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where user_id = ".$user_id." and youhui_id=".$youhui_id);
					//$num =($score_num>intval(app_conf("YOUHUI_SEND_LIMIT")))?intval(app_conf("YOUHUI_SEND_LIMIT")):$score_num;	
					//if($count>=$num){
						 //$root['info'] = "您已经超出该优惠券的下载限额";
						// output($root);
					//}
					
				//}
				//$youhui_id = intval($GLOBALS['request']['id']);
				$youhui_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where is_effect = 1 and id =".$youhui_id);
                                //print_r($youhui_info);exit;
				if(!$youhui_info)
				{
					$root['info'] ="优惠券不存在";
					output($root);
				}

				if($youhui_info['end_time']>0&&$youhui_info['end_time']<get_gmtime())
				{
					$root['info'] ="优惠券已过期";
					output($root);
				}
				
				if($youhui_info['is_sms']==0)
				{
					$root['info'] ="该优惠券不支持短信下载";
					output($root);
				}
				// if($youhui_info['count']<=0)
				//{
						//$root['info']="该优惠券已发完";
						//output($root);
				//}
				
				 $root['youhui_info']=$youhui_info;
				 //短信模板配置发送短信
                $info=send_youhui_sms($youhui_id,$user_id,$mobile);
                //print_r($info);exit;
                $root['info']=$info['content'];
                // print_r($root['info']);exit;
                $sn=gen_verify_youhui_to_mobile($youhui_id,$mobile,$user_id,1);//记录到youhui_log中
               // print_r($sn);exit;
                if(!empty($sn)){
                	 $root['info'].="验证码为:$sn";
                		
                }
               	
				//$GLOBALS['db']->query("update ".DB_PREFIX."youhui set count = count-1 where id = ".$youhui_id);	
				//$GLOBALS['db']->query("update ".DB_PREFIX."youhui set sms_count = sms_count+1,count=count-1,view_count=view_count+1 where id = ".$youhui_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."user set score = score+".$integral." where id = ".$user_id);	
			//}
			
						
		}
		output($root);
	}
}
?>