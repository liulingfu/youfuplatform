<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class SupplierAction extends CommonAction{
	public function index()
	{
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		
		
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
	    if(isset($order))
	    {
	    	$orderby = "order by ".$order." ".$sort;
	    }else 
	    {
	    	 $orderby = "";
	    }

		
		if(trim($_REQUEST['name'])!='')
		{
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier");
			if($total<50000)
			{
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where name like '%".trim($_REQUEST['name'])."%'  $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where name like '%".trim($_REQUEST['name'])."%'");			
			}
			else
			{
				$kws_div = div_str(trim($_REQUEST['name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE)  $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE)");
				
			}
		}
		else
		{
			$list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier  $orderby limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier");
		}
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);
			
		$this->display ();
		return;
	}
	public function add()
	{	
		$this->assign("new_sort", M(MODULE_NAME)->max("sort")+1);
		
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );

		$this->display ();
	}

	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				
				
				if(M("deal")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error (l("SUB_DEAL_EXIST"),$ajax);
				}
				
				if(M("SupplierLocation")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error ("请先清空所有的分店数据",$ajax);
				}
				if(M("SupplierAccount")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error ("请先清空所有的管理员帐户",$ajax);
				}
				
				M("SupplierMoneyLog")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				M("SupplierMoneySubmit")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					clear_auto_cache("static_goods_info");
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
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}					
		
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		
		if (false !== $list) {
			syn_supplier_match($list);
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		if (false !== $list) {
			syn_supplier_match($data['id']);
			clear_auto_cache("static_goods_info");
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
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
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function money_log()
	{
		$map['supplier_id'] = intval($_REQUEST['id']);
		$supplier_info = M("Supplier")->getById($map['supplier_id']);
		$this->assign("supplier_info",$supplier_info);
		$name=$this->getActionName();
		$model = D ("SupplierMoneyLog");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function charge_index()
	{
		$model = D ("SupplierMoneySubmit");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function docharge()
	{
		$id = intval($_REQUEST['id']);
		$charge = M("SupplierMoneySubmit")->getById($id);
		if($charge['status']==0)
		{
			M("SupplierMoneySubmit")->where("id=".$charge['id'])->setField("status",1);
			supplier_money_log($charge['supplier_id'], "-".$charge['money'], "提现成功");
			
			$this->error("确认提现成功");
		}
		else
		{
			$this->error("已提现");
		}
	}
}
?>