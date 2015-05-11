<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class BalanceAction extends CommonAction{
	public function index()
	{
		$deal_info = M("Deal")->getById(intval($_REQUEST['deal_id']));		
		$is_balance = intval($_REQUEST['is_balance']);
		$this->assign("is_balance",$is_balance);
		$this->assign("deal_info",$deal_info);
		if($deal_info)
		{			
			if($deal_info['is_coupon']==1)
			{				
				$map['deal_id'] = $deal_info['id'];
				$map['is_delete'] = 0;
				$map['user_id'] > 0;
				$map['is_valid'] = 1;
				$map['is_balance'] = $is_balance;
				if (method_exists ( $this, '_filter' )) {
					$this->_filter ( $map );
				}
				$name=$this->getActionName();
				$model = D ("DealCoupon");
				if (! empty ( $model )) {
					$this->_list ( $model, $map );
				}
											
				$dataList = $this->get("list");
				$pageBalance = 0;
				foreach($dataList as $k=>$v)
				{
					$dataList[$k]['name'] = M("DealOrderItem")->where("id=".$v['order_deal_id'])->getField("name");
					if(!$dataList[$k]['name'])
					$dataList[$k]['name'] = $deal_info['name'];
					$pageBalance+=$v['balance_price'];
				}				
				
				$tmp_map = $map;
				$tmp_map['is_balance'] = 0;
				$totalBalance0 = M("DealCoupon")->where($tmp_map)->sum("balance_price");
				$tmp_map['is_balance'] = 1;
				$totalBalance1 = M("DealCoupon")->where($tmp_map)->sum("balance_price");
				$tmp_map['is_balance'] = 2;
				$totalBalance2 = M("DealCoupon")->where($tmp_map)->sum("balance_price");
				
				$this->assign("totalBalance0",$totalBalance0+$totalBalance1);
				$this->assign("totalBalance1",$totalBalance1);
				$this->assign("totalBalance2",$totalBalance2);
				$this->assign("totalBalance3",$pageBalance);
				
				$this->assign ( 'list', $dataList );
				//团购券结算
				$this->display ("coupon_index");
			}
			else
			{
				$map['deal_id'] = $deal_info['id'];
				$map['is_balance'] = $is_balance;
				if (method_exists ( $this, '_filter' )) {
					$this->_filter ( $map );
				}
				$name=$this->getActionName();
				$model = D ("DealOrderItem");
				if (! empty ( $model )) {
					$this->_list ( $model, $map );
				}
											
				
				
				$tmp_map = $map;
				$tmp_map['is_balance'] = 0;
				$totalBalance0 = M("DealOrderItem")->where($tmp_map)->sum("balance_total_price");
				$tmp_map['is_balance'] = 1;
				$totalBalance1 = M("DealOrderItem")->where($tmp_map)->sum("balance_total_price");
				$tmp_map['is_balance'] = 2;
				$totalBalance2 = M("DealOrderItem")->where($tmp_map)->sum("balance_total_price");
				
				$this->assign("totalBalance0",$totalBalance0+$totalBalance1);
				$this->assign("totalBalance1",$totalBalance1);
				$this->assign("totalBalance2",$totalBalance2);
				
				$pageBalance = 0;
				$dataList = $this->get("list");
				foreach($dataList as $k=>$v)
				{
					$pageBalance+=$v['balance_total_price'];
				}	
				$this->assign("totalBalance3",$pageBalance);
				
				//订单结算
				$this->display("deal_index"); 
			}
		}
		else			
		$this->display ();
	}
	
	public function check_balance()
	{
		$id = trim($_REQUEST['id']);
		$deal_id = intval($_REQUEST['deal_id']);	
		$deal_info = M("Deal")->getById($deal_id);
		if($deal_info['is_coupon']==1)
		{
			if(M("DealCoupon")->where("deal_id=".$deal_id." and id in (".$id.") and is_balance = 2")->count()>0)
			{
				$this->error("已结算过的数据不能再次结算",1);
			}
			else
			{
				$this->success("success",1);
			}
		}
		else
		{
			if(M("DealOrderItem")->where("deal_id=".$deal_id." and id in (".$id.") and is_balance = 2")->count()>0)
			{
				$this->error("已结算过的数据不能再次结算",1);
			}
			else
			{
				$this->success("success",1);
			}
		}
	}

	 
	public function load_balance()
	{
		$id = trim($_REQUEST['id']);
		$deal_id = intval($_REQUEST['deal_id']);		
		$this->assign("id",$id);
		$this->assign("deal_id",$deal_id);
		$this->display();
	}
	
	public function do_balance()
	{
		$id = trim($_REQUEST['id']);
		$memo = trim(htmlspecialchars($_REQUEST['memo']));
		$ids = explode(",",$id);
		$deal_id = intval($_REQUEST['deal_id']);		
		do_balance($ids,$deal_id,$memo);		
		$this->success("结算成功");
	}
	
}
?>