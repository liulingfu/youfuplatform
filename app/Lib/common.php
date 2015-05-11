<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

//app项目用到的函数库

/**
 *  获取团购城市列表
 */
function get_deal_citys()
{
	return load_auto_cache("city_list_result");
}

/**
 * 获取当前团购城市
 */
function get_current_deal_city()
{		
	$city_list = load_auto_cache("city_list_result");
	$city_list = $city_list['ls'];
	if(es_cookie::is_set("deal_city"))
	{	
		$deal_city_id = es_cookie::get("deal_city");
		$deal_city = $city_list[$deal_city_id];
	}
	
	if(!$deal_city)
	{
		//设置如存在的IP订位
		if(file_exists(APP_ROOT_PATH."system/extend/ip.php"))
		{			
			require_once APP_ROOT_PATH."system/extend/ip.php";
			$ip =  get_client_ip();
			$iplocation = new iplocate();
			$address=$iplocation->getaddress($ip);
			foreach ($city_list as $city)
			{
				if(strpos($address['area1'],$city['name']))
				{
					$deal_city = $city;
					break;
				}
			}
		}
		if(!$deal_city)
		$deal_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_default = 1 and is_effect = 1 and is_delete = 0");
	}
	return $deal_city;
}

/**
 * 获取页面的标题，关键词与描述
 */
function get_shop_info()
{
	if($GLOBALS['deal_city'])
	{
		$shop_info['SHOP_TITLE']	=	$GLOBALS['deal_city']['seo_title']==''?app_conf('SHOP_TITLE'):$GLOBALS['deal_city']['seo_title'];
		$shop_info['SHOP_KEYWORD']	=	$GLOBALS['deal_city']['seo_keyword']==''?app_conf('SHOP_KEYWORD'):$GLOBALS['deal_city']['seo_keyword'];
		$shop_info['SHOP_DESCRIPTION']	= $GLOBALS['deal_city']['seo_description']==''?app_conf('SHOP_DESCRIPTION'):$GLOBALS['deal_city']['seo_description'];
	}
	else
	{
		$shop_info['SHOP_TITLE']	=	app_conf('SHOP_TITLE');
		$shop_info['SHOP_KEYWORD']	=	app_conf('SHOP_KEYWORD');
		$shop_info['SHOP_DESCRIPTION']	=	app_conf('SHOP_DESCRIPTION');
	}

	return $shop_info;
}

/**
 * 获取导航菜单
 */
function format_nav_list($nav_list)
{
		foreach($nav_list as $k=>$v)
		{
			if($v['url']!='')
			{
				if(substr($v['url'],0,7)!="http://")
				{		
					//开始分析url
					$nav_list[$k]['url'] = APP_ROOT."/".$v['url'];
				}
			}
			else
			{
				preg_match("/id=(\d+)/i",$v['u_param'],$matches);
				$id = intval($matches[1]);
				if($v['u_module']=='article'&&$id>0)
				{
					$article = get_article($id);
					if($article['type_id']==1)
					{
						$nav_list[$k]['u_module'] = "help";
					}
					elseif($article['type_id']==2)
					{
						$nav_list[$k]['u_module'] = "notice";
					}
					elseif($article['type_id']==3)
					{
						$nav_list[$k]['u_module'] = "sys";
					}
					else 
					{
						$nav_list[$k]['u_module'] = 'article';
					}
				}
			}
		}
		return $nav_list;
}
function get_nav_list()
{
	return load_auto_cache("cache_nav_list_tuan");
}
function get_shop_nav_list()
{
	return load_auto_cache("cache_nav_list_shop");
}
function get_youhui_nav_list()
{
	return load_auto_cache("cache_nav_list_youhui");
}

function init_nav_list($nav_list)
{
	$u_param = "";
	foreach($_GET as $k=>$v)
	{
		if(strtolower($k)!="ctl"&&strtolower($k)!="act"&&strtolower($k)!="city")
		{
			$u_param.=$k."=".$v."&";
		}
	}
	if(substr($u_param,-1,1)=='&')
	$u_param = substr($u_param,0,-1);
	
	foreach($nav_list as $k=>$v)
	{			
		if($v['url']=='')
		{
				$route = $v['u_module'];
				if($v['u_action']!='')
				$route.="#".$v['u_action'];
				
				$app_index = $v['app_index'];
				
				
				if($v['u_module']=='shop'||$v['u_module']=='youhui'||$v['u_module']=='tuan')
				{
					$route="index";
					$v['u_module'] = "index";
				}
				if($v['u_module']=='youhui_index')
				{
					$route="index";
					$v['u_module'] = "index";					
					
				}
				if($v['u_module']=='daijin_index')
				{
					$route="index";
					$v['u_module'] = "index";
					$v['u_action'] = "daijin_index";
					$v['app_index'] = "youhui";
				}
				if($v['u_action']=='')
				$v["u_action"] = "index";
				
				$str = "u:".$app_index."|".$route."|".$v['u_param'];	
				
				$nav_list[$k]['url'] =  parse_url_tag($str);		

				if($v['app_index']=='index')$v['app_index'] = app_conf("MAIN_APP");
				
				if(ACTION_NAME==$v['u_action']&&MODULE_NAME==$v['u_module']&&APP_INDEX==$v['app_index']&&$v['u_param']==$u_param)
				{					
					$nav_list[$k]['current'] = 1;										
				}	
		}
	}
	return $nav_list;
}

function get_help()
{
	return load_auto_cache("get_help_cache");
}



//获取所有子集的类
class ChildIds
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;	
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAll("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".intval($pid));
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}

//显示错误
function showErr($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 0;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['ERROR_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("error.html");
		exit;
	}
}

//显示成功
function showSuccess($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SUCCESS_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("success.html");
		exit;
	}
}



function get_user_name($id,$show_tag=true)
{
	$key = md5("USER_NAME_LINK_".$id);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	else
	{
		$uname = load_dynamic_cache($key);
		if($uname===false)
		{
			$u = $GLOBALS['db']->getRow("select id,user_name,is_merchant,is_daren from ".DB_PREFIX."user where id = ".intval($id));
			$uname = "<a href='".url("shop","space",array("id"=>$id))."'  class='user_name'  onmouseover='userCard.load(this,".$u['id'].");' >".$u['user_name']."</a>";
//			if($show_tag)
//			{
//				$uname = "<a href='".url("shop","space",array("id"=>$id))."'>".msubstr($u['user_name'],0,5)."</a>";
//				if($u['is_merchant'])
//				{
//					$uname = $uname."<font class='is_merchant'></font>";
//				}
//				if($u['is_daren'])
//				{
//					$uname = $uname."<font class='is_daren'></font>";
//				}
//			}
//			else
//			{
//				$uname = "<a href='".url("shop","space",array("id"=>$id))."'>".$u['user_name']."</a>";
//			}
			set_dynamic_cache($key,$uname);
		}
		$GLOBALS[$key] = $uname; 
		return $GLOBALS[$key];
	}
}


function get_message_rel_data($message,$field='name')
{
	return $GLOBALS['db']->getOne("select ".$field." from ".DB_PREFIX.$message['rel_table']." where id = ".intval($message['rel_id']));
}
function get_delivery_sn($id)
{
	$is_delivery = $GLOBALS['db']->getOne("select d.is_delivery from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.id = ".intval($id));
	if($is_delivery==0)
	return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_5'];
	else
	{
		$delivery_notice =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".intval($id)." order by delivery_time desc");
		if($delivery_notice)
		{
			$str = $delivery_notice['notice_sn'];
			if($delivery_notice['express_id']!=0)
			$track_node = "<a href='javascript:void(0);' onclick='track_express(\"".$delivery_notice['notice_sn']."\",\"".$delivery_notice['express_id']."\");' >".$GLOBALS['lang']['TRACK_EXPRESS']."</a>&nbsp;";
			else
			$track_node = "";
			if($delivery_notice['is_arrival']==0)
			{
				$str.="<br />".$track_node."<a href='".url("shop","uc_order#arrival",array("id"=>$delivery_notice['id']))."'>".$GLOBALS['lang']['CONFIRM_ARRIVAL']."</a>";  
			}
			else
			{
				$str.="<br />".$track_node.$GLOBALS['lang']['ARRIVALED'];
			}
			return $str;
		}
		else
		return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_0'];
	}
}

function get_order_item_list($order_id)
{
	$deal_order_item = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	$str = '';
	foreach($deal_order_item as $k=>$v)
	{
		$str .="<br /><span title='".$v['name']."'>".msubstr($v['name'])."</span>[".$v['number']."]";	
	}
	return $str;
}

//用于获取可同步登录的API
function get_api_login()
{
	if(trim($_REQUEST['act'])!='api_login')
	{
		$apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
		$str = "<div class='blank'></div>";
		foreach($apis as $k=>$api)
		{
			$str .= $url."<span id='api_".$api['class_name']."_0'><script type='text/javascript'>load_api_url('".$api['class_name']."',0);</script></span>";
		}
		return $str;
	}
	else
	return '';
}

//获取已过时间
function pass_date($time)
{
		$time_span = get_gmtime() - $time;
		if($time_span>3600*24*365)
		{
			//一年以前
//			$time_span_lang = round($time_span/(3600*24*365)).$GLOBALS['lang']['SUPPLIER_YEAR'];
			//$time_span_lang = to_date($time,"Y".$GLOBALS['lang']['SUPPLIER_YEAR']."m".$GLOBALS['lang']['SUPPLIER_MON']."d".$GLOBALS['lang']['SUPPLIER_DAY']);
			$time_span_lang = to_date($time,"Y-m-d");
		}
		elseif($time_span>3600*24*30)
		{
			//一月
//			$time_span_lang = round($time_span/(3600*24*30)).$GLOBALS['lang']['SUPPLIER_MON'];
			//$time_span_lang = to_date($time,"Y".$GLOBALS['lang']['SUPPLIER_YEAR']."m".$GLOBALS['lang']['SUPPLIER_MON']."d".$GLOBALS['lang']['SUPPLIER_DAY']);
			$time_span_lang = to_date($time,"Y-m-d");
		}
		elseif($time_span>3600*24)
		{
			//一天
			//$time_span_lang = round($time_span/(3600*24)).$GLOBALS['lang']['SUPPLIER_DAY'];
			$time_span_lang = to_date($time,"Y-m-d");
		}
		elseif($time_span>3600)
		{
			//一小时
			$time_span_lang = round($time_span/(3600)).$GLOBALS['lang']['SUPPLIER_HOUR'];
		}
	    elseif($time_span>60)
		{
			//一分
			$time_span_lang = round($time_span/(60)).$GLOBALS['lang']['SUPPLIER_MIN'];
		}
		else
		{
			//一秒
			$time_span_lang = $time_span.$GLOBALS['lang']['SUPPLIER_SEC'];
		}
		return $time_span_lang;
}

//以下关于商家发货的新增函数
function get_region_name($id)
{
	return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$id);
}
function get_user_info($id)
{
	$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	$str = $user_info['user_name'];
	if($user_info['mobile']!='')
	{
		$str .="(".$GLOBALS['lang']['MOBILE'].":".$user_info['mobile'].")";
	}
	return $str;
}
function get_coupon_sn($deal_order_item_id)
{
	$coupon_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where order_deal_id =".$deal_order_item_id." and is_valid = 1");
	$str = "<br />";
	foreach($coupon_list as $row)
	{
		$begin_time = $row['begin_time']==0?$GLOBALS['lang']['NOT_LIMIT_TIME']:to_date($row['begin_time']);
		$end_time = $row['end_time']==0?$GLOBALS['lang']['NOT_LIMIT_TIME']:to_date($row['end_time']);
		$str.=$row['sn']."(".$begin_time."-".$end_time.")";
		if($row['confirm_time']!=0)
		$str.=$GLOBALS['lang']['COUPON_HAS_USED'];
		$str.="<br />";
	}
	return $str;
}

function get_delivery_status($id)
{
	$s_account_info = es_session::get("account_info");
	$account_id = intval($s_account_info['id']);
	$account_data = $GLOBALS['db']->getRow("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
	
	$res = $GLOBALS['db']->getRow("select d.is_delivery,do.id from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".intval($id));
	$is_delivery = intval($res['is_delivery']);
	if($is_delivery==0)
	return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_5'];
	else
	{
		$delivery_notice =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".intval($id)." order by delivery_time desc");
		if($delivery_notice)
		{
			$express_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."express where id = ".$delivery_notice['express_id']);
			$str = $express_name.$delivery_notice['notice_sn'];	
			if($account_data['allow_delivery'])		
			$str = $str."<br /><a href='".url("biz","order#do_delivery",array("id"=>$res['id']))."'>".$GLOBALS['lang']['REDELIVERY']."</a>";
			return $str;
		}
		else
		{
			$str = $GLOBALS['lang']['ORDER_DELIVERY_STATUS_0'];
			if($account_data['allow_delivery'])		
			$str = $str."<br /><a href='".url("biz","order#do_delivery",array("id"=>$res['id']))."'>".$GLOBALS['lang']['DODELIVERY']."</a>";
			return $str;
		}
	}
}


function get_order_item_link($order_item_id)
{
	if($order_item_id==0)
	{
		return $GLOBALS['lang']['NO_DEAL_COUPON'];
	}
	else
	{
		$order = $GLOBALS['db']->getRow("select order_id,name from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
		if($order)
		{
			return "<a href='".url("tuan","coupon#view",array("id"=>$order['order_id']))."'>".$order['name']."</a>";
		}
		else
		{
			return $GLOBALS['lang']['DEAL_DELETE_COUPON'];
		}
	}
}

function get_coupon_confirm_time($time)
{
	if($time==0)
	{
		return $GLOBALS['lang']['NOT_CONFIRM'];
	}
	else
	{
		return to_date($time);
	}
}

// $type = middle,big,small

function show_avatar($u_id,$type="middle")
{
	$key = md5("AVATAR_".$u_id.$type);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	else
	{
		$avatar_key = md5("USER_AVATAR_".$u_id); 
		$avatar_data = $GLOBALS['dynamic_avatar_cache'][$avatar_key];// 当前用户所有头像的动态缓存			
		if(!isset($avatar_data)||!isset($avatar_data[$key]))
		{
			$avatar_file = get_user_avatar($u_id,$type);	
			$avatar_str = "<a href='".url("shop","space",array("id"=>$u_id))."' style='text-align:center; display:inline-block;'  onmouseover='userCard.load(this,".$u_id.");'>".
				   "<img src='".$avatar_file."'  />".
				   "</a>"; 			
			$avatar_data[$key] = $avatar_str;
			if(count($GLOBALS['dynamic_avatar_cache'])<500) //保存500个用户头像缓存
			{
				$GLOBALS['dynamic_avatar_cache'][$avatar_key] = $avatar_data;
			}			
		}
		else
		{
			$avatar_str = $avatar_data[$key];
		}
		$GLOBALS[$key]= $avatar_str;
		return $GLOBALS[$key];
	}
}

function update_avatar($u_id)
{
	$avatar_key = md5("USER_AVATAR_".$u_id); 
	unset($GLOBALS['dynamic_avatar_cache'][$avatar_key]);
	$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
	$GLOBALS['fcache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
}

//获取用户头像的文件名
function get_user_avatar($id,$type)
{
	$uid = sprintf("%09d", $id);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir1.'/'.$dir2.'/'.$dir3;
				
	$id = str_pad($id, 2, "0", STR_PAD_LEFT); 
	$id = substr($id,-2);
	$avatar_file = APP_ROOT."/public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	$avatar_check_file = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	if(file_exists($avatar_check_file))	
	return $avatar_file;
	else
	return APP_ROOT."/public/avatar/noavatar_".$type.".gif";
	//@file_put_contents($avatar_check_file,@file_get_contents(APP_ROOT_PATH."public/avatar/noavatar_".$type.".gif"));
}

//添加一则日志
/**
 * 
 * @param $content  	内容
 * @param $title    	可能存在的标题
 * @param $type			转发的类型标识    见下代码中的范围 
 * @param $group		插件的类型    插件名称
 * @param $relay_id		转发的主题ID
 * @param $fav_id		喜欢主题的ID
 * @param $group_data   插件同步过来的额外数据, 如价格，标题, url等
 * @param $attach_list	主题的附件列表
 * attach_list = array(
 * 	array(
 * 		'id'=>xx, 附件的ID
 * 		'type'	=>	xx, (如image, 可扩展，如vedio,music等)  //image为到topic_image表中查询
 * 	),
 * )
 * @param $url_route	关联数据的url配置
 * $url_route = array(
 * 	'rel_app_index'	=>	'',
 *  'rel_route'	=>	'',
 *  'rel_param'	=>	''
 * )
 * @param $tags	分享的标签集合，一维数组
 * 如
 * array("美食","旅游")
 * @param xpoint与ypoint移动端可能用到的分享产生的地理定位
 */
function insert_topic($content, $title='', $type='', $group='', $relay_id = 0 , $fav_id = 0, $group_data = "", $attach_list = array() , $url_route=array(), $tags=array(), $xpoint="", $ypoint="",$forum_title='',$group_id=0 )
{
	
	//定义类型的范围
	$type_array = array(
		"share", //分享
		"tuancomment", //团购点评
		"shopcomment", //商城购物点评		
		'youhuicomment', //购买优惠券购物点评
		'fyouhuicomment', //免费优惠券点评
		'eventcomment', //活动点评
		'slocationcomment',  //门店点评
		'eventsubmit',  //活动报名	
		'sharetuan',  //分享团购
		'sharegoods', //分享商品
		'sharefyouhui', //分享优惠券	
		'sharebyouhui',	//分享代金券
		'shareevent',	//分享活劝
	);
	
	$group_array = load_auto_cache("group_array_cache");	
	if(!in_array($group,$group_array))
	$group = "share";
	
	if(!in_array($type,$type_array))
	$type = "share";
	
	//转发与喜欢都是转发喜欢原主题
	
	if($relay_id>0)
	{
		$from_data = $GLOBALS['db']->getRow("select origin_id,title,content from ".DB_PREFIX."topic where id = ".$relay_id);
		if($from_data)
		{
			$data['relay_id'] = $relay_id;
			$data['origin_id'] = $from_data['origin_id'];
			//更新计数
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set relay_count = relay_count + 1 where id in ('".$relay_id."','".$from_data['origin_id']."')");
		}	
	}
	if($fav_id>0)
	{
		$from_data = $GLOBALS['db']->getRow("select origin_id,title,content,user_id from ".DB_PREFIX."topic where id = ".$fav_id);
		if($from_data)
		{
			$data['fav_id'] = $fav_id;
			$data['origin_id'] = $from_data['origin_id'];
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set fav_count = fav_count + 1 where id in ('".$fav_id."','".$from_data['origin_id']."')");
			
			//更新会员的喜欢数与被喜欢数
			$GLOBALS['db']->query("update ".DB_PREFIX."user set fav_count = fav_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
			$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count + 1 where id = ".$from_data['user_id']);
			
			if($fav_id!=$from_data['origin_id'])
			{
				//对原贴表示喜欢，并对原贴的作者被喜欢数+1
				$origin_user_id = intval($GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."topic where id = ".$from_data['origin_id']));
				$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count + 1 where id = ".$origin_user_id);
			}
			
		}	
	}	

//	preg_match_all("/@[^\:]+:/i",$content,$matches);
//	$matches[0] = array_unique($matches[0]);
//	$utitle = "";
//	foreach($matches[0] as $k=>$v)
//	{
//		$matches[1][$k] = "";
//		$utitle.=$v;
//	}
//	$content = str_replace($matches[0],$matches[1],$content);
//	$content = $utitle.$content;	

	//开始解析url
	$content = htmlspecialchars_decode($content);
	$url_reg = "/http:\/\/[a-zA-Z0-9%\&_\-\.\/=\?]+/i";
	preg_match_all($url_reg,$content,$url_matches);

	foreach($url_matches[0] as $k=>$url)
	{

		$url_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."urls where url = '".$url."'");
		if(!$url_data)
		{
			$url_data = array();
			$url_data['url'] = $url;
			$GLOBALS['db']->autoExecute(DB_PREFIX."urls",$url_data);
			$url_id = $GLOBALS['db']->insert_id();
		}
		else
		$url_id = $url_data['id'];
		$url_matches[1][$k] = "[url]".$url_id."[/url]";

	}
	$content = str_replace($url_matches[0],$url_matches[1],$content);	
	$content = htmlspecialchars($content);
	
	
	
	//解析标题
	if($title=='')
	{

			if(preg_match("/#([^#]+)#/",$content,$title_matches))
			{
				$title = $title_matches[1];
				$content = str_replace($title_matches[0],"",$content);			
			}
		
	}	
	$data['forum_title'] = $forum_title;
	$data['group_id'] = $group_id;
	$data['title'] = $title;
	$data['content'] = $content;
	$data['create_time'] = get_gmtime();
	$data['user_id'] = intval($GLOBALS['user_info']['id']);
	$data['user_name'] = trim($GLOBALS['user_info']['user_name']);
	$data['is_effect']  = 1;
	$data['is_delete'] = 0;
	$data['type'] = $type;
	$data['message_id'] = $message_id;	
	$data['topic_group'] = $group;
	$data['group_data'] = $group_data;
	$data['tags'] = implode(" ",$tags);
	$data['xpoint'] = $xpoint;
	$data['ypoint'] = $ypoint;
	
	foreach($url_route as $k=>$v)
	{
		$data[$k]=$v;
	}	
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."topic",$data);

	$id = intval($GLOBALS['db']->insert_id());
	if($id>0)
	{		
		//同步添加话题
		if($title!='')
		$topic_title = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_title where name = '".$title."'");
		
		if($topic_title)
		{
			//已有话题，为分享定位分类
			$cate_ids = $GLOBALS['db']->getAll("select cate_id from ".DB_PREFIX."topic_title_cate_link where title_id = ".$topic_title['id']);
					foreach($cate_ids as $row)
					{
						if($row['cate_id']>0)
						{
							$link_data = array();
							$link_data['topic_id'] = $id;
							$link_data['cate_id'] = $row['cate_id'];
							$GLOBALS['db']->autoExecute(DB_PREFIX."topic_cate_link",$link_data,"INSERT","","SILENT");
						}
					}
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = count + 1 where name = '".$title."'");
		}
		else
		{
			//新话题
			if($title!='')
			{
				$topic_title['name'] = $title;
				$topic_title['count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."topic_title",$topic_title,"INSERT","","SILENT");
			}
		}
		
		$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set topic_count = topic_count + 1 where id = ".$group_id);
		//发贴量加1
		$GLOBALS['db']->query("update ".DB_PREFIX."user set topic_count = topic_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
		if($group=='Fanwe')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set insite_count = insite_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
		}
		//处理标签自动分类
		if(count($tags)>0)
		{
			
			foreach($tags as $tag)
			{
				$tag_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."topic_tag where name = '".$tag."'");
				
				if($tag_id>0)
				{
					$cate_ids = $GLOBALS['db']->getAll("select cate_id from ".DB_PREFIX."topic_tag_cate_link where tag_id = ".$tag_id);
					foreach($cate_ids as $row)
					{
						if($row['cate_id']>0)
						{
							$link_data = array();
							$link_data['topic_id'] = $id;
							$link_data['cate_id'] = $row['cate_id'];
							$GLOBALS['db']->autoExecute(DB_PREFIX."topic_cate_link",$link_data,"INSERT","","SILENT");
						}
					}
				}
			}
		}

		foreach($attach_list as $attach)
		{
			if($attach['type']=='image')
			{
				//插入图片				
				$GLOBALS['db']->query("update ".DB_PREFIX."topic_image set topic_id = ".$id.",topic_table='topic' where id = ".$attach['id']);			
			}
		}
		
		//删除所有创建超过一小时，且未被使用过的图片
		$del_list = $GLOBALS['db']->getAll("select id,path from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		foreach($del_list as $k=>$v)
		{
			@unlink(APP_ROOT_PATH.$v['path']);
			@unlink(APP_ROOT_PATH.$v['o_path']);
		}
		if($relay_id==0&&$fav_id==0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set origin_id = ".$id." where id = ".$id);
		}
		syn_topic_match($id);
		return $id;	
	}
	else
		return false;
}

/**
 * 
 * @param $dp_title  点评的标题
 * @param $dp_content  内容
 * @param $location_id  点评的门店
 * @param $point   评分 1-5
 * @param $is_buy  是否购买点评
 * @param $from    来源 (event/tuan/youhui/daijin)
 * @param $url_route  网址参数
 * @param $message_id  其他部份留言的ID，用于同步
 */
function insert_dp($dp_title,$dp_content,$location_id,$point=0,$is_buy=0,$from="",$url_route=array(),$message_id=0)
{	
	
	$dp_data = array();
	$dp_data['title'] = valid_str($dp_title);
	$dp_data['content'] = valid_str($dp_content);
	$dp_data['create_time'] = get_gmtime();
	$dp_data['point'] = $point;
	$dp_data['user_id'] = intval($GLOBALS['user_info']['id']);
	$dp_data['supplier_location_id'] = $location_id;
	$dp_data['status'] = 1;
	$dp_data['from_data'] = $from;
	$dp_data['is_buy'] = $is_buy;
	$dp_data['message_id'] = $message_id;
	foreach($url_route as $k=>$v)
	{
		$dp_data[$k]=$v;
	}		
	$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp", $dp_data ,"INSERT");
	
	
	$dp_id = $GLOBALS['db']->insert_id();
	if($dp_id>0)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$dp_data['supplier_location_id']);
		//更新统计
		syn_supplier_locationcount($supplier_info);
				
		$cache_id  = md5("store"."view".$supplier_info['id']);		
		$GLOBALS['tmpl']->clear_cache('store_view.html', $cache_id);
	}
	return $dp_id;
			
}

function get_topic_list($limit,$condition='',$orderby='create_time desc',$keywords_array=array())
{
	if($orderby=='')$orderby='create_time desc';
	if($condition!='')
	$condition = " and ".$condition;
	$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0 ".$condition." order by ".$orderby." limit ".$limit);
	$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where is_effect = 1 and is_delete = 0  ".$condition);

	foreach($list as $k=>$v)
	{
		$list[$k] =	get_topic_item($v,$keywords_array);
		if(msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['content']),0,50)!=preg_replace("/<[^>]+>/i","",$list[$k]['content']))
		$list[$k]['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['content']),0,50);
		else
		$list[$k]['short_content'] = preg_replace("/<br[^>]+>/i","",$list[$k]['content']);
		
		if($list[$k]['origin'])
		{
			if(msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']),0,50)!=preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']))
			$list[$k]['origin']['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$list[$k]['origin']['content']),0,50);
			else
			$list[$k]['origin']['short_content'] = preg_replace("/<br[^>]+>/i","",$list[$k]['origin']['content']);
		}
	}
	
	return array('list'=>$list,'total'=>$total);
}

//用于div_to_col计算每个主题的高度
function count_topic_height($topic)
{
	$height = 0;
	if($topic['origin'])
	{
		if(count($topic['origin']['images'])>=3)
		{
			$image_height = intval($topic['origin']['images'][0]['height']*(204/$topic['origin']['images'][0]['width'])) + 100;
			$height+=$image_height;
		}
		if(count($topic['origin']['images'])==2)
		{
			$image_height = intval($topic['origin']['images'][0]['height']*(204/$topic['origin']['images'][0]['width'])) + intval($topic['origin']['images'][1]['height']*(204/$topic['origin']['images'][1]['width']));
			$height+=$image_height;
		}
		if(count($topic['origin']['images'])==1)
		{
			$image_height = intval($topic['origin']['images'][0]['height']*(204/$topic['origin']['images'][0]['width']));
			$height+=$image_height;
		}
		if(count($topic['origin']['images'])==0)
		$height+=150;
	
	}
	
	if(count($topic['images'])>=3)
	{
			$image_height = intval($topic['images'][0]['height']*(204/$topic['images'][0]['width'])) + 100;
			$height+=$image_height;
	}
	if(count($topic['images'])==2)
	{
			$image_height = intval($topic['images'][0]['height']*(204/$topic['images'][0]['width'])) + intval($topic['images'][1]['height']*(204/$topic['images'][1]['width'])) ;
			$height+=$image_height;
	}
	if(count($topic['images'])==1)
	{
			$image_height = intval($topic['images'][0]['height']*(204/$topic['images'][0]['width'])) ;
			$height+=$image_height;
	}
	if(count($topic['images'])==0)
	$height+=150;

	return $height;
}
function div_to_col($list)
{

	$col_size = ceil(app_conf("PAGE_SIZE")/3);
	$new_list = array();
	//初始化三列高度
	$col[1] = 0;
	$col[2] = 0;
	$col[3] = 0;
	foreach($list as $k=>$v)
	{
		$min_height = min($col[1],$col[2],$col[3]);
		foreach($col as $kk=>$vv)
		{
			if($min_height==$vv)
			{
				$new_list[$kk][] = $v;
				$col[$kk] += intval(count_topic_height($v));
				break;
			}
		}

	}
	return $new_list;
}

function cache_topic($topic)
{
	if($topic['is_cached']==0)
	{
		if($topic['id']!=$topic['origin_id'])
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			if($origin_topic)
			{		
				$origin_topic['images'] = $GLOBALS['db']->getAll("select path,o_path,width,height,id from ".DB_PREFIX."topic_image where topic_id = ".$origin_topic['id']);
				$origin_topic['images_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$origin_topic['id']);
			}
			$topic['origin'] = $origin_topic;
			$topic['origin_topic_data'] = serialize($origin_topic);
		}
		
		$topic['images'] = $GLOBALS['db']->getAll("select path,o_path,width,height,id from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
		$topic['image_list'] = serialize($topic['images']);
		$topic['images_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
		
		$group_id = intval($topic['group_id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		$topic['topic_group_data'] = serialize($group_item);
		$topic['topic_group'] = $group_item;
		
		$topic['is_cached'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic",$topic,"UPDATE","id=".$topic['id'],"SILENT");
	}
	else
	{
		$topic['images'] = unserialize($topic['image_list']);
		if($topic['id']!=$topic['origin_id'])
		$topic['origin'] = unserialize($topic['origin_topic_data']);
		$topic['topic_group'] = unserialize($topic['topic_group_data']);
	}
	return $topic;
	
}
function get_topic_item($topic,$keywords_array=array())
{	
	//开始解析同步的数据
	$group = $topic['topic_group'];
	if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
	{
		require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
		$class_name = $group."_fetch_topic";
		if(class_exists($class_name))
		{
			$fetch_obj = new $class_name;
			$topic = $fetch_obj->decode($topic);
		}
	}	
	
	if($topic['rel_app_index']!=''&&$topic['rel_route']!='')
	{
		$topic['rel_url'] = parse_url_tag("u:".$topic['rel_app_index']."|".$topic['rel_route']."|".$topic['rel_param']);		
	}
	
	$topic = cache_topic($topic);
	
	
	$topic['content'] = nl2br(trim($topic['content']));
	$topic['tags_array'] = explode(" ",$topic['tags']);
	
	
	$matches = array();
	foreach($keywords_array as $k=>$item)
	{
		$matches[0][] = $item;
		$matches[1][] = "<span class='result_match'>".$item."</span>";
	}
	$topic['title'] = str_replace($matches[0],$matches[1],$topic['title']);
	$topic['content'] = str_replace($matches[0],$matches[1],$topic['content']);
	return $topic;  //格式化每条的主题
}

//获取相应规格的图片地址
//gen=0:保持比例缩放，不剪裁,如高为0，则保证宽度按比例缩放  gen=1：保证长宽，剪裁
function get_spec_image($img_path,$width=0,$height=0,$gen=0,$is_preview=true)
{
	if($width==0)
		$new_path = $img_path;
	else
	{
		$img_name = substr($img_path,0,-4);
		$img_ext = substr($img_path,-3);	
		if($is_preview)
		$new_path = $img_name."_".$width."x".$height.".jpg";	
		else
		$new_path = $img_name."o_".$width."x".$height.".jpg";	
		if(!file_exists(APP_ROOT_PATH.$new_path))
		{
			require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$imagec = new es_imagecls();
			$thumb = $imagec->thumb(APP_ROOT_PATH.$img_path,$width,$height,$gen,true,"",$is_preview);
			
			if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
        	{
        		$paths = pathinfo($new_path);
        		$path = str_replace("./","",$paths['dirname']);
        		$filename = $paths['basename'];
        		$pathwithoupublic = str_replace("public/","",$path);
	        	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
	        	@file_get_contents($syn_url);
        	}
			
		}
	}
	return $new_path;
}

function get_spec_gif_anmation($url,$width,$height)
{
	require_once APP_ROOT_PATH."system/utils/gif_encoder.php";
	require_once APP_ROOT_PATH."system/utils/gif_reader.php";
	require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
	$gif = new GIFReader();
	$gif->load($url);
	$imagec = new es_imagecls();
	foreach($gif->IMGS['frames'] as $k=>$img)
	{
		$im = imagecreatefromstring($gif->getgif($k));		
		$im = $imagec->make_thumb($im,$img['FrameWidth'],$img['FrameHeight'],"gif",$width,$height,$gen=1);
		ob_start();
		imagegif($im);
		$content = ob_get_contents();
        ob_end_clean();
		$frames [ ] = $content;
   		$framed [ ] = $img['frameDelay'];
	}
		
	$gif_maker = new GIFEncoder (
	       $frames,
	       $framed,
	       0,
	       2,
	       0, 0, 0,
	       "bin"   //bin为二进制   url为地址
	  );
	return $gif_maker->GetAnimation ( );
}

//获取上传的主题附件数据
/* attach_list = array(
 * 	array(
 * 		'id'=>xx,
 * 		'type'	=>	xx, (如image, 可扩展，如vedio,music等)  //image从 topic_image表中取数据
 * 	),
 */
function get_topic_attach_list()
{
	$result = array();
	foreach($_REQUEST['topic_image_idx'] as $idx)
	{
		$topic_image =array();
		$topic_image['type'] = "image";
		$topic_image['id'] =  intval($_REQUEST['topic_image_id'][$idx]);
		$result[] = $topic_image;
	}
	return $result;
}

function show_topic_form($text_name,$width="300px",$height="80px",$is_img = false,$is_topic = false,$is_event = false,$id="topic_form_textarea",$show_btn=false,$show_tag=false)
{
	
	$GLOBALS['tmpl']->caching = true;
	$cache_id  = md5("show_topic_form".$text_name.$width.$height.$is_img.$is_topic.$is_event.$id.$show_btn);		
	if (!$GLOBALS['tmpl']->is_cached('inc/topic_form.html', $cache_id))
	{
		$GLOBALS['tmpl']->assign("text_name",$text_name);
		//输出表情数据html
		$result = $GLOBALS['db']->getAll("select `type`,`title`,`emotion`,`filename` from ".DB_PREFIX."expression order by type");
		$expression = array();
		foreach($result as $k=>$v)
		{
			$v['filename'] = "./public/expression/".$v['type']."/".$v['filename'];
			$v['emotion'] = str_replace(array('[',']'),array('',''),$v['emotion']);
			$expression[$v['type']][] = $v;
		}
		
		$tag_list =$GLOBALS['db']->getAll("select name from ".DB_PREFIX."topic_tag where is_preset = 1 order by count desc limit 5");
		
		$GLOBALS['tmpl']->assign("tag_list",$tag_list);
		$GLOBALS['tmpl']->assign("expression",$expression);
		$GLOBALS['tmpl']->assign("is_img",$is_img);
		$GLOBALS['tmpl']->assign("width",$width);
		$GLOBALS['tmpl']->assign("height",$height);
		$GLOBALS['tmpl']->assign("is_event",$is_event);
		if($is_event)
		{
			$fetch_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."fetch_topic where is_effect = 1 order by sort desc");
			$GLOBALS['tmpl']->assign("fetch_list",$fetch_list);
		}		
		$GLOBALS['tmpl']->assign("is_topic",$is_topic);
		$GLOBALS['tmpl']->assign("box_id",$id);
		$GLOBALS['tmpl']->assign("show_btn",$show_btn);
		$GLOBALS['tmpl']->assign("show_tag",$show_tag);
	}	
	return $GLOBALS['tmpl']->fetch("inc/topic_form.html",$cache_id);
}


function show_reply_form($text_name,$width="300px",$height="80px",$id="topic_form_textarea",$show_btn=false,$js_func="ajax_submit_reply_form")
{
	
	$GLOBALS['tmpl']->caching = true;
	$cache_id  = md5("show_reply_form".$text_name.$width.$height.$id.$show_btn.$js_func);		
	if (!$GLOBALS['tmpl']->is_cached('inc/reply_form.html', $cache_id))
	{
		$GLOBALS['tmpl']->assign("text_name",$text_name);
		//输出表情数据html
		$result = $GLOBALS['db']->getAll("select `type`,`title`,`emotion`,`filename` from ".DB_PREFIX."expression order by type");
		$expression = array();
		foreach($result as $k=>$v)
		{
			$v['filename'] = "./public/expression/".$v['type']."/".$v['filename'];
			$v['emotion'] = str_replace(array('[',']'),array('',''),$v['emotion']);
			$expression[$v['type']][] = $v;
		}
		$GLOBALS['tmpl']->assign("expression",$expression);
		$GLOBALS['tmpl']->assign("width",$width);
		$GLOBALS['tmpl']->assign("height",$height);
		$GLOBALS['tmpl']->assign("box_id",$id);
		$GLOBALS['tmpl']->assign("show_btn",$show_btn);
		$GLOBALS['tmpl']->assign("js_func",$js_func);
	}	
	return $GLOBALS['tmpl']->fetch("inc/reply_form.html",$cache_id);
}



function load_topic_list()
{
	return decode_topic($GLOBALS['tmpl']->fetch("inc/topic_list.html"));
}

function load_topic_col_list()
{
	return decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/topic_col_list.html"));
}

function load_comment_list()
{
	return decode_topic($GLOBALS['tmpl']->fetch("inc/comment_list.html"));
}
function load_message_list()
{
	return decode_topic($GLOBALS['tmpl']->fetch("inc/message_list.html"));
}
function load_reply_list()
{
	return decode_topic($GLOBALS['tmpl']->fetch("inc/topic_page_reply_list.html"));
}

//解析URL标签
// $str = u:shop|acate#index|id=10&name=abc
function parse_url_tag($str)
{
	$key = md5("URL_TAG_".$str);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	
	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	$str = substr($str,2);
	$str_array = explode("|",$str);
	$app_index = $str_array[0];
	$route = $str_array[1];
	$param_tmp = explode("&",$str_array[2]);
	$param = array();
	foreach($param_tmp as $item)
	{
		if($item!='')
		$item_arr = explode("=",$item);
		if($item_arr[0]&&$item_arr[1])
		$param[$item_arr[0]] = $item_arr[1];
	}
	$GLOBALS[$key]= url($app_index,$route,$param);
	set_dynamic_cache($key,$GLOBALS[$key]);
	return $GLOBALS[$key];
}

//编译生成css文件
function parse_css($urls)
{
	
	$url = md5(implode(',',$urls));
	$css_url = 'public/runtime/statics/'.$url.'.css';
	$url_path = APP_ROOT_PATH.$css_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
		mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);
		$tmpl_path = $GLOBALS['tmpl']->_var['TMPL'];	
	
		$css_content = '';
		foreach($urls as $url)
		{
			$css_content .= @file_get_contents($url);
		}
		$css_content = preg_replace("/[\r\n]/",'',$css_content);
		$css_content = str_replace("../images/",$tmpl_path."/images/",$css_content);
//		@file_put_contents($url_path, unicode_encode($css_content));
		@file_put_contents($url_path, $css_content);
	}
	return get_domain().APP_ROOT."/".$css_url;
}

/**
 * 
 * @param $urls 载入的脚本
 * @param $encode_url 需加密的脚本
 */
function parse_script($urls,$encode_url=array())
{	
	$url = md5(implode(',',$urls));
	$js_url = 'public/runtime/statics/'.$url.'.js';
	$url_path = APP_ROOT_PATH.$js_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
		mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);
	
		if(count($encode_url)>0)
		{
			require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		}
		
		$js_content = '';
		foreach($urls as $url)
		{
			$append_content = @file_get_contents($url)."\r\n";
			if(in_array($url,$encode_url))
			{
				$packer = new JavaScriptPacker($append_content);
				$append_content = $packer->pack();
			}			
			$js_content .= $append_content;
		}		
//		require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
//	    $packer = new JavaScriptPacker($js_content);
//		$js_content = $packer->pack();
		@file_put_contents($url_path,$js_content);
	}
	return get_domain().APP_ROOT."/".$js_url;
}

//获取商城公告
//$notice_page 公告显示位置 0:全部 1:首页 2:商城 3:推荐 
function get_notice($limit=0,$notice_page=array(0))
{
	if($limit == 0)
	$limit = app_conf("INDEX_NOTICE_COUNT");
	if($limit>0)
	{
		$limit_str = "limit ".$limit;
	}
	else
	{
		$limit_str = "";
	}
	$list = $GLOBALS['db']->getAll("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.notice_page in (".implode(",",$notice_page).") and ac.type_id = 2 and ac.is_effect = 1 and ac.is_delete = 0 and a.is_effect = 1 and a.is_delete = 0 order by a.sort desc ".$limit_str);
	
	foreach($list as $k=>$v)
	{
			if($v['type_id']==1)
			{
				$module = "help";
			}
			elseif($v['type_id']==2)
			{
				$module = "notice";
			}
			elseif($v['type_id']==3)
			{
				$module = "sys";
			}
			else 
			{
				$module = 'article';
			}
		
			if($v['uname']!='')
			$aurl = url("shop",$module,array("id"=>$v['uname']));
			else
			$aurl = url("shop",$module,array("id"=>$v['id']));
			$list[$k]['url'] = $aurl;
	}
	return $list;
}

function jump_deal($goods,$module)
{
	
	if($goods['buy_type']==1)
	{
				if($goods['uname']!='')
				$url = url("shop","exchange#index",array("id"=>$goods['uname']));
				else
				$url = url("shop","exchange#index",array("id"=>$goods['id']));		
				if($module!="exchange")		
				app_redirect($url);
	}
	else 
	{
		if($goods['is_shop']==0)
		{
					if($goods['uname']!='')
					$url = url("tuan","deal#index",array("id"=>$goods['uname']));
					else
					$url = url("tuan","deal#index",array("id"=>$goods['id']));		
					if($module!="deal")		
					app_redirect($url);
		}
		if($goods['is_shop']==1)
		{
					if($goods['uname']!='')
					$url = url("shop","goods",array("id"=>$goods['uname']));
					else
					$url = url("shop","goods",array("id"=>$goods['id']));	
					if($module!="goods")				
					app_redirect($url);
		}
		if($goods['is_shop']==2)
		{
					if($goods['uname']!='')
					$url = url("youhui","ydetail",array("id"=>$goods['uname']));
					else
					$url = url("youhui","ydetail",array("id"=>$goods['id']));
					if($module!="ydetail")					
					app_redirect($url);
		}
	}
}
/**
 * 获取文章列表
 */
function get_article_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true)
{		
		$key = md5("ARTICLE".$limit.$cate_id.$where.$orderby);	
		if($cached)
		{				
			$res = $GLOBALS['cache']->get($key);
		}
		else
		{
			$res = false;
		}
		if($res===false)
		{
				
			$count_sql = "select count(*) from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
			$sql = "select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
			
			if($cate_id>0)
			{

				$ids = load_auto_cache("deal_shop_acate_belone_ids",array("cate_id"=>$cate_id));
				$sql .= " and a.cate_id in (".implode(",",$ids).")";
				$count_sql .= " and a.cate_id in (".implode(",",$ids).")";
			}
				
			
			if($where != '')
			{
				$sql.=" and ".$where;
				$count_sql.=" and ".$where;
			}
			
			if($orderby=='')
			$sql.=" order by a.sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;

			$articles = $GLOBALS['db']->getAll($sql);	
			foreach($articles as $k=>$v)
			{
				if($v['type_id']==1)
				{
					$module = "help";
				}
				elseif($v['type_id']==2)
				{
					$module = "notice";
				}
				elseif($v['type_id']==3)
				{
					$module = "sys";
				}
				else 
				{
					$module = 'article';
				}
				
				if($v['uname']!='')
				$aurl = url("shop",$module,array("id"=>$v['uname']));
				else
				$aurl = url("shop",$module,array("id"=>$v['id']));
					
				$articles[$k]['url'] = $aurl;
			}	
			$articles_count = $GLOBALS['db']->getOne($count_sql);
			
	 		
			$res = array('list'=>$articles,'count'=>$articles_count);	
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}

function load_page_png($img)
{
	return load_auto_cache("page_image",array("img"=>$img));
}

function get_article($id)
{
	return $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.id = ".intval($id)." and a.is_effect = 1 and a.is_delete = 0");
}

//会员信息发送
/**
 * 
 * @param $title 标题
 * @param $content 内容
 * @param $from_user_id 发件人
 * @param $to_user_id 收件人
 * @param $create_time 时间
 * @param $sys_msg_id 系统消息ID
 * @param $only_send true为只发送，生成发件数据，不生成收件数据
 */
function send_user_msg($title,$content,$from_user_id,$to_user_id,$create_time,$sys_msg_id=0,$only_send=false,$is_notice = false)
{
	$group_arr = array($from_user_id,$to_user_id);
	sort($group_arr);
	if($sys_msg_id>0||$is_notice)
	$group_arr[] = $sys_msg_id;	
	$msg = array();
	$msg['title'] = $title;
	$msg['content'] = addslashes($content);
	$msg['from_user_id'] = $from_user_id;
	$msg['to_user_id'] = $to_user_id;
	$msg['create_time'] = $create_time;
	$msg['system_msg_id'] = $sys_msg_id;
	$msg['type'] = 0;
	$msg['group_key'] = implode("_",$group_arr);
	$msg['is_notice'] = intval($is_notice);
	$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg);
	$id = $GLOBALS['db']->insert_id();
	if($is_notice)
	$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg['group_key']."_".$id."' where id = ".$id);
	if(!$only_send)
	{
		$msg['type'] = 1; //记录发件
		$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg);
	}
}


function show_ke_image($id,$cnt="")
{
	if($cnt)
	{
		$image_path = $cnt;
		$is_show="display:inline-block;";
		$script = "onclick='window.open(this.src);'";
	}
	else{
		$image_path =APP_ROOT."/admin/Tpl/default/Common/images/no_pic.gif";
		$is_show="display:none;";
	}
	return	"<div style='width:120px; height:40px; margin-left:10px; display:inline-block;  float:left;' class='none_border'>
							<script type='text/javascript'>var eid = '".$id."';KE.show({urlType:'domain', id:eid, items : ['upload_image'],skinType: 'tinymce',allowFileManager : false,resizeMode : 0});</script>
							<div style='font-size:0px;'>
							<textarea id='".$id."' name='".$id."' style='width:125px; height:25px;' >".$cnt."</textarea> 
							<input type='text' id='focus_".$id."' style='font-size:0px; border:0px; padding:0px; margin:0px; line-height:0px; width:0px; height:0px;' />
							</div>
						</div>
						<img src='".$image_path."' $script  style='display:inline-block; float:left; cursor:pointer; margin-left:10px; border:#ccc solid 1px; width:35px; height:35px;' id='img_".$id."' />
						<img src='".APP_ROOT."/admin/Tpl/default/Common/images/del.gif' style='".$is_show." margin-left:10px; float:left; border:#ccc solid 1px; width:35px; height:35px; cursor:pointer;' id='img_del_".$id."' onclick='delimg(\"".$id."\")' title='删除' />";
						
}

function show_ke_textarea($id,$width=630,$height=350,$cnt="")
{	
	return "<script type='text/javascript'> var eid = '".$id."';KE.show({urlType:'domain', id:eid, items : ['fsource', 'image', 'justifyleft', 'justifycenter', 'justifyright','justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript','superscript', 'selectall', 'textcolor', 'bold','italic', 'underline', 'strikethrough', 'fullscreen','-','title', 'fontname', 'fontsize'], skinType: 'tinymce',allowFileManager : false,resizeMode : 0, newlineTag:'nl'});</script><div  style='margin-bottom:5px; '><textarea id='".$id."' name='".$id."' style='width:".$width."px; height:".$height."px;' >".$cnt."</textarea> </div>";
}

function replace_public($content)
{
	 $domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	 $domain_origin = get_domain().APP_ROOT;
	 $content = str_replace($domain."/public/","./public/",$content);	
	 $content = str_replace($domain_origin."/public/","./public/",$content);		 
	 return $content;
}

function check_user_auth($m_name,$a_name,$rel_id)
{
	$rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_auth where m_name = '".$m_name."' and a_name = '".$a_name."' and user_id = ".intval($GLOBALS['user_info']['id']));
	foreach($rs as $row)
	{
		if($row['rel_id']==0||$row['rel_id']==$rel_id)
		{
			return true;
		}
	}
	return false;
}

function get_user_auth()
{
	$user_auth = array();
	//定义用户权限
	$user_auth_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_auth where user_id = ".intval($GLOBALS['user_info']['id']));
	foreach($user_auth_rs as $k=>$row)
	{
		$user_auth[$row['m_name']][$row['a_name']][$row['rel_id']] = true;
	}
	return $user_auth;
}


function get_op_change_show($m_name,$a_name)
{
	if($a_name=="replydel"||$a_name=='del')
	{
		//删除
		$money = doubleval(app_conf("USER_DELETE_MONEY"));
		$money_f = "-".format_price(0-$money);
		$score = intval(app_conf("USER_DELETE_SCORE"));
		$score_f = "-".format_score(0-$score);
		$point = intval(app_conf("USER_DELETE_POINT"));
		$point_f = "-".(0-$point)."经验";
	}
	else
	{
		//增加
		$money = doubleval(app_conf("USER_ADD_MONEY"));
		$money_f = "+".format_price($money);
		$score = intval(app_conf("USER_ADD_SCORE"));
		$score_f = "+".format_score($score);
		$point = intval(app_conf("USER_ADD_POINT"));
		$point_f = "+".$point."经验";
	}
	$str = "";
	if($money!=0)$str .= $money_f;
	if($score!=0)$str .= $score_f;
	if($point!=0)$str .= $point_f;
	return $str;
	
}

function get_op_change($m_name,$a_name)
{
	if($a_name=="replydel"||$a_name=='del')
	{
		//删除
		$money = doubleval(app_conf("USER_DELETE_MONEY"));
		
		$score = intval(app_conf("USER_DELETE_SCORE"));
		
		$point = intval(app_conf("USER_DELETE_POINT"));
		
	}
	else
	{
		//增加
		$money = doubleval(app_conf("USER_ADD_MONEY"));
		
		$score = intval(app_conf("USER_ADD_SCORE"));
		
		$point = intval(app_conf("USER_ADD_POINT"));
		
	}
	return array("money"=>$money,"score"=>$score,"point"=>$point);
	
}

function get_gopreview()
{
		$gopreview = es_session::get("gopreview");
		if(!isset($gopreview)||$gopreview=="")
		{
			$has_cart = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cart where session_id = '".es_session::id()."'"));
			if($has_cart>0)
			$gopreview = url("index","cart");
			else
			{
				$gopreview = es_session::get('before_login')?es_session::get('before_login'):url("index");				
			}
		}	
		es_session::delete("before_login");	
		es_session::delete("gopreview");	
		return $gopreview;
}

function set_gopreview()
{
	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");   
    $parse = parse_url($url);
    if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            $url   =  $parse['path'].'?'.http_build_query($params);
    }
    if(app_conf("URL_MODEL")==1)$url = $GLOBALS['current_url'];
	es_session::set("gopreview",$url); 
}	
function app_recirect_preview()
{
	app_redirect(get_gopreview());
}	


/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function run_info()
{

	if(!SHOW_DEBUG)return "";

	$query_time = number_format($GLOBALS['db']->queryTime,6);

	if($GLOBALS['begin_run_time']==''||$GLOBALS['begin_run_time']==0)
	{
		$run_time = 0;
	}
	else
	{
		if (PHP_VERSION >= '5.0.0')
		{
			$run_time = number_format(microtime(true) - $GLOBALS['begin_run_time'], 6);
		}
		else
		{
			list($now_usec, $now_sec)     = explode(' ', microtime());
			list($start_usec, $start_sec) = explode(' ', $GLOBALS['begin_run_time']);
			$run_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
		}
	}

	/* 内存占用情况 */
	if (function_exists('memory_get_usage'))
	{
		$unit=array('B','KB','MB','GB');
		$size = memory_get_usage();
		$used = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		$memory_usage = lang("MEMORY_USED",$used);
	}
	else
	{
		$memory_usage = '';
	}

	/* 是否启用了 gzip */
	$enabled_gzip = (app_conf("GZIP_ON") && function_exists('ob_gzhandler'));
	$gzip_enabled = $enabled_gzip ? lang("GZIP_ON") : lang("GZIP_OFF");

	$str = lang("QUERY_INFO_STR",$GLOBALS['db']->queryCount, $query_time,$gzip_enabled,$memory_usage,$run_time);

	foreach($GLOBALS['db']->queryLog as $K=>$sql)
	{
		if($K==0)$str.="<br />SQL语句列表：";
		$str.="<br />行".($K+1).":".$sql;
	}

	return "<div style='width:940px; padding:10px; line-height:22px; border:1px solid #ccc; text-align:left; margin:30px auto; font-size:14px; color:#999; height:150px; overflow-y:auto;'>".$str."</div>";
}
?>