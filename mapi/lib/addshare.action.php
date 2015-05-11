<?php
class addshare
{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$root = array();		
		$content = strim($GLOBALS['request']['content']);
		$source = strim($GLOBALS['request']['source']);
		$source = str_replace("来自","",$source);
		$email = strim($GLOBALS['request']['email']);
		$pwd = strim($GLOBALS['request']['pwd']);

		
		$result = do_login_user($email,$pwd);
		
		$GLOBALS['user_info'] = $user_data = es_session::get('user_info');
		$user_data['id'] = intval($user_data['id']);
		if(intval($user_data['id'])==0)
		{
			$root['return'] = 0;
			$root['info'] = "请先登录";
			output($root);			
		}
		
		if(isset($_FILES['image_1']))
		{
			//开始上传
			//上传处理
			//创建comment目录
			if (!is_dir(APP_ROOT_PATH."public/comment")) { 
		             @mkdir(APP_ROOT_PATH."public/comment");
		             @chmod(APP_ROOT_PATH."public/comment", 0777);
		        }
			
		    $dir = to_date(get_gmtime(),"Ym");
		    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/comment/".$dir);
		             @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		        }
		        
		    $dir = $dir."/".to_date(get_gmtime(),"d");
		    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/comment/".$dir);
		             @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		        }
		     
		    $dir = $dir."/".to_date(get_gmtime(),"H");
		    if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/comment/".$dir);
		             @chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		        }
		        
		         
	   		if(app_conf("IS_WATER_MARK")==1)
	   		$img_result = save_image_upload($_FILES,"image_1","comment/".$dir,$whs=array('thumb'=>array(100,100,1,0)),1,1);	
			else
	   		$img_result = save_image_upload($_FILES,"image_1","comment/".$dir,$whs=array('thumb'=>array(100,100,1,0)),0,1);	
			
			
			if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
        	{
        		$paths = pathinfo($img_result['topic_image']['url']);
        		$path = str_replace("./","",$paths['dirname']);
        		$filename = $paths['basename'];
        		$pathwithoupublic = str_replace("public/","",$path);
	        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
	        	@file_get_contents($syn_url);
        	}
			
			require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$image = new es_imagecls();
			$info = $image->getImageInfo($img_result['image_1']['path']);
			
			$image_data['width'] = intval($info[0]);
			$image_data['height'] = intval($info[1]);
			$image_data['name'] = valid_str($_FILES['image_1']['name']);
			$image_data['filesize'] = filesize($img_result['image_1']['path']);
			$image_data['create_time'] = get_gmtime();
			$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
			$image_data['user_name'] = addslashes($GLOBALS['user_info']['user_name']);
			$image_data['path'] = $img_result['image_1']['thumb']['thumb']['url'];
			$image_data['o_path'] = $img_result['image_1']['url'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);	
			$image_id = intval($GLOBALS['db']->insert_id());
			//end 上传
		}
		
		if($image_id>0)
		$attach_list = array(array("id"=>$image_id,"type"=>"image"));
		else
		$attach_list = array();

		$id = insert_topic(valid_str($content),$title="",$type="",$group="",$relay_id=0,$fav_id=0,$group_data=array(),$attach_list);
		if($id)
		{
			increase_user_active($user_data['id'],"发表了一则分享");
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '".$source."' where id = ".intval($id));
		}
		
		$syn_data['content'] = $content;
		//$syn_data['img'] = get_abs_img_root($GLOBALS['db']->getOne("select o_path from ".DB_PREFIX."topic_image where id = ".intval($image_id)));
		$syn_data['img'] = $GLOBALS['db']->getOne("select o_path from ".DB_PREFIX."topic_image where id = ".intval($image_id));
		if($syn_data['img'])
		$syn_data['img'] = APP_ROOT_PATH.$syn_data['img'];
		

		//开始同步
		if(intval($GLOBALS['request']['is_syn_sina']))
		{
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set is_syn_sina = 1 where id = ".intval($user_data['id']));
			//$func_name = strim($GLOBALS['request']['type'])."_Sina";
			//$result_sina = $func_name($syn_data);
			$result_sina = Sina($syn_data);
			$ext_info ="";
			if(!$result_sina['status'])
			{
				if(intval($result_sina['code'])==21316||intval($result_sina['code'])==21317) $ext_info .= " 请先绑定新浪微博";				
				if(intval($result_sina['code'])==21314||
					intval($result_sina['code'])==21315) $ext_info .= " 新浪微博授权过期";
			}
		}
		
		if(intval($GLOBALS['request']['is_syn_tencent']))
		{
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set is_syn_tencent = 1 where id = ".intval($user_data['id']));
//			$func_name = strim($GLOBALS['request']['type'])."_Tencent";
//			$result_tencent = $func_name($syn_data);
			$result_tencent = Tencent($syn_data);

			
			if(!$result_tencent['status'])
			{
				if(intval($result_tencent['code'])==0) $ext_info .= " 请先绑定腾讯微博";
				if(intval($result_tencent['code'])==14) $ext_info .= " 腾讯微博未实名认证";
				if(intval($result_tencent['code'])==10017||
					intval($result_tencent['code'])==10018||
					intval($result_tencent['code'])==10019||
					intval($result_tencent['code'])==36||
					intval($result_tencent['code'])==37||
					intval($result_tencent['code'])==38) $ext_info .= " 腾讯微博授权过期";
			}
		}
		
		
		$root['return'] = 1;
		$root['status'] = 1;
		
		
		
		$root['info'] = "发布成功".$ext_info;
		output($root);
	}
}

function Tencent($data)
{
			require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
			OAuth::init($GLOBALS['m_config']['tencent_app_key'],$GLOBALS['m_config']['tencent_app_secret']);	
			
			$uid = intval($GLOBALS['user_info']['id']);
			$udata = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid);
			
			
			
			es_session::set("t_access_token",$udata['t_access_token']);
			es_session::set("t_openid",$udata['t_openid']);
			es_session::set("t_openkey",$udata['t_openkey']);
			if (es_session::get("t_access_token")|| (es_session::get("t_openid")&&es_session::get("t_openkey"))) 
			{		
				if(!empty($data['img']))
				{
					 $params = array(
			        	'content' => $data['content'],
					 	'clientip'	=>	get_client_ip(),
					 	'format'	=>	'json'
				    );
				    $multi = array('pic' => $data['img']);
				   
				    $r = Tencent::api('t/add_pic', $params, 'POST', $multi);
				}
				else
				{
					 $params = array(
			        	'content' => $data['content'],
					 	'clientip'	=>	get_client_ip(),
					 	'format'	=>	'json'
				    );
				    $r = Tencent::api('t/add', $params, 'POST');
				}
				
				
				$msg = json_decode($r,true);
				
	
				
				if(intval($msg['errcode'])==0)
				{
					$result['status'] = true;
					$result['code'] = 0;
					return $result;
				}
				else
				{
					$result['status'] = false;
					$result['code'] = $msg['errcode'];
					return $result;
				}
								
			}
}


function Sina($data)
{
			require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
			$uid = intval($GLOBALS['user_info']['id']);
			$udata = $GLOBALS['db']->getRow("select sina_token from ".DB_PREFIX."user where id = ".$uid);
			$client = new SaeTClientV2($GLOBALS['m_config']['sina_app_key'],$GLOBALS['m_config']['sina_app_secret'],$udata['sina_token']);
			
	
			if(empty($data['img']))
				$msg = $client->update($data['content']);
			else
				$msg = $client->upload($data['content'],$data['img']);

				
			if($msg['error'])
			{
				$result['status'] = false;
				$result['code'] = $msg['error_code'];
			
			}
			else
			{
				$result['status'] = true;
				$result['code'] = 0;
			
			}			
			return $result;
}
?>