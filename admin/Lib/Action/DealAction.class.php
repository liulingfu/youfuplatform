<?php
// +----------------------------------------------------------------------
// | easethink 易想商城系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealAction extends CommonAction{
	public function index()
	{
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		

		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(trim($_REQUEST['supplier_name'])!='')
		{
			if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier"))<50000)
			$sql  ="select group_concat(id) from ".DB_PREFIX."supplier where name like '%".trim($_REQUEST['supplier_name'])."%'";
			else 
			{
				$kws_div = div_str(trim($_REQUEST['supplier_name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$sql = "select group_concat(id) from ".DB_PREFIX."supplier where (match(name_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			}
			$ids = $GLOBALS['db']->getOne($sql);
			$map['supplier_id'] = array("in",$ids);
		}
		$map['publish_wait'] = 0;
		$map['is_shop'] = 0;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据

		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
			
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$log_info = $data['name'];
		$data['create_time'] = get_gmtime();
		$data['update_time'] = get_gmtime();
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
			
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
			
			//开始创建筛选项
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $list;
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						$filter_row = array();
						$filter_row['name'] = $filter_item;
						$filter_row['filter_group_id'] = $filter_group_id;
						M("Filter")->add($filter_row);
					}
				}
			}
		
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $list;
				M("DealCateTypeDealLink")->add($link_data);
			}
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $list;
				M("DealLocationLink")->add($link_data);
			}
			
			//成功提示
			syn_deal_status($list);
			syn_deal_match($list);
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"tuan");
			}
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		$this->assign ( 'vo', $vo );
		
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		foreach($delivery_list as $k=>$v)
		{
			$delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");			
			$delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();	
		}
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		//输出规格库存的配置
		$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$this->assign("attr_cfg_json",$attr_cfg_json);	
		$this->assign("attr_stock_json",$attr_stock_json);	
		
		$this->display ();
	}
	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}		
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']!=0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		
		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$data['update_time'] = get_gmtime();
		$data['publish_wait'] = 0;
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}

		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			
			//同步团购券
			
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set expire_refund = ".$data['expire_refund'].",any_refund = ".$data['any_refund'].",supplier_id=".$data['supplier_id'].",end_time=".$data['coupon_end_time'].",begin_time=".$data['coupon_begin_time']." where deal_id = ".$data['id']);

			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			M("DealAttr")->where("deal_id=".$data['id'])->delete();
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd		= $_REQUEST['deal_attr_stock_hd'];
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $data['id'];
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			//开始创建属性库存
			M("AttrStock")->where("deal_id=".$data['id'])->delete();
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $data['id'];
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				$sql = "select sum(oi.number) from ".DB_PREFIX."deal_order_item as oi left join ".
						DB_PREFIX."deal as d on d.id = oi.deal_id left join ".
						DB_PREFIX."deal_order as do on oi.order_id = do.id where".
						" do.pay_status = 2 and do.is_delete = 0 and d.id = ".$data['id'].
						" and oi.attr_str like '%".$attr_str[$row]."%'";
										
				$stock_data['buy_count'] = intval($GLOBALS['db']->getOne($sql));
				M("AttrStock")->add($stock_data);
			}

			M("FreeDelivery")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $data['id'];
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			M("DealDelivery")->where("deal_id=".$data['id'])->delete();
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $data['id'];
					M("DealDelivery")->add($delivery_conf);
			}
			
			
		//开始创建筛选项
			M("DealFilter")->where("deal_id=".$data['id'])->delete();
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $data['id'];
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						$filter_row = array();
						$filter_row['name'] = $filter_item;
						$filter_row['filter_group_id'] = $filter_group_id;
						M("Filter")->add($filter_row);
					}
				}
			}
			M("DealCateTypeDealLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $data['id'];
				M("DealCateTypeDealLink")->add($link_data);
			}
			
			M("DealLocationLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $data['id'];
				M("DealLocationLink")->add($link_data);
			}
			//成功提示
			syn_deal_status($data['id']);
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"tuan");
			}
			syn_deal_match($data['id']);
			
			rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
			rm_auto_cache("static_goods_info",array("id"=>$data['id']));
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",1);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					$locations = M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
					clear_auto_cache("byouhui_filter_nav_cache");
					clear_auto_cache("ytuan_filter_nav_cache");
					clear_auto_cache("tuan_filter_nav_cache");
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",0);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));					
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					clear_auto_cache("byouhui_filter_nav_cache");
					clear_auto_cache("ytuan_filter_nav_cache");
					$locations = M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
					
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				//删除的验证
				if(M("DealOrder")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("DEAL_ORDER_NOT_EMPTY"),$ajax);
				}
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealDelivery")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealPayment")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealAttr")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("AttrStock")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealCateTypeDealLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
					rm_auto_cache("static_goods_info",array("id"=>$data['id']));
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
					
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField('name');
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		rm_auto_cache("cache_deal_cart",array("id"=>$id));
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		M(MODULE_NAME)->where("id=".$id)->setField("update_time",get_gmtime());	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		rm_auto_cache("cache_deal_cart",array("id"=>$id));
		$locations = M("DealLocationLink")->where(array ('deal_id' => $id ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function attr_html()
	{
		$deal_goods_type = intval($_REQUEST['deal_goods_type']);
		$deal_id = intval($_REQUEST['deal_id']);
		
		if($deal_id>0&&M("Deal")->where("id=".$deal_id)->getField("deal_goods_type")==$deal_goods_type)
		{			
			$goods_type_attr = M()->query("select a.name as attr_name,a.is_checked as is_checked,a.price as price,b.* from ".conf("DB_PREFIX")."deal_attr as a left join ".conf("DB_PREFIX")."goods_type_attr as b on a.goods_type_attr_id = b.id where a.deal_id=".$deal_id." order by a.id asc");

			$goods_type_attr_id = 0;
			if($goods_type_attr)
			{
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
					if($goods_type_attr_id!=$v['id'])
					{
						$goods_type_attr[$k]['is_first'] = 1;
					}
					else
					{
						$goods_type_attr[$k]['is_first'] = 0;
					}
					$goods_type_attr_id = $v['id'];
				}	
			}
			else 
			{
				$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
					$goods_type_attr[$k]['is_first'] = 1;
				}
			}		
		}
		else
		{
			$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
			foreach($goods_type_attr as $k=>$v)
			{
				$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
				$goods_type_attr[$k]['is_first'] = 1;
			}		
		}
		$this->assign("goods_type_attr",$goods_type_attr);		
		$this->display();
	}
	
	public function show_detail()
	{
		$id = intval($_REQUEST['id']);
		
		$deal_info = M("Deal")->getById($id);
		$this->assign("deal_info",$deal_info);
		//购买的单数
		$real_user_count = intval($GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_user_count",$real_user_count);
		
		$real_buy_count =  intval($GLOBALS['db']->getOne("select sum(doi.number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_buy_count",$real_buy_count);
		
		$real_coupon_count = intval(M("DealCoupon")->where("deal_id=".$id." and is_valid=1")->count());
		$this->assign("real_coupon_count",$real_coupon_count);

		//总收款，不计退款
		$pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 group by pn.id");
		$pay_total = 0;
		foreach($pay_total_rows as $money)
		{
			$pay_total = $pay_total + floatval($money['money']);
		}		
		$this->assign("pay_total",$pay_total);

		//每个支付方式下的收款
		$payment_list = M("Payment")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_pay_total = 0;
			$payment_pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 and pn.payment_id = ".$v['id']." group by pn.id");
			foreach($payment_pay_total_rows as $money)
			{
				$payment_pay_total = $payment_pay_total + floatval($money['money']);
			}	
			$payment_list[$k]['pay_total'] = $payment_pay_total;
		}
		$this->assign("payment_list",$payment_list);
		
		
		//订单实收
		$order_total = 0;
		$order_total_rows = $GLOBALS['db']->getAll("select do.pay_amount as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($order_total_rows as $money)
		{
				$order_total = $order_total + floatval($money['money']);
		}	
		$this->assign("order_total",$order_total);
		
		//额外退款的订单
		$extra_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.extra_status > 0 and doi.deal_id = ".$id);
		$this->assign("extra_count",$extra_count);
		
		//额外退款的订单
		$aftersale_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.after_sale > 0 and doi.deal_id = ".$id);
		$this->assign("aftersale_count",$aftersale_count);
		
		//售后退款
		$refund_money = 0;
		$refund_total_rows = $GLOBALS['db']->getAll("select do.refund_money as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($refund_total_rows as $money)
		{
				$refund_money = $refund_money + floatval($money['money']);
		}
		$this->assign("refund_money",$refund_money);
		$this->display();
	}
	
	
	public function shop()
	{
		//分类
		$cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("ShopCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//输出品牌
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("shop_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['shop_cate_id'] = array("in",$cate_ids);
		}
		if(intval($_REQUEST['brand_id'])>0)
		$map['brand_id'] = intval($_REQUEST['brand_id']);
		
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	
	public function shop_add()
	{
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		$this->display();
	}
	
	public function shop_insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/shop_add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['shop_cate_id']==0)
		{
			$this->error(L("SHOP_CATE_EMPTY_TIP"));
		}		
		
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据

		if($data['brand_promote']==1)
		{
			//品牌促销
			$brand_info = M("Brand")->getById($data['brand_id']);
			if($brand_info['brand_promote']==1)
			{
				$data['begin_time'] = $brand_info['begin_time'];
				$data['end_time'] = $brand_info['end_time'];
			}
		}
		else
		{
			$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
			$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		}
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$log_info = $data['name'];
		$data['is_shop'] = 1;
		$data['create_time'] = get_gmtime();
		$data['update_time'] = get_gmtime();
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
		//开始创建筛选项
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $list;
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						if(trim($filter_item)!='')
						{
							$filter_row = array();
							$filter_row['name'] = $filter_item;
							$filter_row['filter_group_id'] = $filter_group_id;
							M("Filter")->add($filter_row);
						}
					}
				}
			}
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $list;
				M("DealLocationLink")->add($link_data);
			}
			
			//成功提示
			syn_deal_status($list);
			syn_deal_match($list);
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function shop_edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		$this->assign ( 'vo', $vo );
		

		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		foreach($delivery_list as $k=>$v)
		{
			$delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");			
			$delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();	
		}
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		//输出规格库存的配置
		$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$this->assign("attr_cfg_json",$attr_cfg_json);	
		$this->assign("attr_stock_json",$attr_stock_json);	
		
		$this->display ();
	}
	
	
	public function shop_update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/shop_edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['shop_cate_id']==0)
		{
			$this->error(L("SHOP_CATE_EMPTY_TIP"));
		}		
		
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		
		if($data['brand_promote']==1)
		{
			//品牌促销
			$brand_info = M("Brand")->getById($data['brand_id']);
			if($brand_info['brand_promote']==1)
			{
				$data['begin_time'] = $brand_info['begin_time'];
				$data['end_time'] = $brand_info['end_time'];
			}
		}
		else
		{
			$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
			$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		}

		  $data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
	     $data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}
		$data['update_time'] = get_gmtime();
		$data['publish_wait'] = 0;
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
			if (false !== $list) {
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set expire_refund = ".$data['expire_refund'].",any_refund = ".$data['any_refund'].",supplier_id=".$data['supplier_id'].",end_time=".$data['coupon_end_time'].",begin_time=".$data['coupon_begin_time']." where deal_id = ".$data['id']);
				
			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			M("DealAttr")->where("deal_id=".$data['id'])->delete();
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd		= $_REQUEST['deal_attr_stock_hd'];
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $data['id'];
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			//开始创建属性库存
			M("AttrStock")->where("deal_id=".$data['id'])->delete();
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $data['id'];
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				$sql = "select sum(oi.number) from ".DB_PREFIX."deal_order_item as oi left join ".
						DB_PREFIX."deal as d on d.id = oi.deal_id left join ".
						DB_PREFIX."deal_order as do on oi.order_id = do.id where".
						" do.pay_status = 2 and do.is_delete = 0 and d.id = ".$data['id'].
						" and oi.attr_str like '%".$attr_str[$row]."%'";
										
				$stock_data['buy_count'] = intval($GLOBALS['db']->getOne($sql));
				M("AttrStock")->add($stock_data);
			}

			M("FreeDelivery")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $data['id'];
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			M("DealDelivery")->where("deal_id=".$data['id'])->delete();
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $data['id'];
					M("DealDelivery")->add($delivery_conf);
			}
			
			//开始创建筛选项
			M("DealFilter")->where("deal_id=".$data['id'])->delete();
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $data['id'];
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						if(trim($filter_item)!='')
						{
							$filter_row = array();
							$filter_row['name'] = $filter_item;
							$filter_row['filter_group_id'] = $filter_group_id;
							M("Filter")->add($filter_row);
						}

					}
				}
			}
			
			M("DealLocationLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $data['id'];
				M("DealLocationLink")->add($link_data);
			}
			//成功提示
			syn_deal_status($data['id']);
			syn_deal_match($data['id']);
			rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
			rm_auto_cache("static_goods_info",array("id"=>$data['id']));
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	public function filter_html()
	{
		$shop_cate_id = intval($_REQUEST['shop_cate_id']);
		$deal_id = intval($_REQUEST['deal_id']);
		$ids = $this->get_parent_ids($shop_cate_id);
		$filter_group = M("FilterGroup")->where(array("cate_id"=>array("in",$ids)))->findAll();
		foreach($filter_group as $k=>$v)
		{
			$filter_group[$k]['value'] = M("DealFilter")->where("filter_group_id = ".$v['id']." and deal_id = ".$deal_id)->getField("filter");
		}
		$this->assign("filter_group",$filter_group);
		$this->display();
	}
	
	//获取当前分类的所有父分类包含本分类的ID
	private $cate_ids = array();
	private function get_parent_ids($shop_cate_id)
	{
		$pid = $shop_cate_id;
		do{
			$pid = M("ShopCate")->where("id=".$pid)->getField("pid");
			if($pid>0)
			$this->cate_ids[] = $pid;
		}while($pid!=0);
		$this->cate_ids[] = $shop_cate_id;
		return $this->cate_ids;
	}
	
	
	//可购买优惠券列表 is_shop = 2
	public function youhui()
	{
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		$map['is_shop'] = 2;
		$map['publish_wait'] = 0;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	
	public function youhui_add()
	{
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		$this->display();
	}
	public function youhui_insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/youhui_add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_YOUHUI_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_YOUHUI_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_YOUHUI_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_YOUHUI_CITY_EMPTY_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_YOUHUI_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据
		
		$data['is_shop'] = 2;
		$data['is_coupon'] = 1;
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$log_info = $data['name'];
		$data['create_time'] = get_gmtime();
		$data['update_time'] = get_gmtime();
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
			
			//开始创建筛选项
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $list;
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						$filter_row = array();
						$filter_row['name'] = $filter_item;
						$filter_row['filter_group_id'] = $filter_group_id;
						M("Filter")->add($filter_row);
					}
				}
			}
		
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $list;
				M("DealCateTypeDealLink")->add($link_data);
			}
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $list;
				M("DealLocationLink")->add($link_data);
			}
			//成功提示
			syn_deal_status($list);
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"daijin");
			}
			syn_deal_match($list);
			clear_auto_cache("byouhui_filter_nav_cache");
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	

	
	public function youhui_edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		$this->assign ( 'vo', $vo );
		
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		$this->display ();
	}
	
	
	public function youhui_update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/youhui_edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_YOUHUI_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_YOUHUI_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_YOUHUI_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_YOUHUI_CITY_EMPTY_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_YOUHUI_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		
		$data['is_shop'] = 2;
		$data['is_coupon'] = 1;
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$data['update_time'] = get_gmtime();
		$data['publish_wait'] = 0;
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set expire_refund = ".$data['expire_refund'].",any_refund = ".$data['any_refund'].",supplier_id=".$data['supplier_id'].",end_time=".$data['coupon_end_time'].",begin_time=".$data['coupon_begin_time']." where deal_id = ".$data['id']);
			
			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			//成功提示
			M("DealCateTypeDealLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $data['id'];
				M("DealCateTypeDealLink")->add($link_data);
			}
			
			M("DealLocationLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $data['id'];
				M("DealLocationLink")->add($link_data);
			}
			
			syn_deal_status($data['id']);
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"daijin");
			}
			syn_deal_match($data['id']);
			rm_auto_cache("cache_deal_cart",array("id"=>$data['id']));
			rm_auto_cache("static_goods_info",array("id"=>$data['id']));
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['cate_id']);
		$deal_id = intval($_REQUEST['deal_id']);
		$sub_cate_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_cate_type as c left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = c.id where l.cate_id = ".$cate_id);
		
		foreach($sub_cate_list as $k=>$v)
		{
			$sub_cate_list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cate_type_deal_link where deal_cate_type_id = ".$v['id']." and deal_id = ".$deal_id);
		}
		$this->assign("sub_cate_list",$sub_cate_list);
		
		if($sub_cate_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	function load_supplier_location()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$deal_id = intval($_REQUEST['deal_id']);
		
		$supplier_location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id);
		foreach($supplier_location_list as $k=>$v)
		{
			if($deal_id>0)
			$supplier_location_list[$k]['checked'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_location_link where location_id = ".$v['id']." and deal_id = ".$deal_id);
			else 
			$supplier_location_list[$k]['checked'] = true;
			
		}
		$this->assign("supplier_location_list",$supplier_location_list);
		
		if($supplier_location_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	
	
	public function publish()
	{
		$map['publish_wait'] = 1;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
}
?>