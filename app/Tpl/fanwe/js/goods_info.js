$(document).ready(function(){
	$(".roll_pic").jCarouselLite({
	    btnNext: ".next",
	    btnPrev: ".prev",
	    visible: 5
	});	
	init_gallery();
	count_attr();
	check_number();
	check_buy();
	$("select[name='attr[]']").bind("change",function(){
		
		count_attr();
		check_number();
		check_buy();
	});
	$("input[name='number']").bind("blur",function(){
		count_attr();
		check_number();
		check_buy();
	});	
	
	init_detail();
	init_comment_check();
	/*init_goods_comment();
	init_comment_submit();
	$("#view_comment").bind("click",function(){
		view_comment();
	});*/
	$("#collect").bind("click",function(){
		var goods_id = $("input[name='goods_id']").val();
		collect_goods(goods_id);
	});
});

function view_comment()
{	
	var bindurl = $(".sub_nav").find("a[rel='2']").attr("href");				
				$.ajax({
					url: bindurl,
					success: function(html){
						$(".goods_comment_cnt").html(html);
						$(".detail_nav").find("li[rel='2']").click();	
						bind_url();
						init_comment_submit();
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});				
				return false;	
}
//初始化产品评论的提交
function init_comment_check()
{
	
	$(".goods_comment_form").find("input[name='commit']").bind("click",function(){
		try{
			if (KE.isEmpty("content")) 
			{
				$.showErr(LANG['MESSAGE_CONTENT_EMPTY']);
				return false;
			}
		}catch(ex)
		{
			var content = $("#content").val();
			if(content=='')
			{
				$.showErr(LANG['MESSAGE_CONTENT_EMPTY']);
				return false;
			}
		}
		
		
	});
}
function init_comment_submit()
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=comment&act=add&ajax=1";
	$(".goods_comment_form").find("input[name='commit']").bind("click",function(){
		var is_buy = $(".goods_comment_form").find("input[name='is_buy']").val();
		var point = $(".goods_comment_form").find("select[name='point']").val();

		var content = KE.html("content");
		var goods_id = $(".goods_comment_form").find("input[name='goods_id']").val();
		var verify = $(".goods_comment_form").find("input[name='verify']").val();
		var query = new Object();
		query.is_buy = is_buy;
		query.point = point;
		query.content = content;
		query.rel_table = 'deal';
		query.rel_id = goods_id;
		query.verify = verify;
		
		if (KE.isEmpty("content")) 
		{
			$.showErr(LANG['MESSAGE_CONTENT_EMPTY']);
		}
		else 
		{
			$.ajax({
				url: ajaxurl,
				data: query,
				type: "POST",
				dataType: "json",
				success: function(data){
					
					if (data.status) 
					{						
						var ajaxurl = comment_url + "&is_buy=" + is_buy;
						$.ajax({
							url: ajaxurl,
							success: function(html){
								$(".goods_comment_cnt").html(html);
								//bind_url();
								$.showSuccess(data.info);
								$(".goods_comment_form").find("textarea[name='content']").val("");
								location.reload();
							},
							error: function(ajaxobj){
//								if (ajaxobj.responseText != '') 
//									alert(ajaxobj.responseText);
							}
						});
					}
					else 
					{
						$.showErr(data.info);
					}
					
				},
				error: function(ajaxobj){
//					if (ajaxobj.responseText != '') 
//						alert(LANG['REFRESH_TOO_FAST']);
				}
			});
		}
		
	});
}

//初始化产品页的评论
function init_goods_comment()
{
	var ajaxurl = comment_url;
	$.ajax({
		url: ajaxurl,
		success: function(html){
			$(".goods_comment_cnt").html(html);		
			init_comment_submit();
			bind_url();	
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}
//初始化产品页的评论分页与类型切换
function bind_url()
{
	$(".goods_comment_cnt .sub_nav,.goods_comment_cnt .pages").find("a").bind("click",function(){
				var bindurl = $(this).attr("href");				
				$.ajax({
					url: bindurl,
					success: function(html){
						$(".goods_comment_cnt").html(html);
						init_comment_submit();
						bind_url();
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});				
				return false;
	});
}
//初始化产品页详情与评论的切换
function init_detail()
{
	$(".detail_nav").find("li").bind("click",function(){
		var rel = $(this).attr("rel");
		$(".detail_cnt").find("li").removeClass("act");
		$(".detail_nav").find("li").removeClass("act");
		$(".detail_cnt").find("#detail_cnt_"+rel).addClass("act");
		$(this).addClass("act");
	});
/*	$(".detail_nav").find("li:first").addClass("act");
	$(".detail_cnt").find("li:first").show();*/
}
//初始化产品的图片
function init_gallery()
{	
	var icon_pics = $(".goods_icon_pic").find("li");
	for(var i=0;i<icon_pics.length;i++)
	{
		$(icon_pics[i]).bind("click",function(){
			$(".goods_main_pic").find("li").removeClass("act");
			$(".goods_icon_pic").find("li").removeClass("act");
			$(this).addClass("act");
			var rel = $(this).attr("rel");
			$(".goods_main_pic").find(".pic_"+rel).addClass("act");
		});
	}
}
//计算产品页的属性价格与库存
function count_attr()
{
	var attr_box = $("select[name='attr[]']");
	var attr_str = '';
	var attr_name = '';
	var current_price = parseFloat($("input[name='current_price']").val());
	var price_str = '';
	var attr_price = 0;
	var attr_stock_express = '';
	for(i=0;i<attr_box.length;i++)
	{
		attr_name += "[ "+$(attr_box[i]).parent().find("span").html()+" ]";
		attr_this = $(attr_box[i]).find("option:selected").attr("rel");
		
		if(attr_this==undefined)
		{
			attr_this = '';
		}
		else
		{
			if(parseFloat($(attr_box[i]).find("option:selected").attr("price"))>0){
				attr_price += parseFloat($(attr_box[i]).find("option:selected").attr("price"));
				price_str += " + "+format_price(parseFloat($(attr_box[i]).find("option:selected").attr("price")));
			}
			attr_str+= "[ "+attr_this+" ]";
			
			attr_stock_express += attr_this;
			
		}		
	}
	
	if(attr_str!='')
	{
		if(attr_price>0)
		{
			$("#attr_price").html(price_str + " = " + (format_price(attr_price+current_price)));
		}else{
			$("#attr_price").html('');
		}
		$("#select_attr").html(LANG['SELECTED_ATTR']+attr_str);
		
		var show_stock = false;
		for(cfg in attr_stock_json)
		{
			
			var re = new RegExp(attr_stock_json[cfg]['attr_str'],"ig");
			rs = re.test(attr_stock_express);
			if(rs)
			{
				var stock = attr_stock_json[cfg]['stock_cfg'] - attr_stock_json[cfg]['buy_count'];
				$("#stock").find("span").html(stock);	
				$("input[name='origin_stock']").val(attr_stock_json[cfg]['stock_cfg']);		
				if(attr_stock_json[cfg]['stock_cfg']>0)		
				show_stock = true;
				else
				show_stock = false;
				break;
			}			
		}
		if(attr_stock_json[0])
		{
			if(show_stock)
			{
				$("#stock").show();
			}
			else
			{
				$("#stock").find("span").html("0");	
				$("#stock").hide();
			}
		}
		else
		{
			if($("input[name='origin_stock']").val()>0)
			$("#stock").show();
		}
	}
	else
	{
		if (attr_name != ''&&attr_stock_json[0]) 
		{
			$("#select_attr").html(LANG['PLEASE_SELECT'] + attr_name);
			$("#stock").find("span").html("0");	
			$("#stock").hide();
		}	
		else if(attr_name != '')
		{
			if($("input[name='origin_stock']").val()>0)
			$("#stock").show();
		}
		$("#attr_price").html("");
	}
}
//计算购买数量的有效性
function check_number()
{
	var origin_stock = parseInt($("input[name='origin_stock']").val());
	var buy_number = $("input[name='number']").val();
	
	if(isNaN(buy_number)||buy_number == ''||buy_number<=0)
	{
		$("input[name='number']").val("1");
		return;
	}
	if (origin_stock > 0) {
		var stock = parseInt($("#stock").find("span").html());
		buy_number = parseInt(buy_number);
		if (buy_number > stock && stock > 0) {
			$("input[name='number']").val(stock);
		}
		else 
			if (stock == 0) {
				$("input[name='number']").val("1");
			}
			else {
				$("input[name='number']").val(buy_number);
			}
	}
}
//验证购买按钮
function check_buy()
{
	var stock = parseInt($("#stock").find("span").html());
	var origin_stock = parseInt($("input[name='origin_stock']").val());
	var attr_box = $("select[name='attr[]']");
	var attr_selected = true;
	var goods_id = $("input[name='goods_id']").val();
	if (attr_box)
	{
		for (i = 0; i < attr_box.length; i++) {
			if($(attr_box[i]).val()=='0')
			{
				attr_selected = false;
				break;
			}
		}
	}

	if((origin_stock>0&&stock==0)||!attr_selected)
	{		
		$("*[name='buy_btn']").unbind("click");
		$("*[name='buy_btn']").addClass("btn_disabled");
	}
	else
	{
		$("*[name='buy_btn']").unbind("click");
		$("*[name='buy_btn']").bind("click",function(){
			add_cart(goods_id);
		});
		$("*[name='buy_btn']").removeClass("btn_disabled");
	}
}

function format_price(price)
{
	return price_unit + price;
}

