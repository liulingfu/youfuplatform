$(document).ready(function(){
	$("#vote").css("top",$(document).scrollTop()+200);	
	$("img").one("error",function(){
		$(this).attr("src",ERROR_IMG);
	});
	$.each($("img"),function(i,n){
		if($(n).attr("src")=='')
			$(n).attr("src",ERROR_IMG);
	});
	$(".lazy,.lazy img").lazyload({ 
		placeholder : LOADER_IMG,
		threshold : 0,
		event:"scroll",
		effect: "fadeIn",
		failurelimit : 10
	});

	//$(".header,#ftw").pngFix();
	//绑定页面滚动事件
	$(window).scroll(function(){
		$("#vote").css("top",$(document).scrollTop()+200);	
	});		
	
	init_gotop();
	
	$('#submit-mail-image,#tip-submit-deal-mail').click(function(){	
		submit_mail($(this));
	});
	
	//绑定抽奖的修改绑定按钮
	$("#modify_bind").bind("click",function(){
		$(this).hide();
		$("#lottery_mobile_input").show();		
		$("#lottery_mobile_word").hide();
	});
	//绑定友情链接计数
	$(".flink").find("a").bind("click",function(){
		var ajaxurl = APP_ROOT+"/shop.php?ctl=link&act=go&url="+$(this).attr("href");
		$.ajax({ 
			url: ajaxurl,
			success: function(html){
				
			}
		});			
	});
	
	$('#verify_ecv').bind("click",function(){
		var ecvsn = $(this).parent().find("input[name='ecvsn']").val();
		var ecvpassword = $(this).parent().find("input[name='ecvpassword']").val();
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=verify_ecv&ecvsn="+ecvsn+"&ecvpassword="+ecvpassword;
		$.ajax({ 
			url: ajaxurl,
			success: function(text){
			$.showSuccess(text);
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});
	});
	
	//关于订单购物车提交按钮的事件
	$("#order_done").click(function(){
		submit_buy();
	});
	
	$('#share-copy-button').click(function(){
		$.copyText('#share-copy-text');
	});	
	
	//加载主导航的焦点取消
	$(".main_nav").find("a").bind("focus",function(){
		$(this).blur();
	});
	
	//初始化分类引出
	$(".first").hover(function(){
		var sub_cate = $(this).parent().find(".subcate");
		if(sub_cate.length>0)
		{
			$(this).addClass("act");			
			var html = "<ul class='toggle'>";
			for(i=0;i<sub_cate.length;i++)
			{				
				html += "<li style='text-indent:"+$(sub_cate[i]).css("text-indent")+";'>";
				if(i==0)
					html+="<div class='hide_white'></div>";
				html += $(sub_cate[i]).html()+"</li>";
			}
			html+="</ul>";
		
			$(this).append(html);
			$(".toggle").hover(function(){
				
			},function(){
				$(this).parent().removeClass("act");
				$(".toggle").remove();
			});
		}
	}, function(){
		$(this).removeClass("act");
		$(".toggle").remove();
	});
	submit_message();
	$("#header_search_box").bind("submit",function(){
		if($("#header_kw").val()=='')
		{
			$.showErr(LANG['INPUT_KW_PLEASE']);
			$("#header_kw").focus();
			return false;
		}
	});
	$("#search_btn").bind("click",function(){
		if($.trim($("#header_kw").val())==''||$.trim($("#header_kw").val())==LANG['HEAD_KEYWORD_EMPTY_TIP'])
		{
			$.showErr(LANG['HEAD_KEYWORD_EMPTY_TIP'],function(){
				$("#header_kw").focus();
			});			
		}
		else
		$("#header_search_box").submit();
	});
	if($.trim($("#header_kw").val())==''||$.trim($("#header_kw").val())==LANG['HEAD_KEYWORD_EMPTY_TIP'])
	{
		$("#header_kw").css("color","#bbbbbb");
	}
	else
	{
		$("#header_kw").css("color","#404040");
	}
	$("#header_kw").bind("focus",function(){
		$(this).css("color","#404040");
		if($.trim($("#header_kw").val())==''||$.trim($("#header_kw").val())==LANG['HEAD_KEYWORD_EMPTY_TIP'])
		{
			$("#header_kw").val("");
		}		
	});
	$("#header_kw").bind("blur",function(){		
		if($.trim($("#header_kw").val())==''||$.trim($("#header_kw").val())==LANG['HEAD_KEYWORD_EMPTY_TIP'])
		{
			$("#header_kw").val(LANG['HEAD_KEYWORD_EMPTY_TIP']);
			$(this).css("color","#bbbbbb");
		}		
	});
	$("#select_search_type").bind("click",function(){
		var left = $(this).offset().left;
		var top = $(this).offset().top;
		$("#dropdown").css("left",left-2);
		$("#dropdown").css("top",top+26);
		$("#dropdown").show();
	});
	$("#dropdown").find("a").bind("click",function(){		
		$("#dropdown").hide();				
		$("#select_search_type").html($(this).html());
		$("#search_ctl").val($(this).attr("ctl"));
		$("#search_act").val($(this).attr("act"));
		$("#header_search_box").attr("action",$(this).attr("action"));
	});
	$(document.body).click(function(e) {
		if($(e.target).attr("id")!='select_search_type')
    	{
    		$("#dropdown").hide();
    	}
		
		if($(e.target).attr("id")!='bcate_box')
    	{
    		$("#bcate_box_drop_down").hide();
    	}
		if($(e.target).attr("id")!='scate_box')
    	{
    		$("#scate_box_drop_down").hide();
    	}
		if($(e.target).attr("id")!='area_box')
    	{
    		$("#area_box_drop_down").hide();
    	}
    });
	init_index_store();

});






//用于未来扩展的提示正确错误的JS
$.showErr = function(str,func)
{
	$.weeboxs.open(str, {boxid:'fanwe_error_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'错误',width:250,type:'wee',onclose:func});
};

$.showSuccess = function(str,func)
{
	$.weeboxs.open(str, {boxid:'fanwe_success_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'提示',width:250,type:'wee',onclose:func});
};

/*验证*/
$.minLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength >= length;
};

$.maxLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength <= length;
};
$.getStringLength=function(str)
{
	str = $.trim(str);
	
	if(str=="")
		return 0; 
		
	var length=0; 
	for(var i=0;i <str.length;i++) 
	{ 
		if(str.charCodeAt(i)>255)
			length+=2; 
		else
			length++; 
	}
	
	return length;
};

$.checkMobilePhone = function(value){
	if($.trim(value)!='')
		return /^\d{6,}$/i.test($.trim(value));
	else
		return true;
};
$.checkEmail = function(val){
	var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/; 
	return reg.test(val);
};


function formSuccess(obj,msg)
{
	if(msg!='')
	$(obj).parent().find(".f-input-tip").html("<span class='form_success'>"+msg+"</span>");
	else
	$(obj).parent().find(".f-input-tip").html("");
}
function formError(obj,msg)
{
	$(obj).parent().find(".f-input-tip").html("<span class='form_err'>"+msg+"</span>");
}


//修改购物车
function modify_cart(id,htmlobj)
{
	var number = $(htmlobj).val();
	var ajaxurl = APP_ROOT+"/shop.php?ctl=cart&act=modifycart&id="+id+"&number="+number;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				$("#cart_list").html(obj.html);
			}
			else
			{
				var str = obj.info.split("|");
				var msg = str[0];
				$.showErr(msg);
				$(".deal_cart_row").removeClass("cart_warn");
				if(str[2])
					$("tr[rel*='cart_"+str[1]+"_"+str[2]+"']").addClass("cart_warn");	
					else
					$(".deal_"+str[1]).addClass("cart_warn");
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}
//删除购物车
function del_cart(id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=cart&act=delcart&id="+id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			location.href = CART_URL;
			/*if(obj.status == 1)
			{
				$("#cart_list").html(obj.html);
			}
			else
			{
				location.href = CART_URL;
			}*/
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

//提交购物车到结算页
function submit_cart()
{
	var verify_code = $("input[name='verify_code']").val();
	var mobile = $("input[name='lottery_mobile']").val();
	var ajaxurl = APP_ROOT+"/shop.php?ctl=cart&act=check&ajax=1&verify="+verify_code+"&mobile="+mobile;
	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				location.href = CART_CHECK_URL;
			}
			else
			{
				if(obj.open_win == 1)
				{
					$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:LANG['PLEASE_LOGIN_FIRST'],width:570,type:'wee'});
				}
				else
				{
					var str = obj.info.split("|");
					var msg = str[0];
					$.showErr(msg);
					$(".deal_cart_row").removeClass("cart_warn");
					if(str[2])
						$("tr[rel*='cart_"+str[1]+"_"+str[2]+"']").addClass("cart_warn");	
						else
						$(".deal_"+str[1]).addClass("cart_warn");
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



//关于购物结算页的相关脚本
//装载配送地区
function load_consignee(consignee_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_consignee&id="+consignee_id;
	$.ajax({ 
		url: ajaxurl,
		success: function(html){
			$("#cart_consignee").html(html);
			load_delivery();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}
//载入配送方式
function load_delivery()
{
	var select_last_node = $("#cart_consignee").find("select[value!='0']");
	if(select_last_node.length>0)
	{		
		var region_id = $(select_last_node[select_last_node.length - 1]).val();
	}
	else
	{
		var region_id = 0;
	}
	
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_delivery&id="+region_id+"&order_id="+$("input[name='id']").val();
	$.ajax({ 
		url: ajaxurl,
		success: function(html){
			$("#cart_delivery").html(html);
			count_buy_total();  //加载完配送方式重新计算总价
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}

//计算购物总价
function count_buy_total()
{
	$("#order_done").attr("disabled",true);
	var query = new Object();
	
	//获取配送方式
	var delivery_id = $("input[name='delivery']:checked").val();

	if(!delivery_id)
	{
		delivery_id = 0;
	}
	query.delivery_id = delivery_id;

	//配送地区
	var select_last_node = $("#cart_consignee").find("select[value!='0']");
	if(select_last_node.length>0)
	{		
		var region_id = $(select_last_node[select_last_node.length - 1]).val();
	}
	else
	{
		var region_id = 0;
	}
	query.region_id = region_id;
	
	//余额支付
	var account_money = $("input[name='account_money']").val();
	if(!account_money||$.trim(account_money)=='')
	{
		account_money = 0;
	}
	query.account_money = account_money;
	
	//全额支付
	if($("#check-all-money").attr("checked"))
	{
		query.all_account_money = 1;
	}
	else
	{
		query.all_account_money = 0;
	}
	
	//代金券
	var ecvsn = $("input[name='ecvsn']").val();
	if(!ecvsn)
	{
		ecvsn = '';
	}
	var ecvpassword = $("input[name='ecvpassword']").val();
	if(!ecvpassword)
	{
		ecvpassword = '';
	}
	query.ecvsn = ecvsn;
	query.ecvpassword = ecvpassword;
	
	//支付方式
	var payment = $("input[name='payment']:checked").val();
	if(!payment)
	{
		payment = 0;
	}
	query.payment = payment;
	query.bank_id = $("input[name='payment']:checked").attr("rel");
	if(!isNaN(order_id)&&order_id>0)
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=count_order_total&id="+order_id;
	else
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=count_buy_total";
	$.ajax({ 
		url: ajaxurl,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(data){
			$("#cart_total").html(data.html);
			$("input[name='account_money']").val(data.account_money);
			if(data.pay_price == 0)
			{
				$("input[name='payment']").attr("checked",false);
			}
			$("#order_done").attr("disabled",false);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}

//购物提交
function submit_buy()
{
	$("#order_done").attr("disabled",true);
	var query = new Object();
	
	//获取配送方式
	var delivery_id = $("input[name='delivery']:checked").val();

	if(!delivery_id)
	{
		delivery_id = 0;
	}
	query.delivery_id = delivery_id;

	//配送地区
	var select_last_node = $("#cart_consignee").find("select[value!='0']");
	if(select_last_node.length>0)
	{		
		var region_id = $(select_last_node[select_last_node.length - 1]).val();
	}
	else
	{
		var region_id = 0;
	}
	query.region_id = region_id;
	
	//余额支付
	var account_money = $("input[name='account_money']").val();
	if(!account_money||$.trim(account_money)=='')
	{
		account_money = 0;
	}
	query.account_money = account_money;
	
	//全额支付
	if($("#check-all-money").attr("checked"))
	{
		query.all_account_money = 1;
	}
	else
	{
		query.all_account_money = 0;
	}
	
	//代金券
	var ecvsn = $("input[name='ecvsn']").val();
	if(!ecvsn)
	{
		ecvsn = '';
	}
	var ecvpassword = $("input[name='ecvpassword']").val();
	if(!ecvpassword)
	{
		ecvpassword = '';
	}
	query.ecvsn = ecvsn;
	query.ecvpassword = ecvpassword;
	
	//支付方式
	var payment = $("input[name='payment']:checked").val();
	if(!payment)
	{
		payment = 0;
	}
	query.payment = payment;
	
	if(!isNaN(order_id)&&order_id>0)
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=count_order_total&id="+order_id;
	else
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=count_buy_total";
	$.ajax({ 
		url: ajaxurl,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(data){
			if(data.is_delivery == 1)
			{
				//配送验证
				if(!data.region_info||data.region_info.region_level != 4)
				{
					$.showErr(LANG['FILL_CORRECT_CONSIGNEE_ADDRESS']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if($.trim($("input[name='consignee']").val())=='')
				{
					$.showErr(LANG['FILL_CORRECT_CONSIGNEE']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if($.trim($("input[name='address']").val())=='')
				{
					$.showErr(LANG['FILL_CORRECT_ADDRESS']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if($.trim($("input[name='zip']").val())=='')
				{
					$.showErr(LANG['FILL_CORRECT_ZIP']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if($.trim($("input[name='mobile']").val())=='')
				{
					$.showErr(LANG['FILL_MOBILE_PHONE']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if(!$.checkMobilePhone($("input[name='mobile']").val()))
				{
					$.showErr(LANG['FILL_CORRECT_MOBILE_PHONE']);
					$("#order_done").attr("disabled",false);
					return;
				}
				if(!data.delivery_info)
				{
					$.showErr(LANG['PLEASE_SELECT_DELIVERY']);
					$("#order_done").attr("disabled",false);
					return;
				}			
			}
			
			if(data.pay_price!=0&&!data.payment_info)
			{
				$.showErr(LANG['PLEASE_SELECT_PAYMENT']);
				$("#order_done").attr("disabled",false);
				return;
			}	
			
			$("#cart_form").submit();
		},
		error:function(ajaxobj)
		{			
//			alert("error: "+ajaxobj.responseText);
//			return false;
		}
	});	
}


function get_verify_code(obj)
{
	var lottery_mobile = $(obj).parent().find("input[name='lottery_mobile']").val();
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=get_verify_code&lottery_mobile="+lottery_mobile;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status)
			{								
				$.showSuccess(obj.info);
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


//定义复制文本
$.copyText = function(id)
{
	var txt = $(id).val();
	if(window.clipboardData)
	{
		window.clipboardData.clearData();
		var judge = window.clipboardData.setData("Text", txt);
		if(judge === true)
			alert(LANG.JS_COPY_SUCCESS);
		else
			alert(LANG.JS_COPY_NOT_SUCCESS);
	}
	else if(navigator.userAgent.indexOf("Opera") != -1)
	{
		window.location = txt;
	} 
	else if (window.netscape) 
	{
		try
		{
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		}
		catch(e)
		{
			alert(LANG.JS_NO_ALLOW);
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip)
			return;
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans)
			return;
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext = txt;
		str.data = copytext;
		trans.setTransferData("text/unicode",str,copytext.length*2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip)
			return false;
		clip.setData(trans,null,clipid.kGlobalClipboard);
		alert(LANG.JS_COPY_SUCCESS);
	}
};


function track_express(express_sn,express_id)
{	
	$.ajax({ 
			url: APP_ROOT+"/express.php?express_sn="+express_sn+"&express_id="+express_id, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				if(obj.status==2)
				{
					window.open(obj.msg);
				}
				if(obj.status==1)
				{
					$.weeboxs.open(obj.msg, {contentType:'html',showButton:false,title:LANG['TRACK_EXPRESS'],width:550,height:280,type:'wee'});
				}
				if(obj.status==0)
				{
					$.showErr(obj.msg);
				}				
			}
	});		
}

function set_sort(type)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=set_sort&type="+type;
	$.ajax({ 
		url: ajaxurl,
		success: function(text){
			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function set_store_sort(type)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=set_store_sort&type="+type;
	$.ajax({ 
		url: ajaxurl,
		success: function(text){
			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function set_event_sort(type)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=set_event_sort&type="+type;
	$.ajax({ 
		url: ajaxurl,
		success: function(text){
			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function switch_style(type)
{
	var ajaxurl = APP_ROOT+"/tuan.php?ctl=ajax&act=switch_style&type="+type;
	$.ajax({ 
		url: ajaxurl,
		success: function(text){
			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}


/**
 * 加入购物车的JS
 */

function add_score(id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==0)
			{
				ajax_login();
			}
			else
			{
				add_cart(id);
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

/* 加入购物车 */
function add_cart(id,attr)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=cart&act=addcart&id="+id;
	if(attr&&attr != '')
	{
		ajaxurl += attr;
		
	}
	else
	{
		attrs = $("select[name='attr[]']");
		for(i=0;i<attrs.length;i++)
		{
			ajaxurl += "&attr[]="+$(attrs[i]).val();
		}
		
	}
	var number = $("input[name='number']").val();
	if(number)
	ajaxurl+="&number="+number;

	$.ajax({
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.open_win == 1)
			{
				if($(".dialog-mask").css("display")=='block')
				{
					$(".dialog-mask,.dialog-box").remove();
				}
				
				if(obj.err == 1)
				$.weeboxs.open("<span class='cart-error'>"+obj.html+"</span>", {contentType:'text',showButton:false,title:LANG['ADD_CART_ERR'],width:570,type:'wee'});	
				else
				$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:LANG['SELECT_AND_ADDCART'],width:570,type:'wee'});
			}
			else if(obj.open_win == 2)
			{
				$.showErr(obj.info);
			}
			else
			{	
				if($(".dialog-mask").css("display")=='block')
				{
					$(".dialog-mask,.dialog-box").remove();
				}
				$("#cart_count").html(parseInt(obj.number));
				$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:LANG['ADDCART_SUCCESS'],width:570,type:'wee'});
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});

}

function collect_goods(goods_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=collect&id="+goods_id;

	$.ajax({
					url: ajaxurl,
					dataType: "json",
					success: function(obj){
						if(obj.open_win == 1)
						{
							$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:LANG['PLEASE_LOGIN_FIRST'],width:570,type:'wee'});
						}
						else
						{
							$.showSuccess(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
	});	
}

function clear_cart()
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=cart&act=clear_cart";

	$.ajax({
					url: ajaxurl,
					success: function(obj){
						location.href= location.href;
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
	});	
}


function submit_sms()
{
	$.weeboxs.open(APP_ROOT+"/shop.php?ctl=sms&act=subscribe", {contentType:'ajax',showButton:false,title:LANG['SMS_SUBSCRIBE'],width:420,height:200,type:'wee'});	
}
function unsubmit_sms()
{
	$.weeboxs.open(APP_ROOT+"/shop.php?ctl=sms&act=unsubscribe", {contentType:'ajax',showButton:false,title:LANG['SMS_UNSUBSCRIBE'],width:420,height:200,type:'wee'});	
}


//定义邮件订阅的js
function submit_mail(o)
{	
	var email = $("#submit-mail-text").val();
	if(email == '')
	{
		$.showErr(LANG.EMAIL_EMPTY_TIP);
		return;
	}
	if(!$.checkEmail(email))
	{
		$.showErr(LANG.EMAIL_FORMAT_ERROR_TIP);
		return;
	}
	var ajaxurl = APP_ROOT+"/tuan.php?ctl=subscribe&act=addmail&email="+email+"&ajax=1";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				$.showSuccess(LANG.SUBSCRIBE_SUCCESS);
				return;
			}
			else
			{
				$.showErr(obj.info);
				return;
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function focus_user(uid,o)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=focus&uid="+uid;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){				
			if(obj.tag==1)
			{
				$(o).removeClass("add_focus");
				$(o).removeClass("remove_focus");
				$(o).addClass("remove_focus");
				$(o).html(obj.html);
			}
			if(obj.tag==2)
			{
				$(o).removeClass("add_focus");
				$(o).removeClass("remove_focus");
				$(o).addClass("add_focus");
				$(o).html(obj.html);
			}
			if(obj.tag==3)
			{
				$.showSuccess(obj.html);
			}
			if(obj.tag==4)
			{
				ajax_login();
			}
				
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function vote_topic(topic_id,tag,o)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=vote_topic&tag="+tag+"&topic_id="+topic_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status)
			$(o).find("span").html(obj.data);
			else
				$.showErr(obj.data);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});		
}

function check_content(obj)
{

	if($(obj).find("*[name='content']").val()=='')
	{
		$.showErr(LANG.MESSAGE_CONTENT_EMPTY);
		$(obj).find("*[name='content']").focus();
		return false;
	}
	else
	{
		return true;
	}
}

function sms_download(id)
{
	var ajaxurl = APP_ROOT+"/youhui.php?ctl=fdetail&act=load_sms&id="+id;
	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{	
				$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:'短信下载',width:570,type:'wee'});
			}
			else
			{				
				//需要登录
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function send_sms(id)
{
	var query = new Object();
	query.id = id;
	query.date_time = $("input[name='date_time']").val();
	query.date_time_h = $("select[name='date_time_h']").val();
	query.date_time_m = $("select[name='date_time_m']").val();
	query.order_count = $("input[name='order_count']").val();
	query.is_private_room = $("input[name='is_private_room']:checked").val();
	query.mobile = $("input[name='mobile']").val();
	

	if(!$.checkMobilePhone(query.mobile)||query.mobile=='')
	{
		$.showErr(LANG['FILL_CORRECT_MOBILE_PHONE']);
		return;
	}
	
	var ajaxurl = APP_ROOT+"/youhui.php?ctl=fdetail&act=send_sms";
	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status == 1)
			{	
				close_pop();
				$.showSuccess(obj.info);
			}
			else
			{				
				//需要登录
				$(".dialog-title").html(LANG['PLEASE_LOGIN_FIRST']);
				$(".dialog-content").html(obj.html);
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function closeCouponSms()
{
	$(".dialog-close").click();
}

function relay_topic(id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==0)
			{
				ajax_login();
			}
			else
			{
				var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=relay_topic&id="+id;
				$.weeboxs.open(ajaxurl, {contentType:'ajax',showButton:false,title:LANG['RELAY_TOPIC'],width:570,type:'wee'});
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
	
}
function do_relay_topic(id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=do_relay_topic&id="+id;
	var query  = new Object();
	query.content = $("textarea[name='relay_content']").val();
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				$("#topic_relay_"+id).html(parseInt($("#topic_relay_"+id).html())+1);
				close_pop();
				$.showSuccess(obj.info);
				var ajax_url = $("input[name='ajax_url']");
				if(ajax_url)
				{
					ajax_load_page($(ajax_url).val(),$("#col_list"));
				}
			}
			else
			{
				$.showErr(obj.info);
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}
function close_pop()
{
	$(".dialog-close").click();
}

function fav_topic(id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=do_fav_topic&id="+id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				$("#topic_fav_"+id).html(parseInt($("#topic_fav_"+id).html())+1);
				$.showSuccess(obj.info);				
				var ajax_url = $("input[name='ajax_url']");
				if(ajax_url)
				{
					ajax_load_page($(ajax_url).val(),$("#col_list"));
				}
			}
			else
			{
				var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
				$.ajax({ 
					url: ajaxurl,
					dataType: "json",
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==0)
						{
							ajax_login();
						}
						else
						{
							$.showSuccess(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
				
			}
//			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function zoom(obj)
{
//	var img_list = $(obj).parent().parent().find(".toogle_topic_image_box");
//	for(i=0;i<img_list.length;i++)
//	{
//		var box = img_list[i];
//		var o = $(box).find("img");
		var o = obj;
		var tag = $(o).attr('tag');
		var b_src = $(o).attr('b');
		var s_src = $(o).attr('s');
		var o_src = $(o).attr('o');
		var w = $(o).attr('w');
		var h = $(o).attr('h');
		
		if(tag == 's')
		{	
			var img_width = 0;
			if(w>525)
			{
				img_width = 525;
			}
			$(o).attr('src',b_src);
			$(o).attr('tag','b');		
			if(img_width>0)
			$(o).attr('width',img_width);	
			else
			$(o).removeAttr('width');	
			var html = '<div><a href=\"'+o_src+'\" target=\"_blank\">查看原图</a></div>' + $(o).parent().html();
			$(o).parent().html(html);				
		}
		else
		{
			$(o).attr('src',s_src);
			$(o).attr('tag','s');
			$(o).removeAttr('width');	
			$(o).parent().find('div').remove();					
		}
//	}
}


//动态载入页面
function ajax_load_page(ajaxurl,dom)
{
	//$(dom).html("<span class='ajaxloading'>"+LANG.AJAX_LOADING+"</span>");
	$.ajax({ 
		url: ajaxurl,
		data:"ajax=1",
		type: "POST",
		success: function(html){
			//$(dom).hide();
			$(dom).html(html);	
			//$(dom).fadeIn();
		},
		error:function(ajaxobj)
		{
//			$(dom).html("");
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function reply_topic(id,obj)
{
	if($(obj).parent().parent().find(".col_item_reply_box").html()=='')
	{
		$(".col_item_reply_box").html("");
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_reply_col_form&id="+id;
		$.ajax({ 
			url: ajaxurl,
			data:"ajax=1",
			type: "POST",
			success: function(html){
				$(obj).parent().parent().find(".col_item_reply_box").html(html);		
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	}
	else
	$(obj).parent().parent().find(".col_item_reply_box").html("");	

}
function ajax_submit_form(obj)
{
	var form = $(obj).parent().parent().parent();
	var verify_img = $(obj).parent().find("img");
	var ajaxurl = $(form).attr("action");
	var img_box = $(form).find("#image_box");
	var textarea = $(form).find("textarea");
	if($.trim(textarea.val())=='')
	{
		$.showErr("请输入分享内容");
		return;
	}
	var groupbox = $(form).find("input[name='group']");
	var groupdatabox = $(form).find("input[name='group_data']");
	var url = $(form).find("input[name='ajax_url']").val();	
	var query = $(form).serialize()+"&ajax=1";	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){	
			if(obj.status==0)
			{
				$.showErr(obj.info);
				return;
			}
			$.showSuccess(obj.info);
			$(img_box).html("");
			$(verify_img).click();
			$(form).find("input[name='verify']").val("");
			$(textarea).val("");
			$(textarea).attr("position",0);
			$(groupbox).val("");	
			$(groupdatabox).val("");	
			$("input[name='other_tag']").attr("checked",false);
			$(".other_tag").hide();
			$(".tag_item").removeClass("tag_item_c");
			$("input[name='tag[]']").val("");
			if($("input[name='syn_weibo']").attr("checked"))
			{
				var syn_class = $(".syn_class");
				for(i=0;i<syn_class.length;i++)
				{					
					syn_topic_to_weibo(obj.data,$(syn_class[i]).val());
				}
			}
			if(url)ajax_load_page(url,$("#col_list"));
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

//同步到微博
function syn_topic_to_weibo(topic_id,class_name)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=syn_to_weibo&topic_id="+topic_id+"&class_name="+class_name;
	$.ajax({ 
		url: ajaxurl,
		type: "POST",
		dataType: "json",
		success: function(data){
			if(!data.status)
			{
				alert(data.msg);
			}
		},
		error:function(ajaxobj)
		{
			
		}
	});	
}

function ajax_submit_reply_form(obj)
{
	var form = $(obj).parent().parent().parent();
	var ajaxurl = $(form).attr("action");
	var textarea = $(form).find("textarea");
	var topic_id = $(form).find("input[name='topic_id']").val();
	var url = APP_ROOT+"/shop.php?ctl=ajax&act=load_reply_col_form&id="+topic_id;
	
	
	var query = $(form).serialize()+"&ajax=1&no_verify=1";	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status)
			{
				$("#topic_reply_"+topic_id).html(parseInt($("#topic_reply_"+topic_id).html())+1);
				$.showSuccess(ajaxobj.info);				
				ajax_load_page(url,$(obj).parent().parent().parent().parent());
			}
			else
				$.showErr(ajaxobj.info);	
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function load_topic_replys(ajaxurl,checklogin)
{
	if(checklogin)
	{
		var ajaxurl_ck = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
		$.ajax({ 
			url: ajaxurl_ck,
			dataType: "json",
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status==0)
				{
					ajax_login(function(){
						$("#topic_page_reply").html("正在加载评论");
						$.ajax({ 
							url: ajaxurl,
							type: "POST",
							success: function(html){
								$("#topic_page_reply").html(html);	
							},
							error:function(ajaxobj)
							{
					//			if(ajaxobj.responseText!='')
					//			alert(ajaxobj.responseText);
							}
						});	
					});
				}
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	}
	else
	{
		$("#topic_page_reply").html("正在加载评论");
		$.ajax({ 
			url: ajaxurl,
			type: "POST",
			success: function(html){
				$("#topic_page_reply").html(html);	
			},
			error:function(ajaxobj)
			{
	//			if(ajaxobj.responseText!='')
	//			alert(ajaxobj.responseText);
			}
		});	
	}
}

function ajax_submit_reply_form_topic_page(obj)
{
	var form = $(obj).parent().parent().parent();
	var ajaxurl = $(form).attr("action");
	var textarea = $(form).find("textarea");
	var topic_id = $(form).find("input[name='topic_id']").val();
	var load_url = $("#load_url").val();
	
	var query = $(form).serialize()+"&ajax=1&no_verify=1";	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status)
			{
				$("#reply_count").html(parseInt($("#reply_count").html())+1);				
				$.showSuccess(ajaxobj.info);		
				load_topic_replys(load_url);
			}
			else
				$.showErr(ajaxobj.info);	
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function delete_topic(id,dom)
{
	if(confirm(LANG.CONFIRM_DELETE_TOPIC))
	{
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=delete_topic&id="+id;
		$.ajax({ 
			url: ajaxurl,
			dataType: "json",
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status)
				{
					$(dom).remove();
				}
				else
					$.showErr(ajaxobj.info);	
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	}
	
}
function delete_topic_reply(id,dom)
{
	if(confirm(LANG.CONFIRM_DELETE_RELAY))
	{
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=delete_topic_reply&id="+id;
		$.ajax({ 
			url: ajaxurl,
			dataType: "json",
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status)
				{
					$(dom).remove();
				}
				else
					$.showErr(ajaxobj.info);	
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	}
}
function init_index_store()
{
	$(".index_store_item").bind("mouseover",function(){
		$(".index_store_detail_box").hide();
		$(".index_store_item").removeClass("act");
		var id = $(this).attr("rel");
		$("#index_store_detail_box_"+id).show();
		$(this).addClass("act");
		$(this).parent().removeClass("r0");
		$(this).parent().removeClass("r1");
		$(this).parent().removeClass("r2");
		$(this).parent().removeClass("r3");
		$(this).parent().removeClass("r4");
		$(this).parent().removeClass("r5");
		$(this).parent().addClass("r"+$(this).attr("idx"));
	});	
}

function event_submit(event_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=check_event&id="+event_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==1)
			{
				//验证可以通过
				show_event_submit(event_id);
			}
			else if(ajaxobj.status==2)
			{
				$.showSuccess(ajaxobj.info);	
			}
			else
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function ajax_login(func)
{	
	$.weeboxs.open(APP_ROOT+"/shop.php?ctl=ajax&act=ajax_login", {contentType:'ajax',showButton:false,title:LANG['PLEASE_LOGIN_FIRST'],width:570,type:'wee',onclose:func});	
}
function show_event_submit(event_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=submit_event&id="+event_id;
	$.weeboxs.open(ajaxurl, {contentType:'ajax',showButton:false,title:LANG['EVENT_SUBMIT'],width:370,type:'wee'});
}

function do_event_submit()
{
	var submit_rows = $(".event_submit_row");
	for(var i=0;i<submit_rows.length;i++)
	{
		var row = $(submit_rows[i]);
		if($(row).find("input").val()=='')
		{
			$.showErr(LANG['PLEASE_INPUT']+$(row).find("span").html());
			$(row).find("input").focus();
			return;
		}
	}
	var query = $("form[name='event_submit_form']").serialize();
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=do_event_submit";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "POST",
		data:query,
		success: function(ajaxobj){
			if(ajaxobj.status==1)
			{
				$.showSuccess(ajaxobj.info);
			}
			else if(ajaxobj.status==2)
			{
				alert(ajaxobj.info);
				location.reload();
			}
			else
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}



var timer; //定时器
userCard=(function(){
	var cardDiv;  //名片dom对象
	var userCardStr="userCard"; //名片dom对象ID前缀
	var qObj,userId;	//触发对象以及用户ID
	var mout=function(){
		//移出事件
		 timer = setTimeout(function(){
	          cardDiv.hide();
	      },500);
	};
	var mover=function(){
		//移入事件
		clearTimeout(timer);
	};	
	var createLoadDiv=function(){
		//创建名片dom对象，首次载入时用
		cardDiv=$("<div id='"+userCardStr+userId+"' class='nameCard'><div class='load'>正在加载，请稍后...</div></div>");
		$("body").append(cardDiv);
	};	
	var resetXY=function(){
		//重置名片dom对象坐标

		var offset = qObj.offset();		
		var of_left = 0;
		if(offset.left+230+qObj.width()>$(document).width())
		{
			of_left = offset.left - 230;
		}
		else
		{
			of_left =  offset.left+qObj.width();
		}
		cardDiv.css( {
			top : offset.top,
			left : of_left
		});
	};	
	var showUserCard = function(){
		//显示名片
		resetXY();
		cardDiv.show();	
	};
	
	var loadCard=function(){		
		$(".nameCard").hide();
		cardDiv=$("#"+userCardStr+userId);		
		if(!cardDiv.length){
			createLoadDiv();
			showUserCard();		
			cardDiv.load(APP_ROOT+"/shop.php?ctl=ajax&act=usercard&uid="+userId);
		}else{
			//已有名片对象时
			showUserCard(); //直接显示
		};
		//为名片对象与触发对象绑定事件
		cardDiv.hover(mover,mout);
		qObj.hover(mover,mout);
	};
	
	return {
		load : function(e,id){//加载id的名片。e:当前DOM元素,直接写this; id:名片上的用户ID		
	
				clearTimeout(timer);
				if(e===undefined || id===undefined || isNaN(id) || id<1){
					return false;
				};				
				qObj=$(e); //为触发对象赋值
				userId=id; //用户ID
				//加载名片
				loadCard(); //加载名片
			}
	  	};
})();


function set_syn(syn_field)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=set_syn&field="+syn_field;
	$.ajax({ 
		url: ajaxurl,
		type: "POST",
		dataType: "json",
		success: function(data){
			alert(data.info);
			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}


function load_api_url(class_name,type)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_api_url&class_name="+class_name+"&type="+type;
	$.ajax({ 
		url: ajaxurl,
		type: "POST",
		success: function(data){
			$("#api_"+class_name+"_"+type).html(data);
		},
		error:function(ajaxobj)
		{

		}
	});
}

function daren_submit()
{
	if($.trim($("*[name='daren_title']").val())=='')
	{
		$.showErr("请输入达人称号");
		return;
	}
	else if($.trim($("*[name='daren_title']").val()).length>10)
	{
		$.showErr("达人称号太长");
		return;
	}
	if($.trim($("*[name='reason']").val())=='')
	{
		$.showErr("请输入申请理由");
		return;
	}
	
	var query = new Object();
	query.daren_title = $.trim($("*[name='daren_title']").val());
	query.reason = $.trim($("*[name='reason']").val());
	
	
	var ajaxurl = APP_ROOT+"/shop.php?ctl=daren&act=submit_daren";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==1)
			{
				$.showSuccess("申请成功，请等待管理员审核");
				$("*[name='daren_title']").val("");
				$("*[name='reason']").val("");
			}
			else if(ajaxobj.status==2)  //其他原因
			{
				$.showErr(ajaxobj.info);	
			}
			else
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function submit_message()
{
	$("#consult-add-form").bind("submit",function(){
		var ajaxurl = $(this).attr("action");
		var query = $(this).serialize() ;
		
		$.ajax({ 
			url: ajaxurl,
			dataType: "json",
			data:query,
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status==1)
				{
					$("#consult-add-form").find("*[name='title']").val("");
					$("#consult-add-form").find("*[name='content']").val("");
					$("#consult-add-form").find("*[name='verify']").val("");
					$.showSuccess(ajaxobj.info,function(){
						location.reload();
					});						
				}
				else
				{
					$.showErr(ajaxobj.info);							
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
}


function init_gotop()
{
	
	$(window).scroll(function(){
		
		var s_top = $(document).scrollTop()+$(window).height()-70;
		if($.browser.msie && $.browser.version =="6.0")
		{
			$("#gotop").css("top",s_top);
			if($(document).scrollTop()>0)
			{				
				$("#gotop").css("visibility","visible");	
			}
			else
			{
				$("#gotop").css("visibility","hidden");	
			}
		}	
		else
		{
			if($(document).scrollTop()>0)
			{
				if($("#gotop").css("display")=="none")
				$("#gotop").fadeIn();	
			}
			else
			{
				if($("#gotop").css("display")!="none")
				$("#gotop").fadeOut();
			}
		}
		
		
	});		
	
	$("#gotop").bind("click",function(){		
		$("html,body").animate({scrollTop:0},"fast","swing",function(){});		
	});
	var top = $(document).scrollTop()+$(window).height()-70;
	if($.browser.msie && $.browser.version =="6.0")
	{
		$("#gotop").css("top",top);
		if($(document).scrollTop()>0)
		{	
			$("#gotop").css("visibility","visible");
		}
		else
		{
			$("#gotop").css("visibility","hidden");
		}
	}
	else
	{
		if($(document).scrollTop()>0)
		{	
			if($("#gotop").css("display")=="none")
			$("#gotop").show();	
		}
		else
		{
			if($("#gotop").css("display")!="none")
			$("#gotop").hide();
		}
	}
	

}

function skip_user_profile()
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=gopreview";
	$.ajax({ 
		url: ajaxurl,
		dataType: "text",
		type: "POST",
		success: function(jumpurl){
			if(jumpurl!="")
			location.href = jumpurl;
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}