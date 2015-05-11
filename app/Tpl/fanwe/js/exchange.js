$(document).ready(function(){
	$("#amountdesc").bind("blur",function(){init_exchange_form();});
	$("#key").bind("change",function(){init_exchange_form();});
	init_exchange_form();
	
	$("#doexchange").bind("click",function(){
		var amountsrc = $("#amountsrc").val();  //所需的值
		var amountdesc = $("#amountdesc").val();
		var titledesc = $("#key").find("option:selected").attr("rel");
		var titlesrc = $("#titlesrc").html();
		if(isNaN(amountdesc)||parseInt(amountdesc)<=0)
		{
			amountdesc = 1;
		}
		else
		{
			amountdesc = Math.floor(amountdesc);
		}
		var key = $("#key").val();
		if(isNaN(amountsrc)||amountsrc<=0)
		{
			$.showErr("兑换所消耗的"+titlesrc+"不能为0，请兑换更多的"+titledesc, function(){
				$("#amountdesc").focus();				
			});
			return;
		}
		
		if(confirm("确定使用["+amountsrc+"]"+titlesrc+"兑换["+amountdesc+"]"+titledesc+"吗？"))
		{
			//开始ajax请求
			var query = new Object();
			query.password = $("#user_pwd").val();
			query.key = key;
			query.amountdesc = amountdesc;
			
			var ajaxurl = APP_ROOT+"/shop.php?ctl=uc_money&act=doexchange";
			 $.ajax({ 
					url: ajaxurl,
					dataType: "json",
					data:query,
					type:"post",
					success: function(obj){
						if(obj.status)
						{
							$.showSuccess("兑换成功",function(){
								$("#user_pwd").val("");
								$("#amountdesc").val("1");
								init_exchange_form();
								location.reload();
							});
						}
						else
						{
							$.showErr(obj.message);
						}
					},
					error:function(ajaxobj)
					{
						if(ajaxobj.responseText!='')
						alert(ajaxobj.responseText);
					}
				});	
			//end
			
		}
	});
});
function init_exchange_form()
{
	var amountdesc = $("#amountdesc").val();
	if(isNaN(amountdesc)||parseInt(amountdesc)<=0)
	{
		amountdesc = 1;
		$("#amountdesc").val("1");
	}
	else
	{
		amountdesc = Math.floor(amountdesc);
		$("#amountdesc").val(amountdesc);
	}
	var key = $("#key").val();
	var titlesrc = exchange_json_data[key]['srctitle'];  //所需的标题
	var amountsrc = Math.floor(amountdesc * exchange_json_data[key]['ratio']);
	$("#amountsrc").val(amountsrc);
	$("#titlesrc").html(titlesrc);
}

/*

$(document).ready(function(){
	$(".dest_box").bind("blur",function(){
		 var key =  $(this).attr("rel");
		 var amount = $(this).val();
		 if(isNaN(amount)||amount<0)
		 {
			 amount = 0;
		 }
		 
		 var src_amount = Math.floor(amount / data_json[key]['ratiodesc'] * data_json[key]['ratiosrc']);
		 $("#row_"+key).val(src_amount);		 
	});
	
	$("#exchange").bind("click",function(){
		 var query = $("#exchange_form").serialize();
		 var ajaxurl = $("#exchange_form").attr("action");

		 
		 $.ajax({ 
				url: ajaxurl,
				dataType: "json",
				data:query,
				type:"post",
				success: function(obj){
					if(obj.status)
					{
						$.showSuccess("兑换成功",function(){
							$(".dest_box").val("0");
							$(".src_val").val("0");
						});
					}
					else
					{
						$.showErr(obj.message);
					}
				},
				error:function(ajaxobj)
				{
					if(ajaxobj.responseText!='')
					alert(ajaxobj.responseText);
				}
			});	
		 
	});
});*/