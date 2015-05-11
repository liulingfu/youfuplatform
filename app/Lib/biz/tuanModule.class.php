<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/shop_lib.php";
class tuanModule extends BizBaseModule
{
	public function __construct()
	{
		parent::__construct();
		$this->check_auth();
	}
	public function index()
	{		
		require_once APP_ROOT_PATH."app/Lib/page.php";		
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);

		
		$GLOBALS['tmpl']->assign("page_title","产品列表");
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		
		$deal_list = $GLOBALS['db']->getAll("select distinct(d.id),d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and d.is_delete = 0 and d.supplier_id = ".$supplier_id." order by d.id desc limit ".$limit);
		
		foreach($deal_list as $k=>$v)
		{
			if($v['supplier_id']>0)
			$deal_list[$k]['supplier_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = ".$v['supplier_id']);
			
			$sql = "select sum(doi.number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$v['id']." and do.pay_status = 2 and do.is_delete = 0 and do.after_sale = 0";
			$deal_list[$k]['sale_count'] = intval($GLOBALS['db']->getOne($sql));
			//$deal_list[$k]['sql'] = $sql;
			$deal_list[$k]['coupon_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as dc where dc.deal_id = ".$v['id']." and dc.is_valid = 1 and dc.is_delete = 0 "));
			$deal_list[$k]['confirm_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as dc where dc.deal_id = ".$v['id']." and dc.is_valid = 1 and dc.is_delete = 0 and dc.confirm_account <> 0"));
		}
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$deal_count = $GLOBALS['db']->getOne("select count(distinct(d.id)) from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and d.is_delete = 0 and d.supplier_id = ".$supplier_id);
		$page = new Page($deal_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_tuan.html");
	}
	
	
	public function deal_coupon()
	{
		require_once APP_ROOT_PATH."app/Lib/page.php";	
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_COUPON_LIST']);
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($s_account_info['supplier_id']);
		$deal_id = intval($_REQUEST['id']);
		$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$deal_id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")";
		$deal_info = $GLOBALS['db']->getRow($sql);
		if(!$deal_info)
		{
			showErr($GLOBALS['lang']['NO_AUTH']);	
		}
		
		$coupon_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_location_link as l on c.deal_id = l.deal_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and c.deal_id = ".$deal_id." and c.is_valid = 1 and c.is_delete = 0 group by c.id order by c.id desc limit ".$limit);
	
		$GLOBALS['tmpl']->assign('coupon_list',$coupon_list);
		
		$coupon_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_location_link as l on c.deal_id = l.deal_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") and c.deal_id = ".$deal_id." and c.is_valid = 1 and c.is_delete = 0 group by l.location_id");
		$page = new Page($coupon_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("biz/biz_tuan_deal_coupon.html");
	}
	
	public function publish()
	{
		$s_account_info = es_session::get("account_info");
		foreach($s_account_info['location_ids'] as $id)
		{
			$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if($location)
			$locations[] = $location;
		}

		$GLOBALS['tmpl']->assign("page_title","发布产品");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		$shop_cate_list = get_cate_tree(0,1);
		$GLOBALS['tmpl']->assign("shop_cate_list",$shop_cate_list);
		
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_tuan_publish.html");
	}
	
	public function modify()
	{
		$s_account_info = es_session::get("account_info");
		foreach($s_account_info['location_ids'] as $id)
		{
			$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			if($location)
			$locations[] = $location;
		}
		$id = intval($_REQUEST['id']);
		$deal_info = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".$id." and d.publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$deal_info)
		{
			showErr("产品不存在或者没有编辑该产品的权限");
		}
		$deal_info['begin_time'] = $deal_info['begin_time']>0?to_date($deal_info['begin_time'],"Y-m-d"):"";
		$deal_info['end_time'] = $deal_info['end_time']>0?to_date($deal_info['end_time'],"Y-m-d"):"";
		$deal_info['images'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id = ".$deal_info['id']." order by sort asc");
	
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		$GLOBALS['tmpl']->assign("page_title","编辑产品");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		$shop_cate_list = get_cate_tree(0,1);
		$GLOBALS['tmpl']->assign("shop_cate_list",$shop_cate_list);
		
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->display("biz/biz_tuan_modify.html");
	}
	
	public function load_deal_cate_type()
	{
		$cate_id = intval($_REQUEST['cate_id']);
		$deal_id = intval($_REQUEST['deal_id']);
		$youhui_id = intval($_REQUEST['youhui_id']);

		
		$list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$cate_id." order by t.sort desc");
		if($list)
		{
			foreach($list as $k=>$v)
			{
				if($deal_id>0)
				$list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate_type_deal_link where deal_cate_type_id = ".$v['id']." and deal_id = ".$deal_id);
				
				if($youhui_id>0)
				$list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate_type_youhui_link where deal_cate_type_id = ".$v['id']." and youhui_id = ".$youhui_id);
			
			}
			$GLOBALS['tmpl']->assign("list",$list);
			$result['html'] = $GLOBALS['tmpl']->fetch("biz/biz_tuan_load_deal_cate_type.html");
			$result['status'] = 1;
		}
		else
		$result['status'] = 0;
		ajax_return($result);
	}
	
	
	public function submit_publish()
	{
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['sub_name'] = addslashes(htmlspecialchars(trim($_REQUEST['sub_name'])));
		$data['origin_price'] = doubleval($_REQUEST['origin_price']);
		$data['balance_price'] = doubleval($_REQUEST['balance_price']);
		$data['max_bought'] = intval($_REQUEST['max_bought']);
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));
		$data['is_shop'] = intval($_REQUEST['is_shop']);
		if($data['is_shop']==1)
		{
			$data['is_coupon'] = 0;
			$data['shop_cate_id'] = intval($_REQUEST['shop_cate_id']);
			$data['cate_id'] = 0;
		}
		else
		{
			$data['is_coupon'] = 1;
			$data['shop_cate_id'] = 0;
			$data['cate_id'] = intval($_REQUEST['cate_id']);
		}
		
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img']))));
		$data['description'] = trim(replace_public($_REQUEST['descript']));
		$data['description'] = valid_tag($data['description']);
		$data['account_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);		
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data);
		$deal_id = intval($GLOBALS['db']->insert_id());
		if($deal_id>0)
		{
			if($_REQUEST['img0']!='')
			{
			$deal_gallery_0 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0'])))),"deal_id"=>$deal_id,"sort"=>0);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_0);
			}
			
			if($_REQUEST['img1']!='')
			{
			$deal_gallery_1 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1'])))),"deal_id"=>$deal_id,"sort"=>1);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_1);
			}
			
			if($_REQUEST['img2']!='')
			{
			$deal_gallery_2 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2'])))),"deal_id"=>$deal_id,"sort"=>2);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_2);
			}
			
			if($_REQUEST['img3']!='')
			{
			$deal_gallery_3 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3'])))),"deal_id"=>$deal_id,"sort"=>3);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_3);
			}
			
			if($_REQUEST['img4']!='')
			{
			$deal_gallery_4 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4'])))),"deal_id"=>$deal_id,"sort"=>4);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_4);
			}
			
			if($_REQUEST['img5']!='')
			{
			$deal_gallery_5 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5'])))),"deal_id"=>$deal_id,"sort"=>5);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_5);
			}
			
			foreach($_REQUEST['deal_cate_type_id'] as $deal_cate_type_id)
			{
				if($deal_cate_type_id>0)
				{
				$deal_cate_type_link = array("deal_id"=>$deal_id,"deal_cate_type_id"=>intval($deal_cate_type_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cate_type_deal_link",$deal_cate_type_link);
				}
			}
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				if($location_id>0)
				{
				$location_link = array("deal_id"=>$deal_id,"location_id"=>intval($location_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_location_link",$location_link);
				}
			}
			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
	
	public function submit_modify()
	{
		$s_account_info = es_session::get("account_info");
		$account_id = intval($s_account_info['id']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.id = ".$id." and publish_wait = 1 and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if(!$data)
		{
			showErr("产品不存在或者没有编辑该产品的权限");
		}
		
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['sub_name'] = addslashes(htmlspecialchars(trim($_REQUEST['sub_name'])));
		$data['origin_price'] = doubleval($_REQUEST['origin_price']);
		$data['balance_price'] = doubleval($_REQUEST['balance_price']);
		$data['max_bought'] = intval($_REQUEST['max_bought']);
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));
		$data['is_shop'] = intval($_REQUEST['is_shop']);
		if($data['is_shop']==1)
		{
			$data['is_coupon'] = 0;
			$data['shop_cate_id'] = intval($_REQUEST['shop_cate_id']);
			$data['cate_id'] = 0;
		}
		else
		{
			$data['is_coupon'] = 1;
			$data['shop_cate_id'] = 0;
			$data['cate_id'] = intval($_REQUEST['cate_id']);
		}
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img']))));
		$data['description'] = trim(replace_public($_REQUEST['descript']));
		$data['description'] = valid_tag($data['description']);
		$data['account_id'] = intval($account_id);
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = intval($s_account_info['supplier_id']);
		
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"UPDATE","id=".$data['id']);
		$deal_id = $data['id'];
		if($deal_id>0)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_gallery where deal_id = ".$deal_id." and sort < 6");
			if($_REQUEST['img0']!='')
			{
			$deal_gallery_0 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0'])))),"deal_id"=>$deal_id,"sort"=>0);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_0);
			}
			
			if($_REQUEST['img1']!='')
			{
			$deal_gallery_1 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1'])))),"deal_id"=>$deal_id,"sort"=>1);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_1);
			}
			
			if($_REQUEST['img2']!='')
			{
			$deal_gallery_2 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2'])))),"deal_id"=>$deal_id,"sort"=>2);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_2);
			}
			
			if($_REQUEST['img3']!='')
			{
			$deal_gallery_3 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3'])))),"deal_id"=>$deal_id,"sort"=>3);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_3);
			}
			
			if($_REQUEST['img4']!='')
			{
			$deal_gallery_4 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4'])))),"deal_id"=>$deal_id,"sort"=>4);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_4);
			}
			
			if($_REQUEST['img5']!='')
			{
			$deal_gallery_5 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5'])))),"deal_id"=>$deal_id,"sort"=>5);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_5);
			}
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cate_type_deal_link where deal_id = ".$deal_id);
			foreach($_REQUEST['deal_cate_type_id'] as $deal_cate_type_id)
			{
				if($deal_cate_type_id>0)
				{
				$deal_cate_type_link = array("deal_id"=>$deal_id,"deal_cate_type_id"=>intval($deal_cate_type_id));
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cate_type_deal_link",$deal_cate_type_link);
				}
			}
			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
}
?>