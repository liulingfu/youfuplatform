<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class reviewModule extends YouhuiBaseModule
{
	public function ajax_list()
	{
		$is_best = intval($_REQUEST['is_best']);
		$supplier_location_id = intval($_REQUEST['supplier_location_id']);
		$filter = isset($_REQUEST['filter'])?$_REQUEST['filter']:"";
		$condition = " status = 1 and supplier_location_id = ".$supplier_location_id." ";
		if($filter!="")
		{
			if($filter=="bad")
			{
				$condition.=" and point < 2 ";
			}
			elseif($filter=='good')
			{
				$condition.=" and point >= 2 ";
			}
			elseif($filter=='is_buy')
			{
				$condition.=" and is_buy = 1 ";
			}
			elseif($filter=='tuan')
			{
				$condition.=" and from_data = 'tuan' ";
			}
			elseif($filter=='event')
			{
				$condition.=" and from_data = 'event' ";
			}
			elseif($filter=='youhui')
			{
				$condition.=" and from_data = 'youhui' ";
			}
			elseif($filter=='daijin')
			{
				$condition.=" and from_data = 'daijin' ";
			}
		}
		$sort = isset($_REQUEST['sort'])?$_REQUEST['sort']:"create_time";
		if($sort=="good_count")
		{
			$sort_str = " is_top desc, good_count desc ";
		}
		elseif($sort=="reply_count")
		{
			$sort_str = " is_top desc, reply_count desc ";
		}
		else 
		{
			$sort_str = " is_top desc, create_time desc ";
		}
		
		
		$is_best_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where ".$condition." and is_best = 1");
		$total_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where ".$condition);
		
		if($is_best==1)
		{
			$count = $is_best_count;
			$condition.=" and is_best = 1 ";
		}
		else
		$count = $total_count;
		
		$page_size = 5; 
		$upara = array(
			"is_best"=>$is_best,
			"filter"=>$filter,
			"sort"=>$sort,
			"supplier_location_id"=>$supplier_location_id
		);
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;			
		
		$sqlList = "select * from ".DB_PREFIX."supplier_location_dp where ".$condition." order by ".$sort_str." limit ".$limit;
		$review_list = $GLOBALS['db']->getAll($sqlList);

		foreach($review_list as $k=>$v)
		{
			$review_list[$k] = sys_get_dp_detail($v);	
			$review_list[$k]['create_time_format'] = pass_date($v['create_time']);
			if($v['from_data']!="")
			{
				$review_list[$k]['rel_url'] = parse_url_tag("u:".$v['rel_app_index']."|".$v['rel_route']."|".$v['rel_param']);	
				$review_list[$k]['rel_name'] = $GLOBALS['lang']['FROM_DATA_'.strtoupper($v['from_data'])];
			}	
		}
		
		
		$GLOBALS['current_url'] = url("youhui","review#ajax_list",$upara);		
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("is_best_count",$is_best_count);
		$GLOBALS['tmpl']->assign("total_count",$total_count);
		$GLOBALS['tmpl']->assign("supplier_location_id",$supplier_location_id);
		$GLOBALS['tmpl']->assign("filter",$filter);
		$GLOBALS['tmpl']->assign("is_best",$is_best);
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign('review_list',$review_list);
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$return['html']=decode_topic($GLOBALS['tmpl']->fetch("inc/review/review_list.html"));
		ajax_return($return);
		exit();
	}
	
	public function savereview(){
		$return["status"]=0;
		if(!$GLOBALS['user_info']){
			$return["status"]=2;
			$return["message"]=$GLOBALS['LANG']["PLEASE_LOGIN_FIRST"];
			ajax_return($return);
			exit();
		}
		
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				$return["message"]=$GLOBALS['lang']['VERIFY_CODE_ERROR'];
				ajax_return($return);
				exit();
			}
		}
		
		es_session::delete("verify");
		
		//创建基础点评数据
		$dp_data = array();
		$dp_data['title'] = addslashes(htmlspecialchars($_REQUEST['dp_title']));
		$dp_data['content'] = addslashes(htmlspecialchars($_REQUEST['content']));
		$dp_data['create_time'] = get_gmtime();
		$dp_data['point'] = intval($_REQUEST['dp_point']);
		$dp_data['user_id'] = intval($GLOBALS['user_info']['id']);
		$dp_data['supplier_location_id'] = intval($_REQUEST['supplier_location_id']);
		$dp_data['status'] = 1;
		if(count($_REQUEST['pics'])>0)
		{
			$dp_data['is_img'] = 1;
		}
		$dp_data['avg_price'] = floatval($_REQUEST['avg_price']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp", $dp_data ,"INSERT");
		$dp_id = $GLOBALS['db']->insert_id();
		if($dp_id>0)
		{
			increase_user_active(intval($GLOBALS['user_info']['id']),"发表了一则商户点评");
			$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
			//创建点评图库
			if(isset($_REQUEST['pics']) && is_array($_REQUEST['pics']) && count($_REQUEST['pics']) > 0)
			{
				$photos = $_REQUEST['pics'];
				foreach($photos as $pkey => $photo)
				{							
					$c_data = array();
					$c_data['image'] = $photo;
					$c_data['sort'] = 10;
					$c_data['create_time'] = get_gmtime();
					$c_data['user_id'] = intval($GLOBALS['user_info']['id']);
					$c_data['supplier_location_id'] = intval($_REQUEST['supplier_location_id']);
					$c_data['dp_id'] = $dp_id;
					$c_data['brief'] = addslashes(htmlspecialchars($_REQUEST['brief'][$pkey]));
					$c_data['status'] = 0;
					$c_data['image_type'] = intval($_REQUEST['type'][$pkey]);		
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_images", $c_data,"INSERT");
				}
			}
			
			//创建点评评分
			$point_Group = $_REQUEST['dp_point_group'];
			foreach($point_Group as $group_id => $point)
			{
				$point_data = array();
				$point_data['group_id'] = $group_id;
				$point_data['dp_id'] = $dp_id;
				$point_data['supplier_location_id'] = intval($_REQUEST['supplier_location_id']);
				$point_data['point'] = intval($point);
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_point_result", $point_data,"INSERT");
			}
			
			//创建点评分组的标签
			$tag_group = $_REQUEST['dp_tags'];
			foreach($tag_group as $group_id => $tag_row)
			{
				if (trim($tag_row)!=''){
					$arr_rer = array(",","，");
					$arr_rep = array(" "," ");
					$tag_row = str_replace($arr_rer,$arr_rep,$tag_row);
					
					$tag_row_data = array();
					$tag_row_data['tags'] = $tag_row;
					$tag_row_data['dp_id'] = $dp_id;
					$tag_row_data['supplier_location_id'] = intval($_REQUEST['supplier_location_id']);
					$tag_row_data['group_id'] = $group_id;
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_tag_result", $tag_row_data, "INSERT");
					
					review_supplier_location_match(intval($_REQUEST['supplier_location_id']),$tag_row,$group_id);
				}			
			}
			
			//分享
			$supplier_info = $GLOBALS['db']->getRow("select name,id,new_dp_count_time from ".DB_PREFIX."supplier_location where id = ".$dp_data['supplier_location_id']);
			$title = "对".$supplier_info['name']."发表了点评";
			$url_route = array(
					'rel_app_index'	=>	'youhui',
					'rel_route'	=>	'store#view',
					'rel_param' => 'id='.$supplier_info['id']
				);
			$tid = insert_topic($dp_data['content'],$title,"slocationcomment",$group="", $relay_id = 0, $fav_id = 0,$group_data = "",$attach_list=array(),$url_route);
			if($tid)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
			}
			
			//更新统计
			syn_supplier_locationcount($supplier_info);
			cache_store_point($supplier_info['id']);
			$cache_id  = md5("store"."view".$supplier_info['id']);		
			$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
			
			$return['status'] = 1;
		}
		else{
			$return['message'] = "数据库异常，提交失败";
		}
		ajax_return($return);
		exit();
	}
	
	public function reply(){
		$return["status"]=0;
		if(!$GLOBALS['user_info']){
			$return["status"]=2;
			$return["message"]=$GLOBALS['LANG']["PLEASE_LOGIN_FIRST"];
			ajax_return($return);
			exit();
		}
		
		//验证码
		if(app_conf("VERIFY_IMAGE")==1)
		{
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{				
				$return["message"]=$GLOBALS['lang']['VERIFY_CODE_ERROR'];
				ajax_return($return);
				exit();
			}
		}
		
		$content = htmlspecialchars(addslashes(valid_str($_REQUEST['content'])));
		$uid = intval($GLOBALS["user_info"]['id']);
		$pid = intval($_REQUEST['pid']);
		$dp_id = intval($_REQUEST['dp_id']);
		$page = intval($_REQUEST['page']);
		
		if(!check_ipop_limit(get_client_ip(),"dpsign",10,$dp_id))
		{
			$return['message']='请勿频繁回应';
			ajax_return($return);
			exit();
		}
		
		es_session::delete("verify");
		
		$dp_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$dp_id);
		$merchant_info = $GLOBALS['db']->getRow("select name,id from ".DB_PREFIX."supplier_location where id = ".$dp_info['supplier_location_id']);
			
		$reply_data = array();
		$reply_data['dp_id'] = $dp_id;
		$reply_data['content'] = $content;
		$reply_data['user_id'] = $uid;
		$reply_data['parent_id'] = $pid;
		$reply_data['create_time'] = get_gmtime();

		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_reply", $reply_data, "INSERT");
		$rid = 	$GLOBALS['db']->insert_id();		
		
		if($rid>0)
		{
			$syn_reply = intval($_REQUEST['syn_reply']);
			if($syn_reply==1)
			{
				$s_account_info = es_session::get("account_info");
				if(in_array($dp_info['supplier_location_id'],$s_account_info['location_ids'])&&$dp_info['from_data']!="")
				{
					//验证通过
					$message_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message where id = ".intval($dp_info['message_id']));
					if($message_info)
					{
						$message_info['admin_reply'] = $content;
						$message_info['update_time'] = get_gmtime();
						$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message_info,"UPDATE","id=".$message_info['id']);						
						send_user_msg("商户回复了你的点评","商户回复了你的点评 [<a href='".url("youhui","review#detail",array("id"=>$dp_info['id']))."' target='_blank'>".$dp_info['title']."</a>]",0,$dp_info['user_id'],get_gmtime(),0,1,1);
					}
				}
			}
			increase_user_active(intval($GLOBALS['user_info']['id']),"回应了一则商户点评");
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location_dp set reply_count = reply_count + 1 where id = ".$dp_id);
		}			
					
			
		//输出回应列表
		$sql_count = "select count(*) from ".DB_PREFIX."supplier_location_dp_reply where dp_id = ".$dp_id;
		$count = $GLOBALS['db']->getOne($sql_count);		
		$page_size =app_conf("PAGE_SIZE");
      	if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");		
        $sql = "select * from ".DB_PREFIX."supplier_location_dp_reply where dp_id = ".$dp_id."  order by create_time desc limit ".$limit;
    
        $reply_list = $GLOBALS['db']->getAll($sql);
        
		foreach($reply_list as $k=>$v)
        {
        	$reply_list[$k]['user_name'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
        	$reply_list[$k]['create_time_format'] = pass_date($v['create_time']);
        }
        
        $GLOBALS['current_url'] = $_SERVER['REQUEST_URI'] = url("youhui","review#detail",array("id"=>$dp_id));
        $page = new Page($count,$page_size);   //初始化分页对象 		
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
        
		$GLOBALS['tmpl']->assign("reply_list",$reply_list);		
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$html = decode_topic($GLOBALS['tmpl']->fetch("inc/review/reply_list.html"));
		$return = array("status"=>1,"message"=>$html);
		ajax_return($return);
		exit();
	}
	
	public function flower(){
		$type = $_REQUEST['type']=='bad_count'?'bad_count':'good_count';
		$rec_id = intval($_REQUEST['rec_id']);
		$rec_module = addslashes($_REQUEST['rec_module']);
		$memo =  addslashes($_REQUEST['memo']);
		$uid = intval($GLOBALS['user_info']['id']);
		if($uid==0)
		{
			$result['status'] = 2;
			$result["message"]=$GLOBALS['LANG']["PLEASE_LOGIN_FIRST"];
			ajax_return($result);
			exit;
		}
		
		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."flower_log where user_id = ".$uid." and rec_id=".$rec_id." and rec_module = '".$rec_module."'");
		if($rs)
		{
			$result['status'] = 0;
			$result['message']	=	"您已经投票过了";
			ajax_return($result);
		}
		else
		{
			if($rec_module=='dp') $table = "supplier_location_dp";
			if($rec_module=='image') $table = "supplier_location_images";
			
			if($GLOBALS['db']->getOne("select user_id from ".DB_PREFIX.$table." where id = ".$rec_id)==$uid)
			{
				$result['status'] = 0;
				$result['message']	=	"不能投自己评论的!";
				ajax_return($result);
			}
			$data['user_id'] = $uid;
			$data['type'] = $type;
			$data['rec_id'] = $rec_id;
			$data['rec_module'] = $rec_module;
			$data['create_time'] = get_gmtime();
			$data['memo'] = $memo;
			$GLOBALS['db']->autoExecute(DB_PREFIX."flower_log",$data,true);
			
			$GLOBALS['db']->query("update ".DB_PREFIX.$table." set ".$type." = ".$type." + 1 where id = ".$rec_id);
			$result['status'] = 1;
			$result['message']	=	intval($GLOBALS['db']->getOne("select $type from ".DB_PREFIX.$table." where id = ".$rec_id));
			ajax_return($result);
			
		}
	}
	
	public function detail(){
		$dp_id = intval($_REQUEST['id']);
		$page = intval($_REQUEST['p']);
		$review_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$dp_id);
		if(!$review_item)
		{
			app_redirect(url("index","index"));
		}
		
		//验证是否为当前商家会员管理的点评
		$s_account_info = es_session::get("account_info");
		if(in_array($review_item['supplier_location_id'],$s_account_info['location_ids'])&&$review_item['from_data']!="")
		{
			$is_admin = 1;
			$GLOBALS['tmpl']->assign("is_admin",$is_admin);
		}
		
		$review_item = sys_get_dp_detail($review_item);
		$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$review_item['supplier_location_id']);
		
 		
		//供应商的地址列表
		//定义location_id
		$locations = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where id = ".$review_item['supplier_location_id']);
			
			$json_location = array();
			$location_ids = array(0);
			foreach($locations as $litem)
			{
				$location_ids[] = $litem['id'];
				$arr = array();
				$arr['title'] = $litem['name'];
				$arr['address'] = $litem['address'];
				$arr['tel'] = $litem['tel'];
				$arr['lng'] = $litem['xpoint'];
				$arr['lat'] = $litem['ypoint'];
				$json_location[] = $arr;
			}
			
			$GLOBALS['tmpl']->assign("json_location",json_encode($json_location));
			$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->assign("store_info",$store_info);
 		//输出回应列表
		$sql_count = "select count(*) from ".DB_PREFIX."supplier_location_dp_reply  where dp_id = ".$dp_id;
		$count = $GLOBALS['db']->getOne($sql_count);		
		$page_size =app_conf("PAGE_SIZE");
		
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;	
		
        $sql = "select * from ".DB_PREFIX."supplier_location_dp_reply where dp_id = ".$dp_id."  order by create_time desc limit ".$limit;
    
        $reply_list = $GLOBALS['db']->getAll($sql);
        
        foreach($reply_list as $k=>$v)
        {
        	$reply_list[$k]['user_name'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
        	$reply_list[$k]['create_time_format'] = pass_date($v['create_time']);
        }
        
       $page = new Page($count,$page_size);   //初始化分页对象 		
	   $p  =  $page->show();
	   $GLOBALS['tmpl']->assign('pages',$p);

	   
	   $GLOBALS['tmpl']->assign('reply_list',$reply_list);  
	    $GLOBALS['tmpl']->assign('user_auth',get_user_auth());
	   $review_list_html = decode_topic($GLOBALS['tmpl']->fetch('inc/review/reply_list.html'));
	   $GLOBALS['tmpl']->assign('review_list_html',$review_list_html);  
	   $GLOBALS['tmpl']->assign('review_item',$review_item);  
	  
	   $GLOBALS['tmpl']->display('review_detail.html');
	}
	
}


function review_supplier_location_match($location_id,$tags,$group_id){
	$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
	if($location)
	{
		$location['tags_match'] = "";
		$location['tags_match_row'] = "";
		
		//标签
		$tags_arr = explode(" ",$tags);
		foreach($tags_arr as $tgs){
			//同步 supplier_tag 表
			$tag_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_tag where tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);
			if($tag_data)
			{
				$tag_data['total_count'] = intval($tag_data['total_count'])+1 ;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data,"UPDATE", "tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);
				
			}
			else
			{
				$tag_data['tag_name'] = trim($tgs);
				$tag_data['supplier_location_id'] = $location_id;
				$tag_data['group_id'] = $group_id;
				$tag_data['total_count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data, "INSERT");
			}
			insert_match_item(trim($tgs),"supplier_location",$location_id,"tags_match");
		}
	}	
}



?>