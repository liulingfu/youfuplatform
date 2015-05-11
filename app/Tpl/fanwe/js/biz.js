$(document).ready(function(){
	$("form[name='supplier_login']").bind("submit",function(){
		var account_name = $(this).find("input[name='account_name']").val();
		var account_password = $(this).find("input[name='account_password']").val();
		if($.trim(account_name)=='')
		{
			$.showErr("请输入商家帐号");
			$(this).find("input[name='account_name']").focus();
			return false;
		}
		
		if($.trim(account_password)=='')
		{
			$.showErr("请输入商家密码");
			$(this).find("input[name='account_password']").focus();
			return false;
		}
		
		var query = $(this).serialize();
		var ajaxurl = APP_ROOT+"/biz.php?ctl=login&act=dologin";
		$.ajax({ 
			url: ajaxurl,
			type: "POST",
			dataType: "json",
			data:query,
			success: function(ajaxobj){
				if(ajaxobj.status==1)
				{
					location.reload();
				}
				else
				{
					$.showErr(ajaxobj.msg);
				}
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
		
		return false;
	});
});



function use_coupon()
{
	var coupon_sn = $.trim($("#coupon_sn").val());
	var coupon_pwd = $.trim($("#coupon_pwd").val());
	var ajaxurl = APP_ROOT+"/biz.php?ctl=verify&act=check_coupon&coupon_sn="+coupon_sn+"&coupon_pwd="+coupon_pwd;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status==2)//未登录
			{
				$.showErr("请先登录");
			}
			if(obj.status == 0)
			{
				//确认失败
				$.showErr(obj.msg);
			}
			if(obj.status == 1)
			{
				//确认成功
				if(confirm(coupon_sn+" ["+obj.msg+"] 是有效的消费券,是否立即使用？"))
				{
					var ajaxurl = APP_ROOT+"/biz.php?ctl=verify&act=use_coupon&coupon_sn="+coupon_sn+"&coupon_pwd="+coupon_pwd;
					$.ajax({ 
						url: ajaxurl,
						dataType: "json",
						success: function(result){
							if(result.status)
							{
								$.showSuccess(result.msg);
							}
							else
								$.showErr(result.info);
						},
						error:function(ajaxobj)
						{
//							if(ajaxobj.responseText!='')
//							alert(ajaxobj.responseText);
						}
					});	
				}
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}



//验证优惠券
function use_youhui()
{
	
	var youhui_sn = $.trim($("#youhui_sn").val());
	var total_fee=$.trim($("#youhui_fee").val());
	
	var ajaxurl = APP_ROOT+"/biz.php?ctl=verify&act=check_youhui&youhui_sn="+youhui_sn;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status)
			{
				if(confirm(obj.info))
				{
					var ajaxurl = APP_ROOT+"/biz.php?ctl=verify&act=use_youhui&youhui_sn="+obj.data.youhui_sn+"&total_fee="+total_fee;
					$.ajax({ 
						url: ajaxurl,
						dataType: "json",
						success: function(result){
							if(result.status)
							{
								$.showSuccess(result.data.youhui_data.name+"[序列号"+result.data.youhui_sn+"]消费使用成功");
							}
							else
							$.showErr(result.info);
						},
						error:function(ajaxobj)
						{
//							if(ajaxobj.responseText!='')
//							alert(ajaxobj.responseText);
						}
					});	
				}
			}
			else
			$.showErr(obj.info);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}


function load_deal_cate_type()
{
	var cate_id = $("#cate_id").val();
	var deal_id = $("#deal_id").val();
	var youhui_id = $("#youhui_id").val();

	var ajaxurl = APP_ROOT+"/biz.php?ctl=tuan&act=load_deal_cate_type&cate_id="+cate_id+"&deal_id="+deal_id+"&youhui_id="+youhui_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(result){
			if(result.status)
			{
				$("#deal_cate_type_row").find("span").html(result.html);
				$("#deal_cate_type_row").show();
			}
			else
			{
				$("#deal_cate_type_row").find("span").html("");
				$("#deal_cate_type_row").hide();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}



function add_submit_row(event_id)
{
	var ajaxurl = APP_ROOT+"/biz.php?ctl=event&act=add_submit_item";
	$.ajax({ 
		url: ajaxurl,
		success: function(obj){
			$("#submit_row").append(obj);
		}
	});	
}
function remove_row(obj)
{
	$(obj).parent().remove();
}
function change_type(obj)
{
	if($(obj).val()>0)
	{
		$(obj).parent().find("span").show();
	}
	else
	{
		$(obj).parent().find("span").hide();
	}
}