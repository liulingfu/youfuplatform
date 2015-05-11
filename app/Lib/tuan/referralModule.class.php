<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/message.php';
require APP_ROOT_PATH.'app/Lib/side.php';

class referralModule extends TuanBaseModule
{
	public function index()
	{				
		$id = intval($_REQUEST['id']);
		$deal = get_referral_deal($id);
		if(!$deal||$deal['buy_type']==1||$deal['is_referral']==0)
		{
			app_redirect(url("tuan","index"));
		}
		
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['REFERRAL_PAGE']);
		$GLOBALS['tmpl']->display("referral.html");	
	}
}


/**
 * 获取指定的团购产品
 */
function get_referral_deal($id=0,$cate_id=0,$city_id=0)
{		
		$time = get_gmtime();
		if($id>0)
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_referral = 1 and is_effect = 1 and is_delete = 0 and (".$time.">= begin_time or begin_time = 0 or notice = 1) ");
		if(!$deal)
		{			
			$sql = "select * from ".DB_PREFIX."deal where is_referral = 1 and is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
			if($cate_id>0)
			{				
				$ids = load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and cate_id in (".implode(",",$ids).")";
			}
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				$sql .= " and city_id in (".implode(",",$ids).")";
			}
			$sql.=" order by sort desc";
			$deal = $GLOBALS['db']->getRow($sql);
			
		}
		
		if($deal)
		{
			if($deal['time_status']==0 && $deal['begin_time']==0 || $deal['begin_time']<get_gmtime())
			{
				syn_deal_status($deal['id']);
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_referral = 1 and id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
			}
			
			//格式化数据
			$deal['begin_time_format'] = to_date($deal['begin_time']);
			$deal['end_time_format'] = to_date($deal['end_time']);
			$deal['origin_price_format'] = format_price($deal['origin_price']);
			$deal['current_price_format'] = format_price($deal['current_price']);
			$deal['success_time_format']  = to_date($deal['success_time']);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
			$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
			else
			$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
			
			if($deal['origin_price']>0&&floatval($deal['discount'])==0)
			$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);

			$deal['discount'] = round($deal['discount'],2);
			
			$deal['save_price_format'] = format_price($deal['save_price']);
	
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
			
			
			$deal['success_time_tip'] = sprintf($GLOBALS['lang']['SUCCESS_TIME_TIP'],$deal['success_time_format'],$deal['min_bought']);
			
			//团购图片集
			$img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
			$deal['image_list'] = $img_list;
			
			//商户信息
			$deal['supplier_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($deal['supplier_id']));
			$deal['supplier_address_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");

			//属性列表
			$deal_attrs_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_attr where deal_id = ".intval($deal['id'])." order by id asc");
			if($deal_attrs_res)
			{
				foreach($deal_attrs_res as $k=>$v)
				{
					$deal_attr[$v['goods_type_attr_id']]['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."goods_type_attr where id = ".intval($v['goods_type_attr_id']));
					$deal_attr[$v['goods_type_attr_id']]['attrs'][] = $v;
				}
				$deal['deal_attr_list'] = $deal_attr;
			}
			if($deal['uname']!='')
			$durl = url("tuan","deal",array("id"=>$deal['uname']));
			else
			$durl = url("tuan","deal",array("id"=>$deal['id']));
			$deal['share_url'] = get_domain().$durl;
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
		}
		return $deal;
	
}
?>