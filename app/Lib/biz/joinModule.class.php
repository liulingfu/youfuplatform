<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


class joinModule extends BizBaseModule
{
		
	public function index()
	{		
		app_redirect(url("biz","join#step1"));
//		$GLOBALS['tmpl']->assign("page_title","商家申请");		
//		$GLOBALS['tmpl']->display("biz/biz_join.html");
	}
	
	public function step1()
	{		
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		$deal_city_list = get_deal_citys();
		$GLOBALS['tmpl']->assign("city_list",$deal_city_list['ls']);
		
		$GLOBALS['tmpl']->assign("step",1);
		
		$GLOBALS['tmpl']->assign("page_title","商家入驻");		
		$GLOBALS['tmpl']->display("biz/biz_join_step1.html");
	}
	
	public  function step2()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}
		$location_id = intval($_REQUEST['location_id']);
		$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id." and is_effect = 1");
		if($location)
		{
			$account_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$GLOBALS['user_info']['merchant_name']."'");
			if($account_info&&$location['supplier_id']!=$account_info['supplier_id'])
			{
				showErr("这家商户不是您的，您不能认领");
			}
			else
			{				
				$data['name'] = $location['name'];
				$data['deal_cate_id'] = $location['deal_cate_id'];
				$deal_cate_type_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type_location_link where location_id = ".$location['id']);
				foreach($deal_cate_type_list as $type)
				{
					$data['deal_cate_type_id'][] = $type['deal_cate_type_id'];
				}
				$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_area_link where location_id = ".$location['id']);
				foreach($area_list as $area)
				{
					$data['area_id'][] = $area['area_id'];
				}
				$data['address'] = $location['address'];
				$data['xpoint'] = $location['xpoint'];
				$data['ypoint'] = $location['ypoint'];
				$data['tel'] = $location['tel'];
				$data['open_time'] = $location['open_time'];
				$data['location_id'] = $location['id'];
				$data['city_id'] = intval($location['city_id']);
			}
		}
		elseif($_POST)
		{
			$data['name'] =  addslashes(htmlspecialchars(trim($_REQUEST['name'])));
			$data['deal_cate_id'] = intval($_REQUEST['deal_cate_id']);
			foreach($_REQUEST['deal_cate_type_id'] as $type)
			{
				$data['deal_cate_type_id'][] = intval($type);
			}
			foreach($_REQUEST['area_id'] as $area)
			{
				$data['area_id'][] = intval($area);
			}
			$data['address'] = addslashes(htmlspecialchars(trim($_REQUEST['address'])));
			$data['xpoint'] = doubleval($_REQUEST['xpoint']);
			$data['ypoint'] = doubleval($_REQUEST['ypoint']);
			$data['tel'] = addslashes(htmlspecialchars(trim($_REQUEST['tel'])));
			$data['open_time'] = addslashes(htmlspecialchars(trim($_REQUEST['open_time'])));
			$data['location_id'] = 0;
			$data['city_id'] = intval($_REQUEST['city_id']);
		}
		else
		{
			app_redirect(url("biz","join#step1"));
		}
		
		$GLOBALS['tmpl']->assign("base_data",base64_encode(serialize($data)));		
		$GLOBALS['tmpl']->assign("step",2);		
		$GLOBALS['tmpl']->assign("page_title","签协议");		
		$GLOBALS['tmpl']->display("biz/biz_join_step2.html");		
	}
	
	
	public  function step3()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}		
		$base_data =  addslashes(htmlspecialchars(trim($_REQUEST['base_data'])));
		
		$data = unserialize(base64_decode($base_data));
		$location_id = intval($data['location_id']);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$GLOBALS['tmpl']->assign("base_data", $base_data);
		$GLOBALS['tmpl']->assign("step",3);
		
		$GLOBALS['tmpl']->assign("page_title","填信息");		
		$GLOBALS['tmpl']->display("biz/biz_join_step3.html");
	}
	
	public function step4()
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}
		
//		print_r( unserialize(base64_decode($_REQUEST['base_data'])));exit;
		
		//上传处理
		//创建attachment目录
		if($_POST)
		{
			if (!is_dir(APP_ROOT_PATH."public/attachment")) { 
		             @mkdir(APP_ROOT_PATH."public/attachment");
		             @chmod(APP_ROOT_PATH."public/attachment", 0777);
		        }
			
		    $dir = to_date(get_gmtime(),"Ym");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
		        
		    $dir = $dir."/".to_date(get_gmtime(),"d");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
		     
		    $dir = $dir."/".to_date(get_gmtime(),"H");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
	
			if($_FILES['h_license']['name']!="")
		    {
				$image_res = save_image_upload($_FILES,"h_license","attachment/".$dir,$whs=array(),0,1);				
				if(intval($image_res['error'])!=0)	
				{
					$messages[] = "营业执照图片".$image_res['message'];
				}
				else 
				{
					if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		        	{
		        		$paths = pathinfo($image_res['h_license']['url']);
		        		$path = str_replace("./","",$paths['dirname']);
		        		$filename = $paths['basename'];
		        		$pathwithoupublic = str_replace("public/","",$path);
			        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
			        	@file_get_contents($syn_url);
		        	}
	
		        	$save_data['h_license'] = $image_res['h_license']['url'];
		        	
				}
		    }
		    else {
		    	$messages[] = "营业执照必需上传";
		    }
		    
			if($_FILES['h_other_license']['name']!="")
		    {
				$image_res = save_image_upload($_FILES,"h_other_license","attachment/".$dir,$whs=array(),0,1);	
				if(intval($image_res['error'])!=0)	
				{
					$messages[] = "其他资质图片".$image_res['message'];
				}
				else 
				{
					if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		        	{
		        		$paths = pathinfo($image_res['h_other_license']['url']);
		        		$path = str_replace("./","",$paths['dirname']);
		        		$filename = $paths['basename'];
		        		$pathwithoupublic = str_replace("public/","",$path);
			        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
			        	@file_get_contents($syn_url);
		        	}
					$save_data['h_other_license'] = $image_res['h_other_license']['url'];
				}
		    }
		    
			if($_FILES['h_supplier_logo']['name']!="")
		    {
				$image_res = save_image_upload($_FILES,"h_supplier_logo","attachment/".$dir,$whs=array(),0,1);	
				if(intval($image_res['error'])!=0)	
				{
					$messages[] = "商家logo".$image_res['message'];
				}
				else 
				{
					if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		        	{
		        		$paths = pathinfo($image_res['h_supplier_logo']['url']);
		        		$path = str_replace("./","",$paths['dirname']);
		        		$filename = $paths['basename'];
		        		$pathwithoupublic = str_replace("public/","",$path);
			        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
			        	@file_get_contents($syn_url);
		        	}
					$save_data['h_supplier_logo'] = $image_res['h_supplier_logo']['url'];
				}
		    }
		    
			if($_FILES['h_supplier_image']['name']!="")
		    {
				$image_res = save_image_upload($_FILES,"h_supplier_image","attachment/".$dir,$whs=array(),0,1);	
				if(intval($image_res['error'])!=0)	
				{
					$messages[] = "门店图片".$image_res['message'];
				}
				else 
				{
					if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		        	{
		        		$paths = pathinfo($image_res['h_supplier_image']['url']);
		        		$path = str_replace("./","",$paths['dirname']);
		        		$filename = $paths['basename'];
		        		$pathwithoupublic = str_replace("public/","",$path);
			        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
			        	@file_get_contents($syn_url);
		        	}
					$save_data['h_supplier_image'] = $image_res['h_supplier_image']['url'];
				}
		    }
		    
		    if(count($messages)==0)
		    {
		    	$base_data = unserialize(base64_decode($_REQUEST['base_data']));
		    	$save_data['name'] = $base_data['name'];
		    	$save_data['cate_config'] = serialize(array('deal_cate_id'=>$base_data['deal_cate_id'],'deal_cate_type_id'=>$base_data['deal_cate_type_id']));
		   		$save_data['location_config'] = serialize($base_data['area_id']);
		   		$save_data['address'] = $base_data['address'];
		   		$save_data['tel'] = $base_data['tel'];
		   		$save_data['open_time'] = $base_data['open_time'];
		   		$save_data['xpoint'] = $base_data['xpoint'];
		   		$save_data['ypoint'] = $base_data['ypoint'];
		   		$save_data['location_id'] = $base_data['location_id'];
		   		$save_data['user_id'] = $user_id;
		   		$save_data['create_time'] = get_gmtime();
		   		$save_data['h_name'] = addslashes(htmlspecialchars(trim($_REQUEST['h_name'])));
		   		$save_data['h_faren'] = addslashes(htmlspecialchars(trim($_REQUEST['h_faren'])));
		   		$save_data['h_user_name'] = addslashes(htmlspecialchars(trim($_REQUEST['h_user_name'])));
		   		$save_data['h_tel'] = addslashes(htmlspecialchars(trim($_REQUEST['h_tel'])));
		   		
		   		if($save_data['h_name']=='')
		   		$messages[] = "请填写企业名称";
		   		
		   		if($save_data['h_faren']=='')
		   		$messages[] = "请填写法人姓名";
		   		
		   		if(count($messages)==0)	   		
		   		{
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_submit",$save_data);
					app_redirect(url("biz","join#step4"));
		   		}
				else
				{
					$GLOBALS['tmpl']->assign("error_messages",$messages);
				}
		    }
		    else 
		    {
		    	$GLOBALS['tmpl']->assign("error_messages",$messages);
		    }
		}	
	    
		$GLOBALS['tmpl']->assign("step",4);		
		$GLOBALS['tmpl']->assign("page_title","完成");		
		$GLOBALS['tmpl']->display("biz/biz_join_step4.html");
//		print_r($_REQUEST);
//		print_r( unserialize(base64_decode($_REQUEST['base_data'])));
	}
	
	public function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['id']);
		$type_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$cate_id);
		$html = "";
		foreach($type_list as $item)
		{
			$html.="<input type='checkbox' name='deal_cate_type_id[]' value='".$item['id']."' />".$item['name']."&nbsp;&nbsp;";
		}

		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
	public function load_city_area()
	{
		$city_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = 0 order by sort desc");
		$html = "";
		if($area_list)
		{
			$html = "<select name='area_id[]'>";
			foreach($area_list as $item)
			{
				$html .= "<option value='".$item['id']."'>".$item['name']."</option>";
			}
			$html.="</select>";
		}
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
		
	}
	
	public function load_quan_list()
	{
		$area_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where pid = ".$area_id." order by sort desc");
		$html = "";
		foreach($area_list as $item)
		{
			$html.="<input type='checkbox' name='area_id[]' value='".$item['id']."' />".$item['name']."&nbsp;&nbsp;";
		}

		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
}
?>