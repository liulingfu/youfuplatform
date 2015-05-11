<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

if (!defined('THINK_PATH')) exit();

//过滤请求
filter_request($_REQUEST);
filter_request($_GET);
filter_request($_POST);
define("AUTH_NOT_LOGIN", 1); //未登录的常量
define("AUTH_NOT_AUTH", 2);  //未授权常量

// 全站公共函数库
// 更改系统配置, 当更改数据库配置时为永久性修改， 修改配置文档中配置为临时修改
function conf($name,$value = false)
{
	if($value === false)
	{
		return C($name);
	}
	else
	{
		if(M("Conf")->where("is_effect=1 and name='".$name."'")->count()>0)
		{
			if(in_array($name,array('EXPIRED_TIME','SUBMIT_DELAY','SEND_SPAN','WATER_ALPHA','MAX_IMAGE_SIZE','INDEX_LEFT_STORE','INDEX_LEFT_TUAN','INDEX_LEFT_YOUHUI','INDEX_LEFT_DAIJIN','INDEX_LEFT_EVENT','INDEX_RIGHT_STORE','INDEX_RIGHT_TUAN','INDEX_RIGHT_YOUHUI','INDEX_RIGHT_DAIJIN','INDEX_RIGHT_EVENT','SIDE_DEAL_COUNT','DEAL_PAGE_SIZE','PAGE_SIZE','BATCH_PAGE_SIZE','HELP_CATE_LIMIT','HELP_ITEM_LIMIT','REC_HOT_LIMIT','REC_NEW_LIMIT','REC_BEST_LIMIT','REC_CATE_GOODS_LIMIT','SALE_LIST','INDEX_NOTICE_COUNT','RELATE_GOODS_LIMIT')))
			{
				$value = intval($value);
			}
			M("Conf")->where("is_effect=1 and name='".$name."'")->setField("value",$value);
		}
		C($name,$value);
	}
}



function write_timezone($zone='')
{
	if($zone=='')
	$zone = conf('TIME_ZONE');
		$var = array(
			'0'	=>	'UTC',
			'8'	=>	'PRC',
		);
		
		//开始将$db_config写入配置
	    $timezone_config_str 	 = 	"<?php\r\n";
	    $timezone_config_str	.=	"return array(\r\n";
	    $timezone_config_str.="'DEFAULT_TIMEZONE'=>'".$var[$zone]."',\r\n";
	    
	    $timezone_config_str.=");\r\n";
	    $timezone_config_str.="?>";
	   
	    @file_put_contents(get_real_path()."public/timezone_config.php",$timezone_config_str);
}



//后台日志记录
function save_log($msg,$status)
{
	if(conf("ADMIN_LOG")==1)
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$log_data['log_info'] = $msg;
		$log_data['log_time'] = get_gmtime();
		$log_data['log_admin'] = intval($adm_session['adm_id']);
		$log_data['log_ip']	= get_client_ip();
		$log_data['log_status'] = $status;	
		$log_data['module']	=	MODULE_NAME;
		$log_data['action'] = 	ACTION_NAME;
		M("Log")->add($log_data);
	}
}


//状态的显示
function get_toogle_status($tag,$id,$field)
{
	if($tag)
	{
		return "<span class='is_effect' onclick=\"toogle_status(".$id.",this,'".$field."');\">".l("YES")."</span>";
	}
	else
	{
		return "<span class='is_effect' onclick=\"toogle_status(".$id.",this,'".$field."');\">".l("NO")."</span>";
	}
}

//状态的显示
function get_is_effect($tag,$id)
{
	if($tag)
	{
		return "<span class='is_effect' onclick='set_effect(".$id.",this);'>".l("IS_EFFECT_1")."</span>";
	}
	else
	{
		return "<span class='is_effect' onclick='set_effect(".$id.",this);'>".l("IS_EFFECT_0")."</span>";
	}
}


//排序显示
function get_sort($sort,$id)
{
	if($tag)
	{
		return "<span class='sort_span' onclick='set_sort(".$id.",".$sort.",this);'>".$sort."</span>";
	}
	else
	{
		return "<span class='sort_span' onclick='set_sort(".$id.",".$sort.",this);'>".$sort."</span>";
	}
}
function get_nav($nav_id)
{
	return M("RoleNav")->where("id=".$nav_id)->getField("name");	
}
function get_module($module_id)
{
	return M("RoleModule")->where("id=".$module_id)->getField("module");
}
function get_group($group_id)
{
	if($group_data = M("RoleGroup")->where("id=".$group_id)->find())
	$group_name = $group_data['name'];
	else
	$group_name = L("SYSTEM_NODE");
	return $group_name;
}
function get_role_name($role_id)
{
	return M("Role")->where("id=".$role_id)->getField("name");
}
function get_admin_name($admin_id)
{
	$adm_name = M("Admin")->where("id=".$admin_id)->getField("adm_name");
	if($adm_name)
	return $adm_name;
	else
	return l("NONE_ADMIN_NAME");
}
function get_log_status($status)
{
	return l("LOG_STATUS_".$status);
}
//验证相关的函数
//验证排序字段
function check_sort($sort)
{
	if(!is_numeric($sort))
	{
		return false;
	}
	return true;
}
function check_empty($data)
{
	if(trim($data)=='')
	{
		return false;
	}
	return true;
}

function set_default($null,$adm_id)
{

	$admin_name = M("Admin")->where("id=".$adm_id)->getField("adm_name");
	if($admin_name == conf("DEFAULT_ADMIN"))
	{
		return "<span style='color:#f30;'>".l("DEFAULT_ADMIN")."</span>";
	}
	else
	{
		return "<a href='".u("Admin/set_default",array("id"=>$adm_id))."'>".l("SET_DEFAULT_ADMIN")."</a>";
	}
}
function get_order_sn($order_id)
{
	return M("DealOrder")->where("id=".$order_id)->getField("order_sn");
}
function get_order_sn_with_link($order_id)
{
	$order_info = M("DealOrder")->where("id=".$order_id)->find();
	if($order_info['type']==0)
	$str = l("DEAL_ORDER_TYPE_0")."：<a href='".u("DealOrder/deal_index",array("order_sn"=>$order_info['order_sn']))."'>".$order_info['order_sn']."</a>";
	else
	$str = l("DEAL_ORDER_TYPE_1")."：<a href='".u("DealOrder/incharge_index",array("order_sn"=>$order_info['order_sn']))."'>".$order_info['order_sn']."</a>";
	
	if($order_info['is_delete']==1)
	$str ="<span style='text-decoration:line-through;'>".$str."</span>";
	return $str;
}
function get_user_name($user_id)
{
	$user_name =  M("User")->where("id=".$user_id." and is_delete = 0")->getField("user_name");
	
	if(!$user_name)
	return l("NO_USER");
	else
	return "<a href='".u("User/index",array("user_name"=>$user_name))."'>".$user_name."</a>";
	
	
}
function get_user_name_js($user_id)
{
	$user_name =  M("User")->where("id=".$user_id." and is_delete = 0")->getField("user_name");
	
	if(!$user_name)
	return l("NO_USER");
	else
	return "<a href='javascript:void(0);' onclick='account(".$user_id.")'>".$user_name."</a>";
	
	
}
function get_pay_status($status)
{
	return L("PAY_STATUS_".$status);
}
function get_delivery_status($status,$order_id)
{
 	//,notice_sn|get_notice_info=$deal_order['notice_id']:{%DELIVERY_SN}
 	$order_item_ids = $GLOBALS['db']->getOne("select group_concat(id) from ".DB_PREFIX."deal_order_item where order_id = ".intval($order_id));
 	if(!$order_item_ids)
 	$order_item_ids = 0;
 	$rs = $GLOBALS['db']->getAll("select dn.notice_sn,dn.id from ".DB_PREFIX."delivery_notice as dn where dn.order_item_id in (".$order_item_ids.") ");
	$result = "";
 	foreach($rs as $row)
	{
		$result .= "&nbsp;".get_notice_info($row['notice_sn'],$row['id'])."<br />";
	}
	return L("ORDER_DELIVERY_STATUS_".$status)."<br />".$result;
}
function get_notice_info($sn,$notice_id)
{
		$express_name = M()->query("select e.name as ename from ".DB_PREFIX."express as e left join ".DB_PREFIX."delivery_notice as dn on dn.express_id = e.id where dn.id = ".$notice_id);
		$express_name = $express_name[0]['ename'];
		if($express_name)
		$str = $express_name."<br/>&nbsp;".$sn;
		else 
		$str = $sn;
		return $str;
}
function get_payment_name($payment_id)
{
	return M("Payment")->where("id=".$payment_id)->getField("name");
}
function get_delivery_name($delivery_id)
{
	return M("Delivery")->where("id=".$delivery_id)->getField("name");
}
function get_region_name($region_id)
{
	return M("DeliveryRegion")->where("id=".$region_id)->getField("name");
}
function get_city_name($id)
{
	return M("DealCity")->where("id=".$id)->getField("name");
}
function get_message_is_effect($status)
{
	return $status==1?l("YES"):l("NO");
}
function get_message_type($type_name,$rel_id)
{
	$show_name = M("MessageType")->where("type_name='".$type_name."'")->getField("show_name");
	if($type_name=='deal_order')
	{
		$order_sn = M("DealOrder")->where("id=".$rel_id)->getField("order_sn");
		if($order_sn)
		return "[".$order_sn."] <a href='".u("DealOrder/deal_index",array("id"=>$rel_id))."'>".$show_name."</a>";
		else
		return $show_name;
	}
	elseif($type_name=='deal')
	{
		$sub_name = M("Deal")->where("id=".$rel_id)->getField("sub_name");
		if($sub_name)
		return "[".$sub_name."]" .$show_name;
		else
		return $show_name;
	}
	elseif($type_name=='supplier')
	{
		$name = M("Supplier")->where("id=".$rel_id)->getField("name");
		if($name)
		return "[".$name."] <a href='".u("Supplier/index",array("id"=>$rel_id))."'>".$show_name."</a>";
		else
		return $show_name;
	}
	else
	{
		if($show_name)
		return $show_name;
		else
		return $type_name;
	}
}

function get_send_status($status)
{
	return L("SEND_STATUS_".$status);
}
function get_send_mail_type($deal_id)
{
	if($deal_id>0)
	return l("DEAL_NOTICE");
	else 
	return l("COMMON_NOTICE");
}
function get_send_type($send_type)
{
	return l("SEND_TYPE_".$send_type);
}

function get_all_files( $path )
{
		$list = array();
		$dir = @opendir($path);
	    while (false !== ($file = @readdir($dir)))
	    {
	    	if($file!='.'&&$file!='..')
	    	if( is_dir( $path.$file."/" ) ){
	         	$list = array_merge( $list , get_all_files( $path.$file."/" ) );
	        }
	        else 
	        {
	        	$list[] = $path.$file;
	        }
	    }
	    @closedir($dir);
	    return $list;
}
function get_order_item_name($id)
{
	return M("DealOrderItem")->where("id=".$id)->getField("name");
}
function get_supplier_name($id)
{
	return M("Supplier")->where("id=".$id)->getField("name");
}

function get_send_type_msg($status)
{
	if($status==0)
	{
		return l("SMS_SEND");
	}
	else
	{
		return l("MAIL_SEND");
	}
}
function show_content($content,$id)
{
	return "<a title='".l("VIEW")."' href='javascript:void(0);' onclick='show_content(".$id.")'>".l("VIEW")."</a>";
}



function get_is_send($is_send)
{
	if($is_send==0)
	return L("NO");
	else
	return L("YES");
}
function get_send_result($result)
{
	if($result==0)
	{
		return L("FAILED");
	}
	else
	{
		return L("SUCCESS");
	}
}

function get_is_buy($is_buy)
{
	return l("IS_BUY_".$is_buy);	
}

function get_point($point)
{
	return l("MESSAGE_POINT_".$point);
}

function get_status($status)
{
	if($status)
	{
		return l("YES");
	}
	else
	return l("NO");
}


function getMPageName($page)
{
	return L('MPAGE_'.strtoupper($page));
}

function getMTypeName($type)
{
	return L('MTYPE_'.strtoupper($type));
}
function get_submit_user($uid)
{
		if($uid==0)
		return "管理员发布";
		else
		{
			$uname = M("SupplierAccount")->where("id=".$uid)->getField("account_name");
			return $uname?$uname:"商家不存在";
		}
		
}
function get_event_cate_name($id)
	{
		return M("EventCate")->where("id=".$id)->getField("name");
	}
	
function show_table_substr($word,$cut=20)
{
	return "<span title='".$word."'>".msubstr($word,0,$cut)."</span>";
}

function get_balance_status($status)
{
	return l("BALANCE_".$status);
}

/**
 * 结算
 * @param unknown_type $rel_ids 结算的数据ID数组
 * @param unknown_type $deal_id 项目编号
 * @param memo 备注 
 */
function do_balance($rel_ids,$deal_id,$memo="")
{
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	$now = get_gmtime();
	if($deal_info['is_coupon']==1)
	{
		$sql = "update ".DB_PREFIX."deal_coupon set is_balance = 2,balance_time = ".$now.",balance_memo = '".$memo."' where id in (".implode(",",$rel_ids).") and is_balance <> 2";
		$sql_amount = "select sum(balance_price) from ".DB_PREFIX."deal_coupon where id in (".implode(",",$rel_ids).") and is_balance <> 2";
		$amount = $GLOBALS['db']->getOne($sql_amount);
		$GLOBALS['db']->query($sql);	
		
		//同步更新订单商品
		$sql_item = "select doi.* from ".DB_PREFIX."deal_order_item as doi where doi.id in(select distinct(dc.order_deal_id) as item_id from ".DB_PREFIX."deal_coupon as dc where dc.id in (".implode(",",$rel_ids)."))";
		$item_list = $GLOBALS['db']->getAll($sql_item);
		foreach($item_list as $k=>$v)
		{
			if($deal_info['deal_type']==1)
			{
				//按单
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set is_balance = 2,balance_time = ".$now.",balance_memo = '".$memo."' where id = ".$v['id']." and is_balance <> 2");
			}
			else
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where order_deal_id = ".$v['id']." and is_balance = 2")==$v['number'])
				{
					//全部	
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set is_balance = 2,balance_time = ".$now.",balance_memo = '".$memo."' where id = ".$v['id']." and is_balance <> 2");			
				}
				else
				{
					//部份
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set is_balance = 3,balance_time = ".$now.",balance_memo = '".$memo."' where id = ".$v['id']." and is_balance <> 2");			
				}
			}
		}		
	}
	else
	{
		$sql_amount = "select sum(balance_total_price) from ".DB_PREFIX."deal_order_item where id in (".implode(",",$rel_ids).") and is_balance <> 2";
		$amount = $GLOBALS['db']->getOne($sql_amount);
		$sql = "update ".DB_PREFIX."deal_order_item set is_balance = 2,balance_time = ".$now.",balance_memo = '".$memo."' where id in (".implode(",",$rel_ids).") and is_balance <> 2";
		
		$GLOBALS['db']->query($sql);
		
	}
	supplier_money_log($deal_info['supplier_id'],$amount, $deal_info['sub_name']."结算 ".$memo);
}

function supplier_money_log($supplier_id,$money,$info)
{
	if($money!=0)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier set money = money +".$money." where id = ".$supplier_id);
		
		$log_info['log_info'] = $info;
		$log_info['create_time'] = get_gmtime();
		$log_info['money'] = floatval($money);
		$log_info['supplier_id'] = $supplier_id;
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_log",$log_info);

	}
}



/**
 * 为某个团购券退款
 * 团购券退款总额
 * 返回: 0:退款失败 1:成功 2全单退款
 */
function refund_coupon($coupon_id)
{
	$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id." and refund_status = 1");
	if(!$coupon_data)return 0;
	$return = 1;
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$coupon_data['order_id']);
	if($coupon_data['coupon_price']<=$order_info['pay_amount']-$order_info['payment_fee']-$order_info['delivery_fee']-$order_info['refund_amound'])
	{
		$refund_price = $coupon_data['coupon_price'];
	}
	else
	{
		$refund_price = $order_info['pay_amount']-$order_info['refund_amound']-$order_info['payment_fee'];  //不退支付手续费;
		$return = 2;
	}
	$refund_price-=$coupon_data['coupon_money'];  //返现的钱也退
	$refund_score = 0-$coupon_data['coupon_score'];
	
	
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 2,is_valid = 2 where id = ".$coupon_data['id']);
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount+".$refund_price.",refund_status = 2,after_sale = 1 where id = ".$order_info['id']);
	
	$data = array("money"=>$refund_price,"score"=>$refund_score);
	require_once APP_ROOT_PATH."system/libs/user.php";
	modify_account($data,$order_info['user_id'],"来自".$order_info['order_sn']."中团购券".$coupon_data['sn']."的退款 ");
	order_log("团购券".$coupon_data['sn']."已退款".format_price($refund_price),$order_info['id']);
	return $return;
}
?>