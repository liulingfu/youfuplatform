<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo conf("APP_NAME");?><?php echo l("ADMIN_PLATFORM");?></title>
<script type="text/javascript" src="__ROOT__/public/runtime/admin/lang.js"></script>
<script type="text/javascript">
	var version = '<?php echo app_conf("DB_VERSION");?>';
</script>
<link rel="stylesheet" type="text/css" href="__TMPL__Common/style/style.css" />
<link rel="stylesheet" type="text/css" href="__TMPL__Common/style/main.css" />
<script type="text/javascript" src="__TMPL__Common/js/jquery.js"></script>
</head>

<body>
	<div class="main">
	<div class="main_title"><?php echo conf("APP_NAME");?><?php echo l("ADMIN_PLATFORM");?> <?php echo L("HOME");?>	</div>
	<div class="blank5"></div>
	<table class="form" cellpadding=0 cellspacing=0>
		<tr>
			<td colspan=2 class="topTd"></td>
		</tr>
		<tr>
			<td class="item_title" style="width:200px;">
				<?php echo L("CURRENT_VERSION");?>
			</td>
			<td class="item_input">
				<?php echo L("APP_VERSION");?>:<?php echo conf("DB_VERSION");?><?php if(app_conf("APP_SUB_VER")){ ?>.<?php echo app_conf("APP_SUB_VER");?><?php } ?> <span id="version_tip"></span>
			</td>
		</tr>
		
		<tr>
			<td class="item_title" style="width:200px;">
				<?php echo L("TIME_INFORMATION");?>
			</td>
			<td class="item_input">
				<?php echo L("CURRENT_TIME");?>：<?php echo to_date(get_gmtime()); ?>
			</td>
		</tr>
		<tr>
			<td class="item_title" style="width:200px;">
				<?php echo L("TOTAL_REG_USER_COUNT");?>
			</td>
			<td class="item_input">				
				<?php echo sprintf(L("TOTAL_USER_COUNT_FORMAT"),$total_user,$total_verify_user); ?>
			</td>
		</tr>
		<tr>
			<td class="item_title" style="width:200px;">
				<?php echo L("CURRENT_DEALING");?>
			</td>
			<td class="item_input">				
				<?php echo sprintf(L("CURRENT_DEALING_FORMAT"),$goods_count,$deal_count,$score_count); ?>
			</td>
		</tr>	
		
		<tr>
			<td class="item_title" style="width:200px;">
				上线优惠
			</td>
			<td class="item_input">
				代金券<?php echo ($daijin_count); ?>，
				优惠券<?php echo ($youhui_count); ?>，
				活动<?php echo ($event_count); ?>
			</td>
		</tr>
		
		<tr>
			<td class="item_title" style="width:200px;">
				会员发表
			</td>
			<td class="item_input">
				分享<?php echo ($topic_count); ?><?php if($reminder['topic_count'] > 0): ?>(<a href="<?php echo u("Topic/index");?>" style="color:#f60;"><?php echo ($reminder["topic_count"]); ?>新</a>)<?php endif; ?>，商家点评<?php echo ($dp_count); ?><?php if($reminder['dp_count'] > 0): ?>(<a href="<?php echo u("SupplierLocation/index");?>" style="color:#f60;"><?php echo ($reminder["dp_count"]); ?>新</a>)<?php endif; ?>，会员留言<?php echo ($msg_count); ?><?php if($reminder['msg_count'] > 0): ?>(<a href="<?php echo u("Message/index",array("is_buy"=>0));?>" style="color:#f60;"><?php echo ($reminder["msg_count"]); ?>新</a>)<?php endif; ?>，购物点评<?php echo ($buy_msg_count); ?><?php if($reminder['buy_msg_count'] > 0): ?>(<a href="<?php echo u("Message/index",array("is_buy"=>1));?>" style="color:#f60;"><?php echo ($reminder["buy_msg_count"]); ?>新</a>)<?php endif; ?>
			</td>
		</tr>
		
		<tr>
			<td class="item_title" style="width:200px;">
				订单统计
			</td>
			<td class="item_input">
				订单总数<?php echo ($order_count); ?>，商品成交<?php echo ($order_buy_count); ?>，充值成交<?php echo ($incharge_order_buy_count); ?>
				<?php if($reminder['order_count'] > 0): ?>(<a href="<?php echo u("DealOrder/deal_index");?>" style="color:#f60;"><?php echo ($reminder["order_count"]); ?>新订单</a>)<?php endif; ?>
				<?php if($reminder['incharge_count'] > 0): ?>(<a href="<?php echo u("DealOrder/incharge_index");?>" style="color:#f60;"><?php echo ($reminder["incharge_count"]); ?>新充值单</a>)<?php endif; ?>
				<?php if($reminder['refund_count'] > 0): ?>(<a href="<?php echo u("DealOrder/deal_index",array("refund_status"=>1));?>" style="color:#f60;"><?php echo ($reminder["refund_count"]); ?>新退款申请</a>)<?php endif; ?>
				<?php if($reminder['retake_count'] > 0): ?>(<a href="<?php echo u("DealOrder/deal_index",array("retake_status"=>1));?>" style="color:#f60;"><?php echo ($reminder["retake_count"]); ?>新退货申请</a>)<?php endif; ?>
			</td>
		</tr>
		
		<tr>
			<td class="item_title" style="width:200px;">
				资金
			</td>
			<td class="item_input">
				总收款<?php echo (format_price($income_amount)); ?>，退款<?php echo (format_price($refund_amount)); ?>
			</td>
		</tr>
		
		<tr>
			<td class="item_title" style="width:200px;">
				<?php echo L("GET_MORE_INFO");?>
			</td>
			<td class="item_input">
				请访问 <a href="http://www.ynztc.com" target="_blank" title="优辅平台O2O商业系统">http://www.ynztc.com 优辅平台O2O商业系统</a>
			</td>
		</tr>
		<tr>
			<td colspan=2 class="bottomTd"></td>
		</tr>
	</table>	
	</div>
	
</body>
</html>