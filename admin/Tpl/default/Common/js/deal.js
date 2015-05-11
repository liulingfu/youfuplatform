function init_dealform()
{
	$("select[name='is_refund']").bind("change",function(){
		init_refund();
	});

	$("input[name='supplier_key_btn']").bind("click",function(){
		search_deal_supplier();
	});
	
	$("input[name='any_refund']").bind("click",function(){
		init_cart_type();
	});
	$("input[name='expire_refund']").bind("click",function(){
		init_cart_type();
	});
	init_cart_type();
	
	$("form").bind("submit",function(){
		var attr_select = $(".attr_select_box");
		if(attr_select.length>0)
		{
			for(i=0;i<attr_select.length;i++)
			{
				if($(attr_select[i]).val()=='')
				{
					alert(LANG['ATTR_SETTING_EMPTY']);
					return false;
				}
			}
		}		
		
	});
	//绑定副标题20个字数的限制
	$("input[name='sub_name']").bind("keyup change",function(){
		if($(this).val().length>20)
		{
			$(this).val($(this).val().substr(0,20));
		}		
	});
	
	//绑定团购券时间行显示
	$("select[name='is_coupon']").bind("change",function(){
		load_coupon_time();
		init_refund();
	});
	init_refund();
	$("select[name='cate_id']").bind("change",function(){
		init_sub_cate();
	});
	init_sub_cate();
	
	$("select[name='supplier_id']").bind("change",function(){
		init_supplier_location();
	});
	init_supplier_location();
	
	//绑定团购商品类型，显示属性
	$("select[name='deal_goods_type']").bind("change",function(){
		load_attr_html();
	});
	
	//绑定配送行的显示
	$("select[name='is_delivery']").bind("change",function(){
		load_weight();
	});
	
	 $("select[name='buy_type']").bind("change",function(){
	 	switch_buy_type();
	 });
	 
	 $("select[name='free_delivery']").bind("change",function(){
		 load_free_delivery();
	 });
	 $("select[name='define_payment']").bind("change",function(){
		 load_payment_box();
	 });
	 $("select[name='shop_cate_id']").bind("change",function(){
		 load_filter_box();
	 });
	 
	switch_buy_type();
	load_attr_html();
	load_coupon_time();
	load_weight();
	load_free_delivery();
	load_payment_box();
	load_filter_box();

}

function init_cart_type()
{
	/*
	if($("input[name='any_refund']:checked").length>0||$("input[name='expire_refund']:checked").length>0)
	{
		$("select[name='cart_type']").parent().parent().hide();
		$("select[name='allow_promote']").parent().parent().hide();
	}
	else
	{
		$("select[name='cart_type']").parent().parent().show();
		$("select[name='allow_promote']").parent().parent().show();
	}
	*/
}
function init_refund()
{
	if($("select[name='is_coupon']").length>0)
	{
		var is_refund = $("select[name='is_refund']").val();
		var is_coupon = $("select[name='is_coupon']").val();
		if(is_coupon==1&&is_refund==1)
		{
			$("#coupon_refund").show();
		}
		else
		{
			$("#coupon_refund").hide();
		}
		
	}	
}
function load_payment_box()
{
	var define_payment = $("select[name='define_payment']").val();
	if(define_payment==1)
	{
		$(".define_payment").show();
	}
	else
	{
		$(".define_payment").hide();
	}	
}
function load_free_delivery()
{
	var free_delivery = $("select[name='free_delivery']").val();
	if(free_delivery==1)
	{
		$(".free_delivery").show();
	}
	else
	{
		$(".free_delivery").hide();
	}
}

//积分兑换和普通购买的切换
function switch_buy_type()
{
	var buy_type = $("select[name='buy_type']").val();
	if(buy_type==1)
	{
		$("select[name='define_payment']").val(0);
		$(".buy_type_0").find(".textbox").val("");
		$(".buy_type_0").hide();
		
	}
	else
	{
		if(buy_type==2)
		{
			$("#price_title").html(LANG['DEAL_ORDER_PRICE']);
		}
		if(buy_type==3)
		{
			$("#price_title").html(LANG['DEAL_SECOND_PRICE']);
		}
		if(buy_type==0)
		{
			$("#price_title").html(LANG['DEAL_CURRENT_PRICE']);
		}
		$(".buy_type_0").show();
	}
}

function load_attr_html()
{
		deal_goods_type = $("select[name='deal_goods_type']").val();
		deal_id = $("input[name='id']").val();
		if(deal_goods_type>0)
		{
			$("#deal_attr_row").show();
			$.ajax({ 
				url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=attr_html&deal_goods_type="+deal_goods_type+"&deal_id="+deal_id, 
				data: "ajax=1",
				success: function(obj){
					$("#deal_attr").html(obj);
				}
			});
		}
		else
		{
			$("#deal_attr_row").hide();
			$("#deal_attr").html("");
		}
}

function load_coupon_time()
{
		if($("select[name='is_coupon']").val()==0)
		{
			$(".coupon_time").hide();
		}
		else
		{
			$(".coupon_time").show();
		}
}

function load_weight()
{
		if($("select[name='is_delivery']").val()==0)
		{
			$(".weight_row").hide();
			$(".free_delivery").hide();
			$("select[name='free_delivery']").val(0);
		}
		else
		{
			$(".weight_row").show();
		}
}

//加载属性库存表
function load_attr_stock(obj)
{
	if(obj)
	{
		 attr_cfg_json = '';
		 attr_stock_json = '';
	}
	

	if($(".deal_attr_stock:checked").length>0)
	{
			$(".max_bought_row").find("input[name='max_bought']").val("");
			$(".max_bought_row").hide();
	}
	else
	{
			$(".max_bought_row").show();
	}
	//初始化deal_attr_stock_hd
	var deal_attr_stock_box = $(".deal_attr_stock");
	for(i=0;i<deal_attr_stock_box.length;i++)
	{
		var v = $(deal_attr_stock_box[i]).attr("checked")?1:0;
		$(deal_attr_stock_box[i]).parent().find(".deal_attr_stock_hd").val(v);
	}
	var box = $(".deal_attr_stock:checked");
	if(!box.length>0)
	{
		$("#stock_table").html("");
		return;
	}
	
	var x = 1; //行数
	var y = 0; //列数
	var attr_id = 0;
	var attr_item_count = 0; //每组属性的个数
	var attr_arr = new Array();
	for(i=0;i<box.length;i++)
	{
		if($(box[i]).attr("rel")!=attr_id)
		{
			y++;
			attr_id = $(box[i]).attr("rel");
			attr_arr.push(attr_id);
		}
		else
		{
			attr_item_count++;
		}
	}

	//开始计算行数
	for(i=0;i<attr_arr.length;i++)
	{
		x = x * parseInt($("input[name='deal_attr_stock["+attr_arr[i]+"][]']:checked").length);
	}	
	var html = "<table width='100%' style='border-left: solid #ccc 1px; border-top: solid #ccc 1px;'>";	
	html += "<tr>";
	for(j=0;j<attr_arr.length;j++)
	{
		html+="<th>"+$("#title_"+attr_arr[j]).html()+"</th>";
	}
	html+="<th>"+LANG['DEAL_MAX_BOUGHT_TIP']+"</th>";
	html +="</tr>";
	
	for(i=0;i<x;i++)
	{
		html += "<tr>";
		for(j=0;j<attr_arr.length;j++)
		{
			html+="<td><select name='stock_attr["+attr_arr[j]+"][]' class='attr_select_box' onchange='check_same(this);'><option value=''>"+LANG['EMPTY_SELECT']+"</option>";
			
			//开始获取相应的选取值
			var cbo = $("input[name='deal_attr_stock["+attr_arr[j]+"][]']:checked");
			for(k=0;k<cbo.length;k++)
			{
				var cnt = $(cbo[k]).parent().find("*[name='deal_attr["+attr_arr[j]+"][]']").val();				
				html =  html + "<option value='"+cnt+"'";
				if(attr_cfg_json!=''&&attr_cfg_json[i][attr_arr[j]]==cnt)
				html = html + " selected='selected' ";
				html = html + ">"+cnt+"</option>";
			}
			
			html+="</select></td>";
		}
		html+="<td><input type='text' class='textbox' style='width: 50px;' name='stock_cfg_num[]' value='";
		if(attr_stock_json!='')
		html = html + attr_stock_json[i]['stock_cfg'];		
		html=html+"' /> <input type='hidden' name='stock_cfg[]' value='";
		if(attr_stock_json!='')
		html+=attr_stock_json[i]['attr_str'];
		html+="' /> </td>";
		html +="</tr>";
	}	
	html += "</table>";
	$("#stock_table").html(html);
}

//检测当前行的配置
function check_same(obj)
{
	var selectbox = $(obj).parent().parent().find("select");
	var row_value = '';
	for(i=0;i<selectbox.length;i++)
	{
		if($(selectbox[i]).val()!='')
			row_value += $(selectbox[i]).val();
		else
		{
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			return;
		}
	}
	//开始检测是否存在该配置
	var stock_cfg = $("input[name='stock_cfg[]']");
	for(i=0;i<stock_cfg.length;i++)
	{
		if(row_value==$(stock_cfg[i]).val()&&row_value!=''&&stock_cfg[i]!=obj)
		{
			alert(LANG['SPEC_EXIST']);
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			$(obj).val("");
			return;
		}
	}
	$(obj).parent().parent().find("input[name='stock_cfg[]']").val(row_value);
}

function sw_shop_cate()
{
	if($("input[name='show_shop_cate']").attr("checked"))
	{
		$("select[name='shop_cate_id']").show();
	}
	else
	{
		$("select[name='shop_cate_id']").val("0");
		$("select[name='shop_cate_id']").hide();
	}
}

function load_filter_box()
{
	var cate_id = $("select[name='shop_cate_id']").val();
	var deal_id = $("input[name='id']").val();
	var buy_type = $("select[name='buy_type']").val();
	if(cate_id>0&&buy_type==0)
	{
		$("#filter_row").show();
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=filter_html&shop_cate_id="+cate_id+"&deal_id="+deal_id, 
			data: "ajax=1",
			success: function(obj){
				$("#filter").html(obj);
			}
		});
	}
	else
	{
		$("#filter_row").hide();
		$("#filter").html("");
	}
	
}

function init_sub_cate()
{
	var cate_id = $("select[name='cate_id']").val();
	var deal_id = $("input[name='id']").val();
	
	if(cate_id>0)
	{
		
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_sub_cate&cate_id="+cate_id+"&deal_id="+deal_id, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				if(obj.status)
				{
					$("#sub_cate_box").show();
					$("#sub_cate_box").find(".item_input").html(obj.data);
				}
				else
				{
					$("#sub_cate_box").hide();
				}
				
			},
			error:function(ajaxobj)
			{
				if(ajaxobj.responseText!='')
				alert(ajaxobj.responseText);
			}
		
		});
	}
	else
	{
		$("#sub_cate_box").hide();
		$("#sub_cate_box").find(".item_input").html("");
	}
}


function init_supplier_location()
{
	var supplier_id = $("select[name='supplier_id']").val();
	var deal_id = $("input[name='id']").val();	
	if(supplier_id>0)
	{		
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_supplier_location&supplier_id="+supplier_id+"&deal_id="+deal_id, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				if(obj.status)
				{
					$("#supplier_location").show();
					$("#supplier_location").find(".item_input").html(obj.data);
				}
				else
				{
					$("#supplier_location").hide();
				}
				
			},
			error:function(ajaxobj)
			{
				if(ajaxobj.responseText!='')
				alert(ajaxobj.responseText);
			}
		
		});
	}
	else
	{
		$("#supplier_location").hide();
		$("#supplier_location").find(".item_input").html("");
	}
}


function search_deal_supplier()
{
	var key = $("input[name='supplier_key']").val();
	if($.trim(key)=='')
	{
		alert(INPUT_KEY_PLEASE);
	}
	else
	{
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"=SupplierLocation&"+VAR_ACTION+"=search_supplier", 
			data: "ajax=1&key="+key,
			type: "POST",
			success: function(obj){
				$("#supplier_list").html(obj);
				$("select[name='supplier_id']").bind("change",function(){
					init_supplier_location();
				});
			}
		});
	}
}