<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealCouponAction extends CommonAction{
	public function index()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		if($deal_info['is_coupon']==0)
		{
			$this->error(l("DEAL_NO_COUPON"));
		}

		$this->assign("deal_info",$deal_info);
		
		
		//处理-1情况的select
		if(!isset($_REQUEST['is_valid']))
		{
			$_REQUEST['is_valid'] = -1;
		}
		if(!isset($_REQUEST['is_confirm']))
		{
			$_REQUEST['is_confirm'] = -1;
		}
		if(!isset($_REQUEST['refund_status']))
		{
			$_REQUEST['refund_status'] = -1;
		}
		
		//定义条件
		$map['is_delete'] = 0;
		$map['deal_id'] = $deal_id;
		if(trim($_REQUEST['sn'])!='')
		{
			$map['sn'] = array('like','%'.trim($_REQUEST['sn']).'%');
		}
		if(trim($_REQUEST['user_id'])!='')
		{
			$map['user_id'] = intval(trim($_REQUEST['user_id']));
		}
		if(intval($_REQUEST['is_valid'])>=0)
		{
			$map['is_valid'] = intval($_REQUEST['is_valid']);
		}
	if(intval($_REQUEST['is_confirm'])>=0)
		{
			if(intval($_REQUEST['is_confirm'])==0)
			$map['confirm_time'] = 0;
			else
			$map['confirm_time'] = array('gt',0);
		}
		if(intval($_REQUEST['refund_status'])>=0)
		{
			$map['refund_status'] = intval($_REQUEST['refund_status']);
		}
	
		
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
	public function add()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		$this->assign("deal_info",$deal_info);
		$this->display();
	}
	
	public function insert() {
		require_once APP_ROOT_PATH."/system/libs/deal.php";
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add",array("deal_id"=>$data['deal_id'])));
		if(M("DealCoupon")->where("deal_id=".$data['deal_id']." and sn='".$data['sn']."'")->count()>0)
		{
			$this->error(L("DEAL_COUPON_SN_EXIST"));
		}
		if(intval($data['user_id'])>0&&M("User")->where("id=".intval($data['user_id']))->count()==0)
		{
			$this->error(L("USER_NOT_EXIST"));
		}
		
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		
		// 更新数据
		$log_info = $data['sn'];
		$res = add_coupon($data['deal_id'],$data['user_id'],$data['is_valid'],$data['sn'],$data['password'],$data['begin_time'],$data['end_time']);
		$status= $res['status'];
		if (false != $status) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$deal_info = M("Deal")->getById($vo['deal_id']);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		$this->assign("deal_info",$deal_info);
		
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("account_name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(M("DealCoupon")->where("deal_id=".$data['deal_id']." and sn='".$data['sn']."'")->count()>0)
		{
			$this->error(L("DEAL_COUPON_SN_EXIST"));
		}
		if(intval($data['user_id'])>0&&M("User")->where("id=".intval($data['user_id']))->count()==0)
		{
			$this->error(L("USER_NOT_EXIST"));
		}
		
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
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
	
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['sn'];	
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
	
	
	public function import()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		if($deal_info['is_coupon']==0)
		{
			$this->error(L("DEAL_NO_COUPON"));
		}
		$this->assign("deal_info",$deal_info);
		$this->display();

	}
	

	public function sample()
	{
		$content = iconv("utf-8","gbk","序列号,密码,生效时间,过期时间" . "\n");
		$timestr = to_date(get_gmtime());
		$content .= iconv("utf-8","gbk","DEMO_123456,12345678,".$timestr . ",".$timestr . "\n");	
	    header("Content-Disposition: attachment; filename=sample.csv");
	    echo $content; 
	}
	
	
	/*导入csv*/	
	public function importInsert()
	{
		require_once APP_ROOT_PATH."/system/libs/deal.php";
		$file = $_FILES['file'];		
		$deal_id = intval($_REQUEST["deal_id"]);
		$content = @file_get_contents($file['tmp_name']);
		$content = explode("\n",$content);
		unset($content[0]);
		$count = 0;
		if($content){
			foreach($content as $k=>$v)
			{
					if($v!='')
					{
						$imp_row = explode(",",$v);
						$sn = trim($imp_row[0]);
						$password = trim($imp_row[1]);
						$begin_time = to_timespan(trim($imp_row[2]));
						$end_time = to_timespan(trim($imp_row[3]));
						$res = add_coupon($deal_id,0,0,$sn,$password,$begin_time,$end_time);
	
						if($res['status'])
						{
							$count++;
						}
					}
			}
			save_log(sprintf(L("IMPORT_COUPON_SUCCESS"),$count),1);
			$this->success(sprintf(L("IMPORT_COUPON_SUCCESS"),$count));
		}else{
			$this->error(L("COUPON_FILE_REQUIRE"));
		}
		
	}
	
	
	
	public function sms()
	{
		if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_COUPON")==1)
		{
			$id = intval($_REQUEST['id']);
			send_deal_coupon_sms($id);
			save_log("ID:".$id.L("SEND_COUPON_SMS_SUCCESS"),1);
			$this->success(L("SEND_COUPON_SMS_SUCCESS"));
		}
		else
		{
			$this->error(L("SEND_COUPON_SMS_FAILED"));
		}
	}
	
	public function mail()
	{
		
		if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
		{
			$id = intval($_REQUEST['id']);
			send_deal_coupon_mail($id);
			save_log("ID:".$id.L("SEND_COUPON_MAIL_SUCCESS"),1);
			$this->success(L("SEND_COUPON_MAIL_SUCCESS"));
		}
		else
		{
			$this->error(L("SEND_COUPON_MAIL_FAILED"));
		}
	}
	
	
	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$deal_id = intval($_REQUEST['deal_id']);
		$deal_info = M("Deal")->getById($deal_id);
		if(!$deal_info)
		{
			$this->error(l("DEAL_NOT_EXIST"));
		}
		if($deal_info['is_coupon']==0)
		{
			$this->error(l("DEAL_NO_COUPON"));
		}
		
		
		//处理-1情况的select
		if(!isset($_REQUEST['is_valid']))
		{
			$_REQUEST['is_valid'] = -1;
		}
		if(!isset($_REQUEST['is_confirm']))
		{
			$_REQUEST['is_confirm'] = -1;
		}
		
		//定义条件
		$map[DB_PREFIX.'deal_coupon.is_delete'] = 0;
		$map[DB_PREFIX.'deal_coupon.deal_id'] = $deal_id;
		if(trim($_REQUEST['sn'])!='')
		{
			$map[DB_PREFIX.'deal_coupon.sn'] = array('like','%'.trim($_REQUEST['sn']).'%');
		}
		if(trim($_REQUEST['user_id'])!='')
		{
			$map[DB_PREFIX.'deal_coupon.user_id'] = intval(trim($_REQUEST['user_id']));
		}
		if(intval($_REQUEST['is_valid'])>=0)
		{
			$map[DB_PREFIX.'deal_coupon.is_valid'] = intval($_REQUEST['is_valid']);
		}
		if(intval($_REQUEST['is_confirm'])>=0)
		{
			if(intval($_REQUEST['is_confirm'])==0)
			$map[DB_PREFIX.'deal_coupon.confirm_time'] = 0;
			else
			$map[DB_PREFIX.'deal_coupon.confirm_time'] = array('gt',0);
		}
		
		$list = M(MODULE_NAME)
				->where($map)
				->join(DB_PREFIX.'user ON '.DB_PREFIX.'user.id = '.DB_PREFIX.'deal_coupon.user_id')
				->join(DB_PREFIX.'deal_order ON '.DB_PREFIX.'deal_order.id = '.DB_PREFIX.'deal_coupon.order_id')
				->join(DB_PREFIX.'deal_order_item ON '.DB_PREFIX.'deal_order_item.id = '.DB_PREFIX.'deal_coupon.order_deal_id')
				->field(DB_PREFIX.'deal_coupon.*,'.DB_PREFIX.'user.user_name,'.DB_PREFIX.'user.mobile,'.DB_PREFIX.'deal_order.order_sn,'.DB_PREFIX.'deal_order_item.name,'.DB_PREFIX.'deal_order_item.number')
				->limit($limit)->findAll();


		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
			$coupon_value = array('id'=>'""', 'sn'=>'""', 'password'=>'""','deal_id'=>'""',  'order_deal_id'=>'""','number'=>'""','order_id'=>'""','user_id'=>'""','mobile'=>'""','begin_time'=>'""','end_time'=>'""','confirm_time'=>'""');
			if($page == 1)
	    	$content = iconv("utf-8","gbk","编号,序列号,密码,团购编号,购买的团购,购买数量,订单号,会员名称,会员手机号,生效时间,过期时间,使用时间" . "\n");
	    	
	    	foreach($list as $k=>$v)
			{
				$mobile = '';
				$mobile = $v['mobile'];
				//$pattern = "/(\d{4})(\d{3})(\d{4})/";
				//$replacement = "\$1***\$3";
				//$v['mobile'] = preg_replace($pattern, $replacement, $v['mobile']);
				
				$coupon_value['id'] = iconv('utf-8','gbk','"' . $v['id'] . '"');
				$coupon_value['sn'] = iconv('utf-8','gbk','"' . $v['sn'] . '"');
				$coupon_value['password'] = iconv('utf-8','gbk','"' . $v['password'] . '"');
				$coupon_value['deal_id'] = iconv('utf-8','gbk','"' . $v['deal_id'] . '"');
				$coupon_value['order_deal_id'] = iconv('utf-8','gbk','"' . $v['name'] . '"');
				$coupon_value['number'] = iconv('utf-8','gbk','"' . $v['number'] . '"');
				$coupon_value['order_id'] = iconv('utf-8','gbk','"' . $v['order_sn'] . '"');
				$coupon_value['user_id'] = iconv('utf-8','gbk','"' . $v['user_name'] . '"');
				$coupon_value['mobile'] = iconv('utf-8','gbk','"' . $v['mobile'] . '"');
				$coupon_value['begin_time'] = iconv('utf-8','gbk','"' . to_date($v['begin_time']) . '"');
				$coupon_value['end_time'] = iconv('utf-8','gbk','"' . to_date($v['end_time']) . '"');
				$coupon_value['confirm_time'] = iconv('utf-8','gbk','"' . to_date($v['confirm_time']) . '"');

			
				$content .= implode(",", $coupon_value) . "\n";
			}	
			
			
			header("Content-Disposition: attachment; filename=coupon_list.csv");
	    	echo $content;  
		}
		else
		{
			if($page==1)
			$this->error(L("NO_RESULT"));
		}
		
	}
	
	public function refund()
	{
		$id = intval($_REQUEST['id']);
		$rs = refund_coupon($id);
		if($rs>0)
			$this->success("退款成功");
		else
			$this->error("非法操作");
	}
	
	public function batch_refund()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
			$ids = explode ( ',', $id );
			$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
			$rel_data = M(MODULE_NAME)->where($condition)->findAll();
			foreach($rel_data as $data)
			{
				$info[] = $data['sn'];
			}
			if($info) $info = implode(",",$info);
			
			foreach($ids as $cid)
			{
				$rs = refund_coupon($cid);
			}
			
			save_log($info."批量退款",1);
			$this->success ("批量退款成功",$ajax);
		} else {
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}

}
?>