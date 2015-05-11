<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

//前后台加载的函数库
require_once 'system_init.php';

//获取真实路径
function get_real_path()
{
	return APP_ROOT_PATH;
}

//获取GMTime
function get_gmtime()
{
	static $now;
	if($now)return $now;
	$now = (time() - date('Z'));
	return $now;
}

function to_date($utc_time, $format = 'Y-m-d H:i:s') {
	if (empty ( $utc_time )) {
		return '';
	}
	$timezone = intval(app_conf('TIME_ZONE'));
	$time = $utc_time + $timezone * 3600; 
	return date ($format, $time );
}

function to_timespan($str, $format = 'Y-m-d H:i:s')
{
	$timezone = intval(app_conf('TIME_ZONE'));
	//$timezone = 8; 
	$time = intval(strtotime($str));
	if($time!=0)
	$time = $time - $timezone * 3600;
    return $time;
}

/**
 *
 * 获取商品是否为当天上线商品
 */
function get_is_today($deal)
{
	if($deal['begin_time']==0) return 0;
	$day_begin =  to_timespan(to_date(get_gmtime(),"Y-m-d"),"Y-m-d");
	$day_end  = $day_begin+3600*24-1;
	if($deal['begin_time']>=$day_begin&&$deal['begin_time']<$day_end)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

//获取客户端IP
function get_client_ip() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "0.0.0.0";
	if(!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)/", $ip))
		$ip = "0.0.0.0";
	return ($ip);
}

//过滤注入
function filter_injection(&$request)
{
	$pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])/i";
	foreach($request as $k=>$v)
	{
				if(preg_match($pattern,$k,$match))
				{
						die("SQL Injection denied!");
				}
		
				if(is_array($v))
				{					
					filter_injection($v);
				}
				else
				{					
					
					if(preg_match($pattern,$v,$match))
					{
						die("SQL Injection denied!");
					}					
				}
	}
	
}

//过滤请求
function filter_request(&$request)
{
		if(MAGIC_QUOTES_GPC)
		{
			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					filter_request($v);
				}
				else
				{
					$request[$k] = stripslashes(trim($v));
				}
			}
		}
		
}

function adddeepslashes(&$request)
{

			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					adddeepslashes($v);
				}
				else
				{
					$request[$k] = addslashes(trim($v));
				}
			}		
}

//request转码
function convert_req(&$req)
{
	foreach($req as $k=>$v)
	{
		if(is_array($v))
		{
			convert_req($req[$k]);
		}
		else
		{
			if(!is_u8($v))
			{
				$req[$k] = iconv("gbk","utf-8",$v);
			}
		}
	}
}

function is_u8($string)
{
	return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}

//清除缓存
function clear_cache()
{
		//系统后台缓存
		syn_dealing();
		clear_dir_file(get_real_path()."public/runtime/admin/Cache/");	
		clear_dir_file(get_real_path()."public/runtime/admin/Data/_fields/");		
		clear_dir_file(get_real_path()."public/runtime/admin/Temp/");	
		clear_dir_file(get_real_path()."public/runtime/admin/Logs/");	
		@unlink(get_real_path()."public/runtime/admin/~app.php");
		@unlink(get_real_path()."public/runtime/admin/~runtime.php");
		@unlink(get_real_path()."public/runtime/admin/lang.js");
		@unlink(get_real_path()."public/runtime/app/config_cache.php");	
		
		
		//数据缓存
		clear_dir_file(get_real_path()."public/runtime/app/data_caches/");				
		clear_dir_file(get_real_path()."public/runtime/app/db_caches/");
		$GLOBALS['cache']->clear();
		clear_dir_file(get_real_path()."public/runtime/data/");

		//模板页面缓存
		clear_dir_file(get_real_path()."public/runtime/app/tpl_caches/");		
		clear_dir_file(get_real_path()."public/runtime/app/tpl_compiled/");
		@unlink(get_real_path()."public/runtime/app/lang.js");	
		
		//脚本缓存
		clear_dir_file(get_real_path()."public/runtime/statics/");		
			
				
		
}
function clear_dir_file($path)
{
   if ( $dir = opendir( $path ) )
   {
            while ( $file = readdir( $dir ) )
            {
                $check = is_dir( $path. $file );
                if ( !$check )
                {
                    @unlink( $path . $file );                       
                }
                else 
                {
                 	if($file!='.'&&$file!='..')
                 	{
                 		clear_dir_file($path.$file."/");              			       		
                 	} 
                 }           
            }
            closedir( $dir );
            rmdir($path);
            return true;
   }
}

//同步未过期团购的状态
function syn_dealing()
{
	$deals = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where time_status <> 2");
	foreach($deals as $v)
	{
		syn_deal_status($v['id']);
	}
}

function check_install()
{
	if(!file_exists(get_real_path()."public/install.lock"))
	{
	    clear_cache();
		header('Location:'.APP_ROOT.'/install');
		exit;
	}
}

function syn_brand_status($id)
{
	//同步品牌状态
	$brand_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".$id);
	//1 无开始与结束时间
	if($brand_info['begin_time']==0&&$brand_info['end_time']==0)
	{
		if($deal_info['time_status']!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
		}
		return 0;
	}
	
	//2 无开始时间，有结束时间
	if($brand_info['begin_time']==0&&$brand_info['end_time']!=0)
	{
		
		//进行中
		if($brand_info['end_time']>get_gmtime())
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//过期
		if($brand_info['end_time']<=get_gmtime())
		{
			if($brand_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 2 where id =".$id);
			}
			return 2;
		}
	}
	
	//3 有开始时间，无结束时间
	if($brand_info['begin_time']!=0&&$brand_info['end_time']==0)
	{
		//进行中
		if($brand_info['begin_time']<=get_gmtime())
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//未开始
		if($brand_info['begin_time']>get_gmtime())
		{
			if($brand_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 1 where id =".$id);
			}
			return 1;
		}
	}
	
	//4 开始结束都有时间
	if($brand_info['begin_time']!=0&&$brand_info['end_time']!=0)
	{
		//未开始
		if($brand_info['begin_time']>get_gmtime())
		{
			if($brand_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 1 where id =".$id);
			}
			return 1;
		}
		//进行中
		if($brand_info['begin_time']<=get_gmtime()&&$brand_info['end_time']>get_gmtime())
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//过期

		if($brand_info['end_time']<=get_gmtime())
		{
			if($brand_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 2 where id =".$id);
			}
			return 2;
		}		
	}
}

//同步XXID的团购商品的状态,time_status,buy_status
function syn_deal_status($id,$dynamic = false)
{
	if(!$dynamic)
	{
		static $cache_goods_list;
		if($cache_goods_list[$id])return $cache_goods_list[$id];
	}
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." or uname = '".$id."'");
	//时间状态
	//1 无开始与结束时间
	if($deal_info['begin_time']==0&&$deal_info['end_time']==0)
	{
		if($deal_info['time_status']!=1)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
			$deal_info['time_status'] = 1;
		}
	}
	//2 无开始时间，有结束时间
	if($deal_info['begin_time']==0&&$deal_info['end_time']!=0)
	{
		
		//进行中
		if($deal_info['end_time']>get_gmtime())
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//过期
		if($deal_info['end_time']<=get_gmtime())
		{
			if($deal_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 2;
			}
		}
	}
	
	//3 有开始时间，无结束时间
	if($deal_info['begin_time']!=0&&$deal_info['end_time']==0)
	{
		//进行中
		if($deal_info['begin_time']<=get_gmtime())
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//未开始
		if($deal_info['begin_time']>get_gmtime())
		{
			if($deal_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 0;
			}
		}
	}
	
	//4 开始结束都有时间
	if($deal_info['begin_time']!=0&&$deal_info['end_time']!=0)
	{
		//未开始
		if($deal_info['begin_time']>get_gmtime())
		{
			if($deal_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 0;
			}
		}
		//进行中
		if($deal_info['begin_time']<=get_gmtime()&&$deal_info['end_time']>get_gmtime())
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//过期

		if($deal_info['end_time']<=get_gmtime())
		{
			if($deal_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 2;
			}
		}		
	}
	
	//开始更新 buy_status
	
		//未成功
		if($deal_info['buy_count']<$deal_info['min_bought'])
		{
			if($deal_info['buy_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 0,success_time = 0 where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 0;
				$deal_info['success_time'] = 0;
			}
		}
		//成功未卖光
		if($deal_info['buy_count']>=$deal_info['min_bought']&&(($deal_info['buy_count']<$deal_info['max_bought']&&$deal_info['max_bought']>0)||$deal_info['max_bought']==0))
		{
			if($deal_info['buy_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 1,success_time=".get_gmtime()." where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 1;
				$deal_info['success_time'] = get_gmtime();
			}
		}
		//卖光
		if($deal_info['buy_count']>=$deal_info['max_bought']&&$deal_info['max_bought']>0) //库存零表示不限
		{
			if($deal_info['buy_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 2 where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 2;
			}
		}

		//同步成功后，发相应的团购券发券
		$buy_status = $deal_info['buy_status'];
		if($buy_status > 0)
		{
			//成功后发券, 将user_id <> 0 且 is_valid = 0的发放出去
			$deal_coupons = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id <> 0 and is_valid = 0 and deal_id = ".intval($deal_info['id']));
			foreach($deal_coupons as $deal_coupon)
			{
				send_deal_coupon($deal_coupon['id']);	
			}			
		}
		
		if($deal_info['time_status']!=2&&$deal_info['reopen']!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal set reopen = 0 where id = ".intval($deal_info['id'])." and time_status <> 2");
			$deal_info['reopen'] = 0;
		}
		$cache_goods_list[$id] = $deal_info;
		return $deal_info;
}

//发放团购券
function send_deal_coupon($deal_coupon_id)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set is_valid = 1 where id = ".$deal_coupon_id." and user_id <> 0 and is_delete = 0 and is_valid = 0");
	$rs = $GLOBALS['db']->affected_rows();
	if($rs)
	{
		//发邮件团购券
		send_deal_coupon_mail($deal_coupon_id);	
		//发短信团购券
		send_deal_coupon_sms($deal_coupon_id);			
	}
}

//发邮件团购券
function send_deal_coupon_mail($deal_coupon_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);			
		if($coupon_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_COUPON'");
			$tmpl_content = $tmpl['content'];
			$coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
			$coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');			
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
			$coupon_data['user_name'] = $user_info['user_name'];
			$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
			$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
			$coupon_data['supplier_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
			$coupon_data['supplier_address']=$GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
				
			$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}	
			$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
			$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
			if($deal_type == 1&&$order_item)
			{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
			}
			
			$GLOBALS['tmpl']->assign("coupon",$coupon_data);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['YOU_GOT_COUPON'];
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			
		}
	}
}

//发积分邮件通知
function send_score_mail($order_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("SEND_SCORE_MAIL")==1)
	{
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);			
		if($order_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_SCORE'");
			$tmpl_content = $tmpl['content'];
			
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);			
			$GLOBALS['tmpl']->assign("username",$user_info['user_name']);
			$GLOBALS['tmpl']->assign("order_sn",$order_info['order_sn']);
			
			if($order_info['return_total_score']>0)
			{
				$GLOBALS['tmpl']->assign("score_value","获得".format_score(abs($order_info['return_total_score'])));
			}
			else
			{
				$GLOBALS['tmpl']->assign("score_value","消费".format_score(abs($order_info['return_total_score'])));
			}
			
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = "积分变更通知";
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			
		}
	}
}

//发短信团购券
function send_deal_coupon_sms($deal_coupon_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$forbid_sms = intval($GLOBALS['db']->getOne("select forbid_sms from ".DB_PREFIX."deal where id = ".$coupon_data['deal_id']));
			if($forbid_sms==0)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
				if($user_info['mobile']!='')
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_COUPON'");				
					$tmpl_content = $tmpl['content'];
					$coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
					$coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');			
					$coupon_data['user_name'] = $user_info['user_name'];
					$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$coupon_data['supplier_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
				     $coupon_data['supplier_address']=$GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
					$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
					$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
					if($deal_type == 1&&$order_item)
					{
						$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
						$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					}
					
	
					$GLOBALS['tmpl']->assign("coupon",$coupon_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
				}
			}
		}		
	}
}

//发积分短信通知
function send_score_sms($order_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("SEND_SCORE_SMS")==1)
	{
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);			
		if($order_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_SCORE'");
			$tmpl_content = $tmpl['content'];
			
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);	
			if($user_info['mobile']!="")
			{		
			$GLOBALS['tmpl']->assign("username",$user_info['user_name']);
			$GLOBALS['tmpl']->assign("order_sn",$order_info['order_sn']);
			
			if($order_info['return_total_score']>0)
			{
				$GLOBALS['tmpl']->assign("score_value","获得".format_score(abs($order_info['return_total_score'])));
			}
			else
			{
				$GLOBALS['tmpl']->assign("score_value","消费".format_score(abs($order_info['return_total_score'])));
			}
			
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
			
		}
	}
}


//发团购券确认使用的短信
function send_use_coupon_sms($deal_coupon_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("SMS_USE_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
			if($user_info['mobile']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USE_COUPON'");				
				$tmpl_content = $tmpl['content'];
				$coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
				$coupon_data['user_name'] = $user_info['user_name'];
				$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
				$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
				$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
				if($deal_type == 1&&$order_item)
				{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
				}
				$GLOBALS['tmpl']->assign("coupon",$coupon_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
			}
		}		
	}
}


//发团购券确认使用的邮件
function send_use_coupon_mail($deal_coupon_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_USE_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
			
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USE_COUPON'");				
				$tmpl_content = $tmpl['content'];
				$coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
				$coupon_data['user_name'] = $user_info['user_name'];
				$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
				$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
				$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
				if($deal_type == 1&&$order_item)
				{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
				}
				$GLOBALS['tmpl']->assign("coupon",$coupon_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
			
		}		
	}
}


//发短信抽奖
function send_lottery_sms($lottery_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("LOTTERY_SN_SMS")==1&&$lottery_id>0)
	{
		$lottery_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where id = ".$lottery_id);				
		if($lottery_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$lottery_data['user_id']);

				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_LOTTERY'");				
				$tmpl_content = $tmpl['content'];
				$lottery_data['user_name'] = $user_info['user_name'];
				$lottery_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);
				$lottery_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);
					
				$GLOBALS['tmpl']->assign("lottery",$lottery_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $lottery_data['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
			
		}		
	}
}

//发注册验证邮件
function send_user_verify_mail($user_id)
{
	if(app_conf("MAIL_ON")==1)
	{
		$verify_code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$verify_code."' where id = ".$user_id);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);			
		if($user_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_VERIFY'");
			$tmpl_content=  $tmpl['content'];
			$user_info['verify_url'] = get_domain().url("shop","user#verify",array("id"=>$user_info['id'],"code"=>$user_info['verify']));			
			$GLOBALS['tmpl']->assign("user",$user_info);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['REGISTER_SUCCESS'];
			$msg_data['content'] = addslashes($msg);;
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		}
	}
}


//发密码验证邮件
function send_user_password_mail($user_id)
{
	if(app_conf("MAIL_ON")==1)
	{
		$verify_code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set password_verify = '".$verify_code."' where id = ".$user_id);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);			
		if($user_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_PASSWORD'");
			$tmpl_content=  $tmpl['content'];
			$user_info['password_url'] = get_domain().url("shop","user#modify_password", array("code"=>$user_info['password_verify'],"id"=>$user_info['id']));			
			$GLOBALS['tmpl']->assign("user",$user_info);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['RESET_PASSWORD'];
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		}
	}
}


//发短信收款单
function send_payment_sms($notice_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_PAYMENT")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);				
		if($notice_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);
			if($user_info['mobile']!=''||$order_info['mobile']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_PAYMENT'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);			
				$notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
				$notice_data['money_format'] = format_price($notice_data['money']);
				$GLOBALS['tmpl']->assign("payment_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				if($user_info['mobile']!='')
				{
					$msg_data['dest'] = $user_info['mobile'];
				}
				else
				{
					$msg_data['dest'] = $order_info['mobile'];
				}
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				
			}
		}		
	}
}

//发邮件收款单
function send_payment_mail($notice_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_PAYMENT")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);				
		if($notice_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			if($user_info['email']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_PAYMENT'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);			
				$notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
				$notice_data['money_format'] = format_price($notice_data['money']);
				$GLOBALS['tmpl']->assign("payment_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['PAYMENT_NOTICE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}		
	}
}



//发邮件发货单
function send_delivery_mail($notice_sn,$deal_names = '',$order_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_DELIVERY")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);				
		if($notice_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			if($user_info['email']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DELIVERY'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);			
				$notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
				$notice_data['deal_names'] = $deal_names;
				$GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['DELIVERY_NOTICE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}		
	}
}

//发短信发货单
function send_delivery_sms($notice_sn,$deal_names = '',$order_id)
{
	if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_DELIVERY")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);						
		if($notice_data)
		{
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			if($user_info['mobile']!=''||$order_info['mobile']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DELIVERY'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);			
				$notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
				$notice_data['deal_names'] = $deal_names;
				$GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				if($user_info['mobile']!='')
				{
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
				
				if($order_info['mobile']!=''&&$order_info['mobile']!=$user_info['mobile'])
				{
					$msg_data['dest'] = $order_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}		
	}
}


//发短信验证码
function send_verify_sms($mobile,$code)
{
	if(app_conf("SMS_ON")==1)
	{
		
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_VERIFY_CODE'");				
				$tmpl_content = $tmpl['content'];
				$verify['mobile'] = $mobile;
				$verify['code'] = $code;
				$GLOBALS['tmpl']->assign("verify",$verify);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $mobile;
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
	}
}


//发邮件退订验证
function send_unsubscribe_mail($email)
{
	if(app_conf("MAIL_ON")==1)
	{
		if($email)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."mail_list set code = '".rand(1111,9999)."' where mail_address='".$email."' and code = ''");
			$email_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mail_list where mail_address = '".$email."' and code <> ''");
			if($email_item)
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_UNSUBSCRIBE'");				
				$tmpl_content = $tmpl['content'];
				$mail = $email_item;
				$mail['url'] = get_domain().url("shop","subscribe#dounsubscribe", array("code"=>base64_encode($mail['code']."|".$mail['mail_address'])));
				$GLOBALS['tmpl']->assign("mail",$mail);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $mail['mail_address'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['MAIL_UNSUBSCRIBE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = 0;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}		
	}
}

function get_deal_cate_name($cate_id)
{
	return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id =".$cate_id);
}
	
function get_deal_city_name($city_id)
{
	return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city where id =".$city_id);
}

function format_price($price)
{
	return app_conf("CURRENCY_UNIT")."".(round($price,2));
}
function format_score($score)
{
	return intval($score)."".app_conf("SCORE_UNIT");	
}

//utf8 字符串截取
function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr"))
    {
        $slice =  mb_substr($str, $start, $length, $charset);
        if($suffix&$slice!=$str) return $slice."…";
    	return $slice;
    }
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix&&$slice!=$str) return $slice."…";
    return $slice;
}


//字符编码转换
if(!function_exists("iconv"))
{	
	function iconv($in_charset,$out_charset,$str)
	{
		require 'libs/iconv.php';
		$chinese = new Chinese();
		return $chinese->Convert($in_charset,$out_charset,$str);
	}
}

//JSON兼容
if(!function_exists("json_encode"))
{	
	function json_encode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->encode($data);
	}
}
if(!function_exists("json_decode"))
{	
	function json_decode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->decode($data,1);
	}
}

//邮件格式验证的函数
function check_email($email)
{
	if(!preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$email))
	{
		return false;
	}
	else
	return true;
}

//验证手机号码
function check_mobile($mobile)
{
	if(!empty($mobile) && !preg_match("/^\d{6,}$/",$mobile))
	{
		return false;
	}
	else
	return true;
}

//跳转
function app_redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);    
    if(empty($msg))
        $msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
        	if(substr($url,0,1)=="/")
        	{        		
        		header("Location:".get_domain().$url);
        	}
        	else
        	{
        		header("Location:".$url);
        	}
            
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}



/**
 * 验证访问IP的有效性
 * @param ip地址 $ip_str
 * @param 访问页面 $module
 * @param 时间间隔 $time_span
 * @param 数据ID $id
 */
function check_ipop_limit($ip_str,$module,$time_span=0,$id=0)
{
		$op = es_session::get($module."_".$id."_ip");
    	if(empty($op))
    	{
    		$check['ip']	=	 get_client_ip();
    		$check['time']	=	get_gmtime();
    		es_session::set($module."_".$id."_ip",$check);    		
    		return true;  //不存在session时验证通过
    	}
    	else 
    	{   
    		$check['ip']	=	 get_client_ip();
    		$check['time']	=	get_gmtime();    
    		$origin	=	es_session::get($module."_".$id."_ip");
    		
    		if($check['ip']==$origin['ip'])
    		{
    			if($check['time'] - $origin['time'] < $time_span)
    			{
    				return false;
    			}
    			else 
    			{
    				es_session::set($module."_".$id."_ip",$check);
    				return true;  //不存在session时验证通过    				
    			}
    		}
    		else 
    		{
    			es_session::set($module."_".$id."_ip",$check);
    			return true;  //不存在session时验证通过
    		}
    	}
    }

//发放返利的函数
function pay_referrals($id)
{
	$referrals_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."referrals where id = ".$id);
	if($referrals_data)
	{
		$sql = "update ".DB_PREFIX."referrals set pay_time = ".get_gmtime()." where id = ".$id." and pay_time = 0 ";
		$GLOBALS['db']->query($sql);
		$rs = $GLOBALS['db']->affected_rows();
		if($rs)
		{
			//开始发放返利
			require_once APP_ROOT_PATH."system/libs/user.php";
			$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$referrals_data['order_id']);
			$user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['user_id']);
			$rel_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['rel_user_id']);
			$referral_amount = $referrals_data['money']>0?format_price($referrals_data['money']):format_score($referrals_data['score']);
			$msg = sprintf($GLOBALS['lang']['REFERRALS_LOG'],$order_sn,$rel_user_name,$referral_amount);
			modify_account(array('money'=>$referrals_data['money'],'score'=>$referrals_data['score']),$referrals_data['user_id'],$msg);	
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}
//发货的通用函数
/**
 * 
 * @param $order_id 订单ID
 * @param $order_deal_id  发货的订单商品ID
 * @param $delivery_sn  发货号
 */
function make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo='',$express_id = 0)
{
	//先删除原先相关的发货单号
	$GLOBALS['db']->query("delete from ".DB_PREFIX."delivery_notice where order_item_id = ".$order_deal_id);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	$delivery_notice['notice_sn'] = $delivery_sn;
	$delivery_notice['delivery_time'] = get_gmtime();
	$delivery_notice['order_item_id'] = $order_deal_id;
	$delivery_notice['user_id'] = $order_info['user_id'];	
	$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));
	$adm_id = intval($adm_session['adm_id']);
	$delivery_notice['admin_id'] = $adm_id;	
	$delivery_notice['memo'] = $memo;
	$delivery_notice['express_id'] = $express_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."delivery_notice",$delivery_notice,'INSERT','','SILENT');
	return $GLOBALS['db']->insert_id();
}

function get_deal_mail_content($deal_rs)
{
	$tmpl_content = file_get_contents(APP_ROOT_PATH."app/Tpl/".app_conf("TEMPLATE")."/deal_mail.html");
	$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);
	
	if($deal_rs)
	{
		foreach($deal_rs as $k=>$deal)
		{
			$deal_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where id = ".$deal['city_id']);
			$deal['city_name'] = $deal_city['name'];
			
			$send_date = to_date(get_gmtime(),'Y年m月d日');
			$weekarray = array("日","一","二","三","四","五","六");
			$send_date .= " 星期".$weekarray[to_date(get_gmtime(),"w")];
			$deal['send_date'] = $send_date;
			
			
			$deal['url'] = url("tuan","deal",array("id"=>$deal['id'],"city"=>$deal_city['uname']));
	
			if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
			$deal['save_money'] = $deal['origin_price'] - $deal['current_price'];			
			else
			$deal['save_money'] = $deal['origin_price']*((10-$deal['discount'])/10);
							
			if($deal['origin_price']>0&&floatval($deal['discount'])==0)
			$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);
	
			$deal['discount'] = round($deal['discount'],2);
	
			
			$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$deal['supplier_id']);
			$supplier_address_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$deal['supplier_id']." and is_main = 1");
			$deal['saler_name'] = $supplier_info['name'];
			$deal['saler_address'] = $supplier_address_info['address'];
			$deal['saler_tel'] = $supplier_address_info['tel'];
			
			if(app_conf("INVITE_REFERRALS_TYPE")==0)
			{
				$deal['referrals'] = format_price(app_conf("INVITE_REFERRALS"));
			}
			else
			{
				$deal['referrals'] = format_score(app_conf("INVITE_REFERRALS"));
			}
			
			
			$deal['referrals_url'] = url("tuan","referral",array("id"=>$deal['deal_id'],"city"=>$deal_city['uname']));
			$deal_rs[$k] = $deal;
		
		}
		$GLOBALS['tmpl']->assign("deal_rs",$deal_rs);
		$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);	
		
		$tmpl_path = app_conf("TMPL_DOMAIN_ROOT")==''?get_domain().APP_ROOT."/app/Tpl/":app_conf("TMPL_DOMAIN_ROOT")."/";		
		$content = str_replace("deal_mail/",$tmpl_path.app_conf("TEMPLATE")."/deal_mail/",$content);	
		return $content;
	}
	else
	return '';
}

/**
 * $notice.site_name
 * $notice.deal_name
 * $notice.site_url
 * @param $deal_id
 */
function get_deal_sms_content($deal_id)
{
	$tmpl_content = $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_NOTICE_SMS'");
	$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	if($deal)
	{
		$notice['site_name'] = app_conf("SHOP_TITLE");
		$notice['deal_name'] = $deal['sub_name'];
		$notice['site_url'] = get_domain().APP_ROOT;
		$GLOBALS['tmpl']->assign("notice",$notice);
		$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		return $content;
	}
	else
	return '';
}

/**
 * $bond.sn
 * $bond.password
 * $bond.name
 * $bond.user_name
 * $bond.begin_time_format
 * $bond.end_time_format
 * $bond.tel
 * $bond.address
 * $bond.route
 * $bond.open_time
 * @param $coupon_id
 * @param $location_id
 */
function get_coupon_content($coupon_id,$location_id)
{
	$tmpl_content = app_conf("COUPON_PRINT_TPL");
	$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id =".$coupon_id." and user_id = ".intval($GLOBALS['user_info']['id']));
	if(!$coupon_data)
	return '';	
	$location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$location_id);
	
	$bond['sn'] = $coupon_data['sn'];
	$bond['password'] = $coupon_data['password'];
	$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($coupon_data['deal_id']));
	$deal_type = intval($deal_info['deal_type']);
	if(!$order_item)
	{
		$order_item['name'] = $deal_info['name'];
		$order_item['sub_name'] = $deal_info['sub_name'];
	}
	
	$bond['name'] = $order_item['name'];
	$bond['sub_name'] = $order_item['sub_name'];
	if($deal_type == 1)
	{
		$bond['name'].= "&nbsp;&nbsp;".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
		$bond['sub_name'].= "&nbsp;&nbsp;".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
	}
	$bond['user_name'] = $GLOBALS['user_info']['user_name'];
	$bond['begin_time_format'] = to_date($coupon_data['begin_time']);
	$bond['end_time_format'] = to_date($coupon_data['end_time']);
	$bond['tel'] = $location_info['tel'];
	$bond['address'] = $location_info['address'];
	$bond['route'] = $location_info['route'];
	$bond['open_time'] = $location_info['open_time'];
	
	$GLOBALS['tmpl']->assign("bond",$bond);
	$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	return $content;

}


function gzip_out($content)
{
	header("Content-type: text/html; charset=utf-8");
    header("Cache-control: private");  //支持页面回跳
	$gzip = app_conf("GZIP_ON");
	if( intval($gzip)==1 )
	{
		if(!headers_sent()&&extension_loaded("zlib")&&preg_match("/gzip/i",$_SERVER["HTTP_ACCEPT_ENCODING"]))
		{
			$content = gzencode($content,9);	
			header("Content-Encoding: gzip");
			header("Content-Length: ".strlen($content));
			echo $content;
		}
		else
		echo $content;
	}else{
		echo $content;
	}
	
}

function order_log($log_info,$order_id)
{
	$data['id'] = 0;
	$data['log_info'] = $log_info;
	$data['log_time'] = get_gmtime();
	$data['order_id'] = $order_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_log", $data);
}


/**
	 * 保存图片
	 * @param array $upd_file  即上传的$_FILES数组
	 * @param array $key $_FILES 中的键名 为空则保存 $_FILES 中的所有图片
	 * @param string $dir 保存到的目录
	 * @param array $whs
	 	可生成多个缩略图
		数组 参数1 为宽度，
			 参数2为高度，
			 参数3为处理方式:0(缩放,默认)，1(剪裁)，
			 参数4为是否水印 默认为 0(不生成水印)
	 	array(
			'thumb1'=>array(300,300,0,0),
			'thumb2'=>array(100,100,0,0),
			'origin'=>array(0,0,0,0),  宽与高为0为直接上传
			...
		)，
	 * @param array $is_water 原图是否水印
	 * @return array
	 	array(
			'key'=>array(
				'name'=>图片名称，
				'url'=>原图web路径，
				'path'=>原图物理路径，
				有略图时
				'thumb'=>array(
					'thumb1'=>array('url'=>web路径,'path'=>物理路径),
					'thumb2'=>array('url'=>web路径,'path'=>物理路径),
					...
				)
			)
			....
		)
	 */
//$img = save_image_upload($_FILES,'avatar','temp',array('avatar'=>array(300,300,1,1)),1);
function save_image_upload($upd_file, $key='',$dir='temp', $whs=array(),$is_water=false,$need_return = false)
{
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
		
		$list = array();

		if(empty($key))
		{
			foreach($upd_file as $fkey=>$file)
			{
				$list[$fkey] = false;
				$image->init($file,$dir);
				if($image->save())
				{
					$list[$fkey] = array();
					$list[$fkey]['url'] = $image->file['target'];
					$list[$fkey]['path'] = $image->file['local_target'];
					$list[$fkey]['name'] = $image->file['prefix'];
				}
				else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
			}
		}
		else
		{
			$list[$key] = false;
			$image->init($upd_file[$key],$dir);
			if($image->save())
			{
				$list[$key] = array();
				$list[$key]['url'] = $image->file['target'];
				$list[$key]['path'] = $image->file['local_target'];
				$list[$key]['name'] = $image->file['prefix'];
			}
			else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
		}

		$water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
		$alpha = app_conf("WATER_ALPHA");
		$place = app_conf("WATER_POSITION");
		
		foreach($list as $lkey=>$item)
		{
				//循环生成规格图
				foreach($whs as $tkey=>$wh)
				{
					$list[$lkey]['thumb'][$tkey]['url'] = false;
					$list[$lkey]['thumb'][$tkey]['path'] = false;
					if($wh[0] > 0 || $wh[1] > 0)  //有宽高度
					{
						$thumb_type = isset($wh[2]) ? intval($wh[2]) : 0;  //剪裁还是缩放， 0缩放 1剪裁
						if($thumb = $image->thumb($item['path'],$wh[0],$wh[1],$thumb_type))
						{
							$list[$lkey]['thumb'][$tkey]['url'] = $thumb['url'];
							$list[$lkey]['thumb'][$tkey]['path'] = $thumb['path'];
							if(isset($wh[3]) && intval($wh[3]) > 0)//需要水印
							{
								$paths = pathinfo($list[$lkey]['thumb'][$tkey]['path']);
								$path = $paths['dirname'];
				        		$path = $path."/origin/";
				        		if (!is_dir($path)) { 
						             @mkdir($path);
						             @chmod($path, 0777);
					   			}   	    
				        		$filename = $paths['basename'];
								@file_put_contents($path.$filename,@file_get_contents($list[$lkey]['thumb'][$tkey]['path']));      
								$image->water($list[$lkey]['thumb'][$tkey]['path'],$water_image,$alpha, $place);
							}
						}
					}
				}
			if($is_water)
			{
				$paths = pathinfo($item['path']);
				$path = $paths['dirname'];
        		$path = $path."/origin/";
        		if (!is_dir($path)) { 
		             @mkdir($path);
		             @chmod($path, 0777);
	   			}   	    
        		$filename = $paths['basename'];
				@file_put_contents($path.$filename,@file_get_contents($item['path']));        		
				$image->water($item['path'],$water_image,$alpha, $place);
			}
		}			
		return $list;
}

function empty_tag($string)
{	
	$string = preg_replace(array("/\[img\]\d+\[\/img\]/","/\[[^\]]+\]/"),array("",""),$string);
	if(trim($string)=='')
	return $GLOBALS['lang']['ONLY_IMG'];
	else 
	return $string;
	//$string = str_replace(array("[img]","[/img]"),array("",""),$string);
}

//验证是否有非法字汇，未完成
function valid_str($string)
{
	$string = msubstr($string,0,5000);
	if(app_conf("FILTER_WORD")!='')
	$string = preg_replace("/".app_conf("FILTER_WORD")."/","*",$string);
	return $string;
}



//解析主题的内容

function decode_topic_without_allmedia($str)
{
	$expression_replace_array = load_auto_cache("expression_replace_none_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);
			
	
	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{

		foreach($matches[1] as $k=>$image_id)
		{			
			$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);			
	}
	
	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		$url_result = $GLOBALS['db']->getAll("select id,url from ".DB_PREFIX."urls where id in (".implode(",",$url_matches[1]).")");
		foreach($url_result as $kk=>$vv)
		{
			$url_data[$vv['id']] = $vv;
		}
		foreach($url_matches[1] as $k=>$url_id)
		{			
			if($url_data[$url_id])
			$url_matches[1][$k] = "<a href='".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."' target='_blank' title='".$url_data[$url_id]['url']."' >".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."</a>";
			else
			$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);	
	}	
	return $str;
}

function decode_topic_without_img($str)
{
	$expression_replace_array = load_auto_cache("expression_replace_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);
	
	$name_count = preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$str,$name_matches);
	if($name_count > 0)
	{
		$name_matches[0] = array_unique($name_matches[0]);
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $k=>$user_name)
		{				
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$user_name."' and is_effect = 1 and is_delete = 0");			
			if($uinfo)
			{
				$name_matches[1][$k] = "<a href='".url("shop","space",array("id"=>$uinfo['id']))."'  class='user_name' onmouseover='userCard.load(this,".$uinfo['id'].");' >@".$user_name."</a>";				
			}
			else
			$name_matches[1][$k] = $name_matches[0][$k];
		}
		$str = str_replace($name_matches[0],$name_matches[1],$str);			
	}	
	
	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{

		foreach($matches[1] as $k=>$image_id)
		{			
			$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);			
	}
	
	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		$url_result = $GLOBALS['db']->getAll("select id,url from ".DB_PREFIX."urls where id in (".implode(",",$url_matches[1]).")");
		foreach($url_result as $kk=>$vv)
		{
			$url_data[$vv['id']] = $vv;
		}
		foreach($url_matches[1] as $k=>$url_id)
		{			
			if($url_data[$url_id])
			$url_matches[1][$k] = "<a href='".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."' target='_blank' title='".$url_data[$url_id]['url']."' >".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."</a>";
			else
			$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);	
	}	
	return $str;
}

function decode_topic($str)
{	
	$expression_replace_array = load_auto_cache("expression_replace_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);
	
	$name_count = preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$str,$name_matches);
	if($name_count > 0)
	{
		$name_matches[0] = array_unique($name_matches[0]);
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $k=>$user_name)
		{				
			$user_name = addslashes(trim(htmlspecialchars($user_name)));
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$user_name."' and is_effect = 1 and is_delete = 0");			
			if($uinfo)
			{
				$name_matches[1][$k] = "<a href='".url("shop","space",array("id"=>$uinfo['id']))."' class='user_name'  onmouseover='userCard.load(this,".$uinfo['id'].");' >@".$user_name."</a>";				
			}
			else
			$name_matches[1][$k] = $name_matches[0][$k];
		}
		$str = str_replace($name_matches[0],$name_matches[1],$str);			
	}	
	
	
	//开始处理图片的解析
	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{
		$img_result = $GLOBALS['db']->getAll("select id,path,o_path,width,height from ".DB_PREFIX."topic_image where id in (".implode(",",$matches[1]).")");
		foreach($img_result as $kk=>$vv)
		{
			$img_data[$vv['id']] = $vv;
		}
		foreach($matches[1] as $k=>$image_id)
		{			
			if($img_data[$image_id])
			$matches[1][$k] = "<span class='toogle_topic_image_box'><img onclick='zoom(this);' src='".$img_data[$image_id]['path']."' b='".$img_data[$image_id]['o_path']."' s = '".$img_data[$image_id]['path']."' w='".$img_data[$image_id]['width']."' h='".$img_data[$image_id]['height']."' tag='s' /></span>";
			else
			$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);		
	}
	
	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		$url_result = $GLOBALS['db']->getAll("select id,url from ".DB_PREFIX."urls where id in (".implode(",",$url_matches[1]).")");
		foreach($url_result as $kk=>$vv)
		{
			$url_data[$vv['id']] = $vv;
		}
		foreach($url_matches[1] as $k=>$url_id)
		{			
			if($url_data[$url_id])
			$url_matches[1][$k] = "<a href='".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."' target='_blank' title='".$url_data[$url_id]['url']."' >".get_domain().url("shop","url",array("r"=>base64_encode($url_id)))."</a>";
			else
			$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);	
	}	
	return $str;
}





/**
 * utf8字符转Unicode字符
 * @param string $char 要转换的单字符
 * @return void
 */
function utf8_to_unicode($char)
{
	switch(strlen($char))
	{
		case 1:
			return ord($char);
		case 2:
			$n = (ord($char[0]) & 0x3f) << 6;
			$n += ord($char[1]) & 0x3f;
			return $n;
		case 3:
			$n = (ord($char[0]) & 0x1f) << 12;
			$n += (ord($char[1]) & 0x3f) << 6;
			$n += ord($char[2]) & 0x3f;
			return $n;
		case 4:
			$n = (ord($char[0]) & 0x0f) << 18;
			$n += (ord($char[1]) & 0x3f) << 12;
			$n += (ord($char[2]) & 0x3f) << 6;
			$n += ord($char[3]) & 0x3f;
			return $n;
	}
}

/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @param string $depart 分隔,默认为空格为单字
 * @return string
 */
function str_to_unicode_word($str,$depart=' ')
{
	$arr = array();
	$str_len = mb_strlen($str,'utf-8');
	for($i = 0;$i < $str_len;$i++)
	{
		$s = mb_substr($str,$i,1,'utf-8');
		if($s != ' ' && $s != '　')
		{
			$arr[] = 'ux'.utf8_to_unicode($s);
		}
	}
	return implode($depart,$arr);
}


/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @return string
 */
function str_to_unicode_string($str)
{
	$string = str_to_unicode_word($str,'');
	return $string;
}

//分词
function div_str($str)
{
	require_once APP_ROOT_PATH."system/libs/words.php";
	$words = words::segment($str);
	$words[] = $str;	
	return $words;
}


/**
 * 
 * @param $tag  //要插入的关键词
 * @param $table  //表名
 * @param $id  //数据ID
 * @param $field		// tag_match/name_match/cate_match/locate_match
 */
function insert_match_item($tag,$table,$id,$field)
{
	if($tag=='')
	return;
	
	$unicode_tag = str_to_unicode_string($tag);
	$sql = "select count(*) from ".DB_PREFIX.$table." where match(".$field.") against ('".$unicode_tag."' IN BOOLEAN MODE) and id = ".$id;
	$rs = $GLOBALS['db']->getOne($sql);
	if(intval($rs) == 0)
	{
		$match_row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX.$table." where id = ".$id);
		if($match_row[$field]=="")
		{
				$match_row[$field] = $unicode_tag;
				$match_row[$field."_row"] = $tag;
		}
		else
		{
				$match_row[$field] = $match_row[$field].",".$unicode_tag;
				$match_row[$field."_row"] = $match_row[$field."_row"].",".$tag;
		}
		$GLOBALS['db']->autoExecute(DB_PREFIX.$table, $match_row, $mode = 'UPDATE', "id=".$id, $querymode = 'SILENT');	
		
	}	
}

function get_all_parent_id($id,$table,&$arr = array())
{
	if(intval($id)>0)
	{
		$arr[] = $id;
		$pid = $GLOBALS['db']->getOne("select pid from ".$table." where id = ".$id);
		if($pid>0)
		{
			get_all_parent_id($pid,$table,$arr);
		}
	}
}

/**
 * 
 * @param $title_name 标题名称
 * @param $type  类型 0:话题 1:活动
 */
function syn_topic_title($title_name,$type=0)
{
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_title where name = '".$title_name."'");
	if(!$data)
	{
		$data = array("name"=>$title_name);
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_title", $data, $mode = 'INSERT', "", $querymode = 'SILENT');	
	}
	$topic_group = intval($type)==0?"share":"event";
	$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where title like '%".$title_name."%' and topic_group = '".$topic_group."'"));	
	$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = ".$count);
}

function syn_deal_match($deal_id)
{
	$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	if($deal)
	{
		$deal['name_match'] = "";
		$deal['name_match_row'] = "";
		$deal['deal_cate_match'] = "";
		$deal['deal_cate_match_row'] = "";
		$deal['shop_cate_match'] = "";
		$deal['shop_cate_match_row'] = "";
		$deal['tag_match'] = "";
		$deal['tag_match_row'] = "";
		$deal['locate_match'] = "";
		$deal['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal", $deal, $mode = 'UPDATE', "id=".$deal_id, $querymode = 'SILENT');	
		
		//同步商品的全文索引标签
		//获取筛选属性
		$deal_filters = $GLOBALS['db']->getAll("select filter from ".DB_PREFIX."deal_filter where deal_id = ".$deal_id);		
		foreach($deal_filters as $row)
		{
			$tags = preg_split("/[ ,]/i",$row['filter']);
			foreach($tags as $tag)
			{
				if(trim($tag)!="")
				insert_match_item($tag,"deal",$deal_id,"tag_match");
			}
		}
		
		//属性
		$deal_attrs = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_attr where deal_id = ".$deal_id);
		foreach($deal_attrs as $row)
		{
			$tag = trim($row['name']);
			if(trim($tag)!="")
			insert_match_item($tag,"deal",$deal_id,"tag_match");

		}
		
		//同步名称
		$name_arr = div_str(trim($deal['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"deal",$deal_id,"name_match");
		}
		$brand_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = ".$deal['brand_id']);
		insert_match_item($brand_name,"deal",$deal_id,"name_match");
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($deal['cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"deal_cate_match");
			}
		}
		$goods_cate =array();
		get_all_parent_id(intval($deal['shop_cate_id']),DB_PREFIX."shop_cate",$goods_cate);
		if(count($goods_cate)>0)
		{
			$goods_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."shop_cate where id in (".implode(",",$goods_cate).")");
			foreach ($goods_cates as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"shop_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_deal_link as l on l.deal_cate_type_id = t.id where l.deal_id = ".$deal['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"deal",$deal_id,"deal_cate_match");
		}
		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($deal['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"locate_match");
			}
		}
		$supplier_locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."deal_location_link as b on a.id = b.location_id where a.supplier_id = ".intval($deal['supplier_id'])." and b.deal_id = ".$deal['id']);
		foreach($supplier_locations as $locate)
		{		
			$address_arr = div_str(trim($locate['address']));
			foreach($address_arr as $address_item)
			{
				insert_match_item($address_item,"deal",$deal_id,"locate_match");
			}
			
			$areas = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$locate['id']);
			foreach($areas as $area)
			{
				if($area['pid']>0)
				{
					$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
					insert_match_item(trim($parent_name),"deal",$deal_id,"locate_match");
				}
				insert_match_item($area['name'],"deal",$deal_id,"locate_match");
			}
			
			//获取默认门店的坐标
			if($locate['is_main']==1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set xpoint = '".$locate['xpoint']."',ypoint = '".$locate['ypoint']."' where id = ".$deal_id);
			}
		}
	}	
}

function syn_event_match($event_id)
{
	$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id);
	if($event)
	{
		$event['name_match'] = "";
		$event['name_match_row'] = "";
		$event['deal_cate_match'] = "";
		$event['deal_cate_match_row'] = "";
		$event['locate_match'] = "";
		$event['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."event", $event, $mode = 'UPDATE', "id=".$event_id, $querymode = 'SILENT');	
				
		//同步名称
		$name_arr = div_str(trim($event['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"event",$event_id,"name_match");
		}
		$brief_arr = div_str(trim($event['brief'])); 
		foreach($brief_arr as $name_item)
		{
			insert_match_item($name_item,"event",$event_id,"name_match");
		}
		
		//分类类别
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event['id']);
		insert_match_item(trim($cate_name),"event",$event_id,"cate_match");

		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($event['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"event",$event_id,"locate_match");
			}
		}
		
		$address_arr = div_str(trim($event['address']));
		foreach($address_arr as $address_item)
		{
				insert_match_item($address_item,"event",$event_id,"locate_match");
		}
		
		$area_list = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."event_area_link as l on l.area_id = a.id where l.event_id = ".$event_id);
		
		foreach($area_list as $area)
		{
			if($area['pid']>0)
			{
				$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
				insert_match_item(trim($parent_name),"event",$event_id,"locate_match");
			}
			insert_match_item(trim($area['name']),"event",$event_id,"locate_match");
		}
	}	
}

function syn_supplier_location_match($location_id)
{
	$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
	if($location)
	{
		$location['name_match'] = "";
		$location['name_match_row'] = "";
		$location['deal_cate_match'] = "";
		$location['deal_cate_match_row'] = "";
		$location['locate_match'] = "";
		$location['locate_match_row'] = "";
		$location['tags_match'] = "";
		$location['tags_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location", $location, $mode = 'UPDATE', "id=".$location_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($location['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"supplier_location",$location_id,"name_match");
		}
		
		$brands = $GLOBALS['db']->getAll("select b.name from ".DB_PREFIX."brand as b left join ".DB_PREFIX."supplier_location_brand_link as l on l.brand_id = b.id where l.location_id = ".$location_id);
		foreach($brands as $brand)
		{
			insert_match_item($brand['name'],"supplier_location",$location_id,"name_match");
		}		
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($location['deal_cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"supplier_location",$location_id,"deal_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_location_link as l on l.deal_cate_type_id = t.id where l.location_id = ".$location['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"supplier_location",$location_id,"deal_cate_match");
		}
		
		//地址
		$address_arr = div_str(trim($location['address'])); 
		foreach($address_arr as $add)
		{
			insert_match_item($add,"supplier_location",$location_id,"locate_match");
		}
		
		//标签
		$tags_arr = explode(" ",$location["tags"]);
		foreach($tags_arr as $tgs){
			insert_match_item(trim($tgs),"supplier_location",$location_id,"tags_match");
		}
		
		$tags_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_tag where supplier_location_id = ".$location_id);
		foreach($tags_all as $kk=>$vv)
		{
			insert_match_item(trim($vv['tag_name']),"supplier_location",$location_id,"tags_match");
		}
		
		$area_list = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$location_id);
		
		foreach($area_list as $area)
		{
			if($area['pid']>0)
			{
				$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
				insert_match_item(trim($parent_name),"supplier_location",$location_id,"locate_match");
			}
			insert_match_item(trim($area['name']),"supplier_location",$location_id,"locate_match");
		}
	}	
}

function syn_supplier_match($supplier_id)
{
	$supplier = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
	if($supplier)
	{
		$supplier['name_match'] = "";
		$supplier['name_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier", $supplier, $mode = 'UPDATE', "id=".$supplier_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($supplier['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"supplier",$supplier_id,"name_match");
		}
		
	}
}

function syn_topic_match($topic_id)
{
	$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id);
	if(preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$topic['content'],$name_matches))
	{
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $match_item)
		{
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$match_item."' and is_effect = 1 and is_delete = 0");			
			if($uinfo)
			{
				insert_match_item($match_item,"topic",$topic_id,"user_name_match");		
			}
			
		}
	}
	$tags = explode(" ",$topic['tags']);
	foreach($tags as $tag)
	{
		insert_match_item(trim($tag),"topic",$topic_id,"keyword_match");
	}
	
	require_once APP_ROOT_PATH."system/libs/words.php";
	$segments = words::segment($topic['content']);
	foreach($segments as $segment)
	{
		insert_match_item($segment,"topic",$topic_id,"keyword_match");
	}
	$segments = div_str($topic['title']);
	foreach($segments as $segment)
	{
		insert_match_item($segment,"topic",$topic_id,"keyword_match");
	}
	
	$cate_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag_cate as t left join ".DB_PREFIX."topic_cate_link as l on l.cate_id = t.id where l.topic_id = ".$topic_id);
	foreach($cate_list as $k=>$v)
	{
		insert_match_item($v['name'],"topic",$topic_id,"cate_match");
	}
	
	$image_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$topic_id);
	$has_image = intval($image_count)>0?1:0;
	$GLOBALS['db']->query("update ".DB_PREFIX."topic set has_image = ".$has_image." where id = ".$topic_id);
	
}
function syn_youhui_match($youhui_id)
{
	$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$youhui_id);
	if($youhui)
	{
		$youhui['name_match'] = "";
		$youhui['name_match_row'] = "";
		$youhui['deal_cate_match'] = "";
		$youhui['deal_cate_match_row'] = "";
		$youhui['locate_match'] = "";
		$youhui['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui", $youhui, $mode = 'UPDATE', "id=".$youhui_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($youhui['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"youhui",$youhui_id,"name_match");
		}
		
		$brand_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = ".$youhui['brand_id']);
		insert_match_item($brand_name,"youhui",$youhui_id,"name_match");
			
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($youhui['deal_cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"youhui",$youhui_id,"deal_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_youhui_link as l on l.deal_cate_type_id = t.id where l.youhui_id = ".$youhui['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"youhui",$youhui_id,"deal_cate_match");
		}
		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($youhui['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"youhui",$youhui_id,"locate_match");
			}
		}

		$supplier_locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."youhui_location_link as b on a.id = b.location_id where a.supplier_id = ".intval($youhui['supplier_id'])." and b.youhui_id = ".$youhui['id']);
		
		foreach($supplier_locations as $locate)
		{		
			$address_arr = div_str(trim($locate['address']));
			foreach($address_arr as $address_item)
			{
				insert_match_item($address_item,"youhui",$youhui_id,"locate_match");
			}
			
			$areas = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$locate['id']);
			foreach($areas as $area)
			{
				if($area['pid']>0)
				{
					$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
					insert_match_item(trim($parent_name),"youhui",$youhui_id,"locate_match");
				}
				insert_match_item($area['name'],"youhui",$youhui_id,"locate_match");
			}
		}
	
	}
}
/**
 * 格式化点评内容
 */
function sys_get_dp_detail($data)
{

	$data['user_name'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$data['user_id']);
		
	$data['group_point'] = $GLOBALS['db']->getAll("select g.id,g.name,p.point from ".DB_PREFIX."supplier_location_dp_point_result as p left join ".DB_PREFIX."point_group as g on p.group_id = g.id where p.dp_id = ".$data['id']);
	$data['point_lang'] = $GLOBALS['lang']["dp_point_".$data['point']];
			
	$data['imgs'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_images where dp_id = ".$data['id']."  order by sort");
	$data['img_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_images where dp_id = ".$data['id']."  order by sort");

	if($data['from_data']!="")
	{
		$data['rel_url'] = parse_url_tag("u:".$data['rel_app_index']."|".$data['rel_route']."|".$data['rel_param']);	
		$data['rel_name'] = $GLOBALS['lang']['FROM_DATA_'.strtoupper($data['from_data'])];
	}
	//标签组
	$data['group_tag'] = $GLOBALS['db']->getAll("select g.id,g.name,t.tags from ".DB_PREFIX."supplier_location_dp_tag_result as t left join ".DB_PREFIX."tag_group as g on t.group_id = g.id where t.dp_id = ".$data['id']." and g.allow_dp = 1");
			//print_r($data['group_tag']);
	foreach($data['group_tag'] as $kk=>$vv)
	{
		$tags_arr = explode(" ",$vv['tags']);
		foreach($tags_arr as $kkk=>$vvv)
		{
			$vvv = trim($vvv);
			if($vvv!="")
			{
				$tags_item = array("name"=>$vvv,"url"=>url("youhui","store",array("tag"=>$vvv)));
				$data['group_tag'][$kk]['tags_arr'][] = $tags_item;
			}
					
		}
	}
	
	return $data;
	
}
/**
 * 更新商户统计
 */
function syn_supplier_locationcount($supplier_locationinfo)
{
	$supplier_locationinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$supplier_locationinfo['id']);
	$supplier_locationinfo['new_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id']." and create_time > ".$supplier_locationinfo['new_dp_count_time'])); 
	$supplier_locationinfo['dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['image_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['ref_avg_price'] = floatval($GLOBALS['db']->getOne("select avg(avg_price) from ".DB_PREFIX."supplier_location_dp where status=1 and supplier_location_id = ".$supplier_locationinfo['id']));
	$supplier_locationinfo['good_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point>=3 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['common_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point=2 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['bad_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point<2 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['good_rate'] = floatval($supplier_locationinfo['good_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['common_rate'] = floatval($supplier_locationinfo['common_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['bad_rate'] = floatval($supplier_locationinfo['bad_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['total_point'] = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])) 
	+ intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."supplier_location_sign_log where location_id = ".$supplier_locationinfo['id'])); 
	$dp_avg = floatval($GLOBALS['db']->getOne("select avg(point) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id']));
	$sign_avg = floatval($GLOBALS['db']->getOne("select avg(point) from ".DB_PREFIX."supplier_location_sign_log where location_id = ".$supplier_locationinfo['id'])); 	

	if($dp_avg>0&&$sign_avg>0)
	$supplier_locationinfo['avg_point'] = ($dp_avg+$sign_avg)/2;
	elseif ($dp_avg>0)
	$supplier_locationinfo['avg_point'] = $dp_avg;
	else
	$supplier_locationinfo['avg_point'] = $sign_avg;
	
	 $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$supplier_locationinfo,"UPDATE"," id= ".$supplier_locationinfo['id']);
	//同步分组评分
	$point_group_result = $GLOBALS['db']->getAll("select supplier_location_id,group_id,sum(point) as total_point,avg(point) as avg_point from ".DB_PREFIX."supplier_location_dp_point_result where supplier_location_id = ".$supplier_locationinfo['id']." group by group_id");
	foreach($point_group_result as $k=>$v)
	{
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_point_result where supplier_location_id=".intval($v['supplier_location_id'])." and group_id=".$v['group_id'])==0)
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_point_result", $v, "INSERT");
	}
}

//封装url

function url($app_index,$route="index",$param=array())
{
	$key = md5("URL_KEY_".$app_index.$route.serialize($param));
	if(isset($GLOBALS[$key]))
	{
		$url = $GLOBALS[$key];
		return $url;
	}
	
	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	
	$show_city = intval($GLOBALS['city_count'])>1?true:false;  //有多个城市时显示城市名称到url
	$route_array = explode("#",$route);
	
	if(isset($param)&&$param!=''&&!is_array($param))
	{
		$param['id'] = $param;
	}

	$module = strtolower(trim($route_array[0]));
	$action = strtolower(trim($route_array[1]));

	if(!$module||$module=='index')$module="";
	if(!$action||$action=='index')$action="";
	
	if(app_conf("URL_MODEL")==0)
	{
		//过滤主要的应用url
		if($app_index==app_conf("MAIN_APP"))
		$app_index = "index";
		
		//原始模式
		$url = APP_ROOT."/".$app_index.".php";
		if($module!=''||$action!=''||count($param)>0||$show_city) //有后缀参数
		{
			$url.="?";
		}

		if(isset($param['city']))
		{
			$url .= "city=".$param['city']."&";
			unset($param['city']);
		}		
		if($module&&$module!='')
		$url .= "ctl=".$module."&";
		if($action&&$action!='')
		$url .= "act=".$action."&";
		if(count($param)>0)
		{
			foreach($param as $k=>$v)
			{
				if($k&&$v)
				$url =$url.$k."=".urlencode($v)."&";
			}
		}
		if(substr($url,-1,1)=='&'||substr($url,-1,1)=='?') $url = substr($url,0,-1);
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}
	else
	{
		//重写的默认
		$url = APP_ROOT;
	
		if($app_index!='index')
		$url .= "/".$app_index;

		if($module&&$module!='')
		$url .= "/".$module;
		if($action&&$action!='')
		$url .= "-".$action;
		
		if(count($param)>0)
		{
			$url.="/";
			foreach($param as $k=>$v)
			{
				if($k!='city')
				$url =$url.$k."-".urlencode($v)."-";
			}
		}
		
		//过滤主要的应用url
		if($app_index==app_conf("MAIN_APP"))
		$url = str_replace("/".app_conf("MAIN_APP"),"",$url);
		
		$route = $module."#".$action;
		switch ($route)
		{
				case "xxx":
					break;
				default:
					break;
		}
				
		if(substr($url,-1,1)=='/'||substr($url,-1,1)=='-') $url = substr($url,0,-1);
		
		
		
		if(isset($param['city']))
		{
			$city_uname = $param['city'];
			if($city_uname=="all")
			{
				return "http://www.".app_conf("DOMAIN_ROOT").$url."/city-all";	
			}
			else
				{
				$domain = "http://".$city_uname.".".app_conf("DOMAIN_ROOT");	
				return $domain.$url;	
			}	
		}
		if($url=='')$url="/";
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}
	
	
}


function unicode_encode($name) {//to Unicode
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) {// 两个字节的字
            $cn_word = '\\'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
            $str .= strtoupper($cn_word);
        } else {
            $str .= $c2;
        }
    }
    return $str;
}

function unicode_decode($name) {//Unicode to
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)) {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            } else {
                $name .= $str;
            }
        }
    }
    return $name;
}

//生成短信发送的优惠券
/**
 * 
 * @param $youhui_id 优惠券ID
 * @param $mobile 手机号
 * @param $user_id 会员ID
 * 以下参数仅供 send_type = 2 预订验证券使用
 * @param $order_count 预订的人数
 * @param $is_private_room  预订是否包间
 * @param $date_time  预订时间
 */
function gen_verify_youhui($youhui_id,$mobile,$user_id,$order_count=0,$is_private_room=0,$date_time=0)
{
	
	$data = array();
	$data['youhui_id'] = intval($youhui_id);
	$data['user_id'] = intval($user_id);
	$data['user_id'] = intval($user_id);
	$data['mobile'] = $mobile;
	$data['order_count'] = intval($order_count);
	$data['order_count'] = intval($order_count);
	$data['is_private_room'] = intval($is_private_room);
	$data['date_time'] = intval($date_time);
	$data['create_time'] = get_gmtime();
	$data['youhui_sn'] = rand(10000000,99999999);
	do{
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_log", $data, $mode = 'INSERT', "", $querymode = 'SILENT');		
		$rs = $GLOBALS['db']->insert_id();	
	}while(intval($rs)==0);
	return $rs;
}

//生成短信发送的优惠券
/**
 * 
 * @param $youhui_id 优惠券ID
 * @param $mobile 手机号
 * @param $user_id 会员ID
 * 以下参数仅供 send_type = 2 预订验证券使用
 * @param $order_count 预订的人数
 * @param $is_private_room  预订是否包间
 * @param $date_time  预订时间
 */
function gen_verify_youhui_to_mobile($youhui_id,$mobile,$user_id,$order_count=0,$is_private_room=0,$date_time=0)
{
	
	$data = array();
	$data['youhui_id'] = intval($youhui_id);
	$data['user_id'] = intval($user_id);
	$data['mobile'] = $mobile;
	$data['order_count'] = intval($order_count);
	$data['order_count'] = intval($order_count);
	$data['is_private_room'] = intval($is_private_room);
	$data['date_time'] = intval($date_time);
	$data['create_time'] = get_gmtime();
	$data['youhui_sn'] = rand(10000000,99999999);
	$data['send_method']=1;
	do{
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_log", $data, $mode = 'INSERT', "", $querymode = 'SILENT');		
		$rs = $GLOBALS['db']->insert_id();	
	}while(intval($rs)==0);
	if($rs>0){
		$rs=$data['youhui_sn'] ;
	}
	return $rs;
}



//发送优惠券短信(直接下载无验证类型), 函数不验证发送次数是否超限，前台发送时验证
function send_youhui_sms($youhui_id,$user_id,$mobile)
{
	if(app_conf("SMS_ON")==1&&$mobile!='')
	{	
	
		$youhui_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$youhui_id);				
		if($youhui_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
			if($user_info)
			{
				$msg_data['dest'] = $mobile;
				$msg_data['send_type'] = 0;
				$msg_data['content'] = $youhui_data['sms_content'];
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = 0;
				$msg_data['is_youhui'] = 1;
				$msg_data['youhui_id'] = $youhui_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."youhui set sms_count = sms_count +1,view_count = view_count +1 where id = ".$youhui_id);
					return $id;
				}
				else 
				return false;
				
			}
			else
			return false;
		}	
		else
		return false;	
	}
	else
	{
		return false;
	}
}
//发送优惠券短信(验证类型), 函数不验证发送次数是否超限，前台发送时验证
function send_youhui_log_sms($log_id)
{
	if(app_conf("SMS_ON")==1)
	{	
		$log_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where id = ".$log_id);	
		$youhui_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$log_data['youhui_id']);				
		if($youhui_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$log_data['user_id']);
			if($user_info)
			{
				$msg_data['dest'] = $log_data['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = $youhui_data['sms_content']." - 验证码:".$log_data['youhui_sn'];
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = get_gmtime();
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = 0;
				$msg_data['is_youhui'] = 1;
				$msg_data['youhui_id'] = $youhui_data['id'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."youhui set sms_count = sms_count +1,view_count = view_count +1 where id = ".$youhui_data['id']);
					return $id;
				}
				else 
				return false;
				
			}
			else
			return false;
		}
		else
		return false;		
	}
	else
	{
		return false;
	}
}

//载入动态缓存数据
function load_dynamic_cache($name)
{
	if(isset($GLOBALS['dynamic_cache'][$name]))
	{
		return $GLOBALS['dynamic_cache'][$name];
	}
	else
	{
		return false;
	}
}

function set_dynamic_cache($name,$value)
{
	if(!isset($GLOBALS['dynamic_cache'][$name]))
	{
		if(count($GLOBALS['dynamic_cache'])>MAX_DYNAMIC_CACHE_SIZE)
		{
			array_shift($GLOBALS['dynamic_cache']);
		}
		$GLOBALS['dynamic_cache'][$name] = $value;		
	}
}


//同步一张图片到分享图片表(图片可以为本地获远程。 远程需要开启file_get_contents()的远程权限)
function syn_image_to_topic($image)
{
	$image_str = @file_get_contents($image);
	$file_name = md5(microtime(true)).rand(10,99).".jpg";
	
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
	   
	   $file_url = "./public/comment/".$dir."/".$file_name;	  
	   $file_path = APP_ROOT_PATH."public/comment/".$dir."/".$file_name;
	   @file_put_contents($file_path,$image_str);
	   $filesize = intval(@filesize($file_path));
	   if($filesize>0)
	   {
		   $icon_url = get_spec_image($file_url,100,100,1);		   
		   require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$image = new es_imagecls();
			$info = $image->getImageInfo($file_path);
			
			$image_data['width'] = intval($info[0]);
			$image_data['height'] = intval($info[1]);
			$image_data['name'] =$file_name;
			$image_data['filesize'] = $filesize;
			$image_data['create_time'] = get_gmtime();
			$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
			$image_data['user_name'] = addslashes($GLOBALS['user_info']['user_name']);
			$image_data['path'] = $icon_url;
			$image_data['o_path'] = $file_url;
			$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);				
			$data['id'] = intval($GLOBALS['db']->insert_id());
			$data['url'] = $icon_url;
	   }
	   return $data;
	
}

function load_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$result = $obj->load($param);
	}
	else
	$result = false;
	return $result;
}

function rm_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->rm($param);
	}
}


function clear_auto_cache($key)
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->clear_all();
	}
}

//获取随机会员提供关注
function get_rand_user($count,$is_daren=0,$uid=0)
{
	//第0阶梯达人，10个会员
	$danren_result_0 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_0");
	if($danren_result_0===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 10";	
		$danren_result_0 = $GLOBALS['db']->getAll($sql);
		if($danren_result_0)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_0",$danren_result_0,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_0",array(),3600);
	}	
	
	//第1阶梯达人，50个会员
	$danren_result_1 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_1");
	if($danren_result_1===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 10,50";	
		$danren_result_1 = $GLOBALS['db']->getAll($sql);
		if($danren_result_1)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_1",$danren_result_1,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_1",array(),3600);
	}
	
	//第2阶梯达人，2000个会员
	$danren_result_2 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_2");
	if($danren_result_2===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 50,2000";	
		$danren_result_2 = $GLOBALS['db']->getAll($sql);
		if($danren_result_2)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_2",$danren_result_2,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_2",array(),3600);
	}
	
	$danren_list[] = $danren_result_0;
	$danren_list[] = $danren_result_1;
	$danren_list[] = $danren_result_2;
	
	//非达人 , 2000个活跃会员
	$nodanren_result = $GLOBALS['cache']->get("RAND_USER_CACHE_NODAREN");
	if($nodanren_result===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 0 order by is_merchant desc,is_daren desc,topic_count desc limit 2000";	
		$nodanren_result = $GLOBALS['db']->getAll($sql);
		if($nodanren_result)
		$GLOBALS['cache']->set("RAND_USER_CACHE_NODAREN",$nodanren_result,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_NODAREN",array(),3600);
	}	
	
	$user_list = array();
	if($uid==0)
	{
		$user_group = 0; //阶梯数		
		while(count($user_list)<$count&&$user_group<3)
		{
			$current_count = count($user_list);
			for($loop=0;$loop<$count-$current_count;$loop++)
			{				
				$i = rand(0,count($danren_list[$user_group])-1);				
				$user_item = $danren_list[$user_group][$i];
				unset($danren_list[$user_group][$i]);
				sort($danren_list[$user_group]);
				if($user_item)
				$user_list[] = $user_item;
			}
			$user_group++;			
		}
		
		if(count($user_list)<$count&&$is_daren==0)
		{
			//人数还不足，并允许非达人
			$current_count = count($user_list);
			for($loop=0;$loop<$count-$current_count;$loop++)
			{				
				$i = rand(0,count($nodanren_result)-1);				
				$user_item = $nodanren_result[$i];
				unset($nodanren_result[$i]);
				sort($nodanren_result);
				if($user_item)
				$user_list[] = $user_item;
			}
		}

	}
	else
	{
		
		
		$user_group = 0; //阶梯数		
		while(count($user_list)<$count&&$user_group<3)
		{
			$current_count = count($user_list);
			//$loop_count 用于限制循环上限, $c用于计算个数, $i标识当前位置
			for($loop_count=0,$c=0;$c<$count-$current_count&&$loop_count<100;$loop_count++,$c++)
			{				
				$i = rand(0,count($danren_list[$user_group])-1);				
				$user_item = $danren_list[$user_group][$i];
				unset($danren_list[$user_group][$i]);
				sort($danren_list[$user_group]);
				if($user_item)
				{
					if($user_item['id']!=$uid&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$uid." and focused_user_id = ".intval($user_item['id']))==0)
					$user_list[] = $user_item;							
					else					
					$c--;									
				}
							
			}
			$user_group++;			
		}
		
		if(count($user_list)<$count&&$is_daren==0)
		{
			//人数还不足，并允许非达人
			
			$current_count = count($user_list);
			for($loop_count=0,$c=0;$c<$count-$current_count&&$loop_count<100;$loop_count++,$c++)
			{
				$i = rand(0,count($nodanren_result)-1);				
				$user_item = $nodanren_result[$i];
				unset($nodanren_result[$i]);
				sort($nodanren_result);
				if($user_item)
				{
					if($user_item['id']!=$uid&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$uid." and focused_user_id = ".intval($user_item['id']))==0)
					$user_list[] = $user_item;							
					else					
					$c--;									
				}		
			}
		}		
		
	}
	return $user_list;
	
}

/*ajax返回*/
function ajax_return($data)
{
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($data));
        exit;	
}


//增加会员活跃度
function increase_user_active($user_id,$log)
{
	$t_begin_time = to_timespan(to_date(get_gmtime(),"Y-m-d"));  //今天开始
	$t_end_time = to_timespan(to_date(get_gmtime(),"Y-m-d"))+ (24*3600 - 1);  //今天结束
	$y_begin_time = $t_begin_time - (24*3600); //昨天开始
	$y_end_time = $t_end_time - (24*3600);  //昨天结束
	
	$point = intval(app_conf("USER_ACTIVE_POINT"));
	$score = intval(app_conf("USER_ACTIVE_SCORE"));
	$money = doubleval(app_conf("USER_ACTIVE_MONEY"));
	$point_max = intval(app_conf("USER_ACTIVE_POINT_MAX"));
	$score_max = intval(app_conf("USER_ACTIVE_SCORE_MAX"));
	$money_max = doubleval(app_conf("USER_ACTIVE_MONEY_MAX"));
	
	$sum_money = doubleval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	$sum_score = intval($GLOBALS['db']->getOne("select sum(score) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	$sum_point = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	
	if($sum_money>=$money_max)$money = 0;
	if($sum_score>=$score_max)$score = 0;
	if($sum_point>=$point_max)$point = 0;
	
	if($money>0||$score>0||$point>0)
	{
		require_once  APP_ROOT_PATH."system/libs/user.php";
		modify_account(array("money"=>$money,"score"=>$score,"point"=>$point),$user_id,$log);
		$data['user_id'] = $user_id;
		$data['create_time'] = get_gmtime();
		$data['money'] = $money;
		$data['score'] = $score;
		$data['point'] = $point;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_active_log",$data);
	}
}

/**
 * 
 * @param $location_id 店铺ID
 * @param $data_type  tuan/event/youhui/daijin
 */
function recount_supplier_data_count($location_id,$data_type)
{
	switch ($data_type)
	{
		case "tuan":
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			$sql = " select count(*) from ".DB_PREFIX."deal_location_link as l left join ".DB_PREFIX."deal as d on d.id = l.deal_id where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 0 and d.time_status <> 2 and l.location_id = ".$location_id;
			$count = intval($GLOBALS['db']->getOne($sql));

			$store['tuan_count'] = $count;
			$tuan_youhui_cache = unserialize($store['tuan_youhui_cache']);
			$tuan_youhui_cache['tuan'] = $GLOBALS['db']->getRow("select d.name,d.id from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.is_effect = 1 and d.is_delete = 0 and d.time_status <> 2 and d.is_shop = 0 and l.location_id = ".$location_id." limit 1");
			$store['tuan_youhui_cache'] = serialize($tuan_youhui_cache);
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);
			return $store;
			
		case "daijin":
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			$sql = " select count(*) from ".DB_PREFIX."deal_location_link as l left join ".DB_PREFIX."deal as d on d.id = l.deal_id where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 2 and d.time_status <> 2 and l.location_id = ".$location_id;
			$count = intval($GLOBALS['db']->getOne($sql));
			$store['daijin_count'] = $count;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);		
			return $store;
			
		case "shop":
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			$sql = " select count(*) from ".DB_PREFIX."deal_location_link as l left join ".DB_PREFIX."deal as d on d.id = l.deal_id where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 1 and d.time_status <> 2 and l.location_id = ".$location_id;
			$count = intval($GLOBALS['db']->getOne($sql));
			$store['shop_count'] = $count;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);		
			return $store;
			
		case "event":
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			$time = get_gmtime();
			$time_condition = '  and (e.event_end_time = 0 or e.event_end_time > '.$time.' ) ';
			$sql = " select count(*) from ".DB_PREFIX."event_location_link as l left join ".DB_PREFIX."event as e on e.id = l.event_id where e.is_effect = 1  $time_condition and l.location_id = ".$location_id;
			$count = intval($GLOBALS['db']->getOne($sql));
			$store['event_count'] = $count;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);		
			return $store;
		
		case "youhui":
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			$time = get_gmtime();
			$time_condition = '  and (y.end_time = 0 or y.end_time > '.$time.' ) ';
			$sql = " select count(*) from ".DB_PREFIX."youhui_location_link as l left join ".DB_PREFIX."youhui as y on y.id = l.youhui_id where y.is_effect = 1  $time_condition and l.location_id = ".$location_id;
			$count = intval($GLOBALS['db']->getOne($sql));
			$store['youhui_count'] = $count;	
			
			$tuan_youhui_cache = unserialize($store['tuan_youhui_cache']);
			$tuan_youhui_cache['youhui'] =  $GLOBALS['db']->getRow("select y.name,y.id from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where y.is_effect = 1 and (y.end_time = 0 or y.end_time > ".get_gmtime().") and l.location_id = ".$location_id." limit 1");
			$store['tuan_youhui_cache'] = serialize($tuan_youhui_cache);
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);
			return $store;
		
	}
	
}

function build_deal_filter_condition($param)
{
	$area_id = intval($param['aid']);
	$quan_id = intval($param['qid']);
	$cate_id = intval($param['cid']);
	$deal_type_id = intval($param['tid']);
	$city_id = intval($GLOBALS['deal_city']['id']);
	$condition = "";
	if($city_id>0)
	{			
		$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
		if($ids)
		$condition .= " and city_id in (".implode(",",$ids).")";
	}
	if($area_id>0)
	{
			if($quan_id>0)
			{
						
					$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);				
					$kw_unicodes[] = str_to_unicode_string($area_name);
					
					$kw_unicode = implode(" ",$kw_unicodes);
					//有筛选
					$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";		
			}
			else
			{
				$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
				$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
				$unicode_quans = array();
				foreach($quan_list as $k=>$v){
					$unicode_quans[] = str_to_unicode_string($v['name']);
				}
				$kw_unicode = implode(" ", $unicode_quans);
				$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			}		
	}
	
	if($cate_id>0)
	{			
			$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$cate_id);
			$cate_name_unicode = str_to_unicode_string($cate_name);
					
			if($deal_type_id>0)
			{
				$deal_type_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate_type where id = ".$deal_type_id);
				$deal_type_name_unicode = str_to_unicode_string($deal_type_name);
				$condition .= " and (match(deal_cate_match) against('+".$cate_name_unicode." +".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
			}
			else
			{
				$condition .= " and (match(deal_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
			}
	}
	return $condition;
}

function is_animated_gif($filename){
 $fp=fopen($filename, 'rb');
 $filecontent=fread($fp, filesize($filename));
 fclose($fp);
 return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
}


function make_deal_cate_js()
{
	$js_file = APP_ROOT_PATH."public/runtime/app/deal_cate_conf.js";
	if(!file_exists($js_file))
	{
		$js_str = "var deal_cate_conf = [";
		$deal_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		foreach($deal_cates as $k=>$v)
		{
			$js_str.='{"n":"'.$v['name'].'","i":"'.$v['id'].'","s":[';
			$deal_cate_types = $GLOBALS['db']->getAll("select t.id,t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$v['id']." order by t.sort desc");
			foreach($deal_cate_types as $kk=>$vv)
			{
				$js_str .= '{"n":"'.$vv['name'].'","i":"'.$vv['id'].'"},';
			}
			if($deal_cate_types)
			$js_str = substr($js_str,0,-1);
			$js_str .= ']},';
		}
		if($deal_cates)
		$js_str = substr($js_str,0,-1);
		$js_str.="];";
		@file_put_contents($js_file,$js_str);
	}
}

function make_deal_region_js()
{
	$dir = APP_ROOT_PATH."public/runtime/app/deal_region_conf/";
	if (!is_dir($dir))
    {
             @mkdir($dir);
             @chmod($dir, 0777);
    }  
	$js_file = $dir.intval($GLOBALS['deal_city']['id']).".js";
	if(!file_exists($js_file))
	{
		$js_str = "var deal_region_conf = [";
		$areas = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area where city_id = ".intval($GLOBALS['deal_city']['id'])." and pid = 0 order by sort desc");
		foreach($areas as $k=>$v)
		{
			$js_str.='{"n":"'.$v['name'].'","i":"'.$v['id'].'","s":[';
			$regions = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area where city_id = ".intval($GLOBALS['deal_city']['id'])." and pid = ".$v['id']." order by sort desc");
			foreach($regions as $kk=>$vv)
			{
				$js_str .= '{"n":"'.$vv['name'].'","i":"'.$vv['id'].'"},';
			}
			if($regions)
			$js_str = substr($js_str,0,-1);
			$js_str .= ']},';
		}
		if($areas)
		$js_str = substr($js_str,0,-1);
		$js_str.="];";
		@file_put_contents($js_file,$js_str);
	}
}


function make_delivery_region_js()
{
	$path = APP_ROOT_PATH."public/runtime/app/region.js"; 
	if(!file_exists($path))
	{
		$jsStr = "var regionConf = ".get_delivery_region_js();		
		@file_put_contents($path,$jsStr);
	}
}
function get_delivery_region_js($pid = 0)
{

		$jsStr = "";
		$childRegionList = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = ".$pid." order by id asc");
		foreach($childRegionList as $childRegion)
		{
			if(empty($jsStr))
				$jsStr .= "{";
			else
				$jsStr .= ",";
				
			$childStr = get_delivery_region_js($childRegion['id']);
			$jsStr .= "\"r$childRegion[id]\":{\"i\":$childRegion[id],\"n\":\"$childRegion[name]\",\"c\":$childStr}";
		}
		
		if(!empty($jsStr))
			$jsStr .= "}";
		else
			$jsStr .= "\"\"";
				
		return $jsStr;

}

function update_sys_config()
{
	$filename = APP_ROOT_PATH."public/sys_config.php";
	if(!file_exists($filename))
	{
		//定义DB
		require APP_ROOT_PATH.'system/db/db.php';
		$dbcfg = require APP_ROOT_PATH."public/db_config.php";
		define('DB_PREFIX', $dbcfg['DB_PREFIX']); 
		if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
			mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
		$pconnect = false;
		$db = new mysql_db($dbcfg['DB_HOST'].":".$dbcfg['DB_PORT'], $dbcfg['DB_USER'],$dbcfg['DB_PWD'],$dbcfg['DB_NAME'],'utf8',$pconnect);
		//end 定义DB

		$sys_configs = $db->getAll("select * from ".DB_PREFIX."conf");
		$config_str = "<?php\n";
		$config_str .= "return array(\n";
		foreach($sys_configs as $k=>$v)
		{
			$config_str.="'".$v['name']."'=>'".addslashes($v['value'])."',\n";
		}
		$config_str.=");\n ?>";	
		file_put_contents($filename,$config_str);
		$url = APP_ROOT."/";
		app_redirect($url);
	}
}

/**
 * 更新结算状态
 * @param unknown_type $rel_id  相关的数据ID(团购券ID或订单商品ID)
 * @param unknown_type $deal_id
 */
function update_balance($rel_id,$deal_id)
{
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	if($deal_info['is_coupon']==1)
	{
		//团购券
		$sql = "update ".DB_PREFIX."deal_coupon set is_balance = 1 where id = ".$rel_id." and is_balance = 0";
		$GLOBALS['db']->query($sql);	
	}
	else
	{
		//订单商品
		$sql = "update ".DB_PREFIX."deal_order_item set is_balance = 1 where id = ".$rel_id." and is_balance = 0";
		$GLOBALS['db']->query($sql);
	}
}

function get_dstatus($status,$id)
{
		if($status)
		{
			$delivery_notice = $GLOBALS['db']->getRow("select dn.notice_sn,dn.delivery_time,de.name,dn.memo from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."express as de on dn.express_id = de.id where dn.order_item_id = ".$id);	
			return "已发货，发货单号：".$delivery_notice['name'].$delivery_notice['notice_sn']."，发货时间：".to_date($delivery_notice['delivery_time'])." 发货备注：<span title='".$delivery_notice['memo']."'>".msubstr($delivery_notice['memo'])."</span>";
		}
		else
		return "未发货";
}


function gen_qrcode($str,$size = 5)
{

	require_once APP_ROOT_PATH."system/phpqrcode/qrlib.php";

	$root_dir = APP_ROOT_PATH."public/images/qrcode/";
 	if (!is_dir($root_dir)) {
            @mkdir($root_dir);               
            @chmod($root_dir, 0777);
     }
     
     $filename = md5($str."|".$size);
     $hash_dir = $root_dir. '/c' . substr(md5($filename), 0, 1)."/";
     if (!is_dir($hash_dir))
     {
        @mkdir($hash_dir);
        @chmod($hash_dir, 0777);
     }   
	
	$filesave = $hash_dir.$filename.'.png';

	if(!file_exists($filesave))
	{
		QRcode::png($str, $filesave, 'Q', $size, 2); 
	}	
	return APP_ROOT."/public/images/qrcode/c". substr(md5($filename), 0, 1)."/".$filename.".png";       
}


function valid_tag($str)
{
	
	return preg_replace("/<(?!div|ol|ul|li|sup|sub|span|br|img|p|h1|h2|h3|h4|h5|h6|\/div|\/ol|\/ul|\/li|\/sup|\/sub|\/span|\/br|\/img|\/p|\/h1|\/h2|\/h3|\/h4|\/h5|\/h6|blockquote|\/blockquote|strike|\/strike|b|\/b|i|\/i|u|\/u)[^>]*>/i","",$str);
}

//显示语言
// lang($key,p1,p2......) 用于格式化 sprintf %s
function lang($key)
{
	$args = func_get_args();//取得所有传入参数的数组
	$key = strtoupper($key);
	if(isset($GLOBALS['lang'][$key]))
	{
		if(count($args)==1)
			return $GLOBALS['lang'][$key];
		else
		{
			$result = $key;
			$cmd = '$result'." = sprintf('".$GLOBALS['lang'][$key]."'";
			for ($i=1;$i<count($args);$i++)
			{
				$cmd .= ",'".$args[$i]."'";
			}
			$cmd.=");";
			eval($cmd);
			return $result;
		}
	}
	else
		return $key;
}

//缓存下商户
function cache_store_point($store_id)
{
	$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$store_id);
	
	if($store)
	{
		$group_point = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."point_group as pg left join ".DB_PREFIX."point_group_link as pgl on pg.id = pgl.point_group_id  where pgl.category_id = ".$store['deal_cate_id']." order by sort asc" );
		foreach($group_point as $kk=>$vv)
		{
			$group_point[$kk]['avg_point'] =  round(floatval($GLOBALS['db']->getOne("select avg_point from ".DB_PREFIX."supplier_location_point_result where supplier_location_id = ".$store['id']." and group_id = ".$vv['id'])),1);
		}
		$store['dp_group_point'] = serialize($group_point);
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$store['id'],"SILENT");
	}
	return $store;
}

function filter_ctl_act_req($str){
	$search = array("../","\n","\r","\t","\r\n","'","<",">","\"");
		
	return str_replace($search,"",$str);
}
?>