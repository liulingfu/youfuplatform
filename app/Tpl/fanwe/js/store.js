//is_buy==2取所有
function load_store_dp(id,is_best,filter,sort)
{
	$.ajax({ 
		url: APP_ROOT+"/youhui.php?ctl=review&act=ajax_list&is_best="+is_best+"&supplier_location_id="+id+"&filter="+filter+"&sort="+sort,
		type: "POST",	
		dataType: "json",	
		cache:false,		
		success:function(result){
			$("#store_dp").html(result.html);
		},
		error:function(o){
			//alert(o.responseText);
		}
	});
}

function load_review_form(id,deal_cate_id)
{
	$.ajax({ 
		url: APP_ROOT+"/youhui.php?ctl=store&act=get_reivew_form&is_ajax=1&id="+id+"&deal_cate_id="+deal_cate_id ,
		type: "POST",		
		cache:false,		
		success:function(result){
			$("#review_form").html(result);
		},
		error:function(o){
			
		}
	});
}

$(document).ready(function(){
	$(".ext_nav").find("li").bind("click",function(){
		load_store_extra($(this).attr("rel"),$(this).attr("data"));
	});
	$(".ext_nav").find("li:first").click();
});

function store_send_form(store_id){
	$.ajax({
		url: APP_ROOT + "/youhui.php?ctl=store&act=send_sms_view&id=" + store_id,
		type: "POST",
		dataType:"json",
		success: function(result){
			if (result.status == 2) {
				ajax_login();
			}
			else if (result.status == 0) {
				$.showErr(result.message);
			}
			else {
				$.weeboxs.open(result.html, {contentType: 'text',showButton: false,title: LANG['STORE_SMS_TITLE'],width: 500,type: 'wee'});
			}
		}
	});
}

function StoreSmsSend(){
	var check_mobile = false;
	$(".store_sms_form form .mobile-input").each(function(){
		if($(this).val()!=""){
			check_mobile = true;
		}
	});
	
	if(check_mobile==false){
		$.showErr(LANG['FILL_MOBILE_PHONE']);
		return ;
	}
	
	var data = $(".store_sms_form form").serialize();
	
	$.ajax({
		url: APP_ROOT + "/youhui.php?ctl=store&act=send_store_sms",
		data:data,
		type: "POST",
		dataType: "json",
		success: function(result){
			if (result.status == 2) {
				closeCouponSms();
				ajax_login();
			}
			else if (result.status == 0) {
				$.showErr(result.message);
			}
			else {
				closeCouponSms();
				$.showErr(LANG['SEND_HAS_SUCCESS']);
			}
		}
	});
	
}

function load_store_extra(location_id,type)
{
	$.ajax({ 
		url: APP_ROOT+"/youhui.php?ctl=store&act=ajax_get_content&is_ajax=1&id="+location_id+"&type="+type ,
		type: "POST",
		success:function(result){
			$(".ext_nav").find("li").removeClass("act");
			$(".ext_nav").find("li[data='"+type+"']").addClass("act");
			$(".detail_cnt").find("li").hide();

			$(".detail_cnt").find("#extra_info").html(result);
			$(".detail_cnt").find("#extra_info").show();
			
			
		},
		error:function(o){
			
		}
	});
}

function vote_tag(tag_name,group_id,location_id,dom)
{
	var query = new Object();
	query.tag_name = tag_name;
	query.group_id = group_id;
	query.location_id = location_id;
	$.ajax({ 
		url: APP_ROOT+"/youhui.php?ctl=store&act=vote_tag" ,
		type: "POST",
		data:query,
		dataType:"json",
		success:function(result){
			if(result.status==1)
			{
				$(dom).html(result.info);
			}
			else if(result.status==2)
			{
				ajax_login();
			}
			else
			{
				$.showErr(result.info);
			}
			
			
		},
		error:function(o){
			
		}
	});
}

function join_store(url)
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
				location.href = url;
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function sign_location(id)
{
	var ajaxurl_ck = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
	$.ajax({ 
		url: ajaxurl_ck,
		dataType: "json",
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==0)
			{
				ajax_login();
			}
			else
			{
				var ajaxurl = APP_ROOT+"/youhui.php?ctl=store&act=sign_page&id="+id;
				$.weeboxs.open(ajaxurl, {contentType: 'ajax',showButton: false,title:"签到评分",width: 500,type: 'wee'});
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
	
}


//关于签到的评分

(function($) {      
	//主评分的控制
	$.fn.sign_rating_main = function() {
		var level_data = new Array("差","一般","好","很好","非常好");
		var outBar = $(this);
		$(outBar).find("span").find("input").val(0);	
	    var total_width = $(outBar).width();
	    var sec_width = total_width / 5;	    
	    $(outBar).bind("mousemove mouseover",function(event){
	    	//绑定移动事件
	    	var pageX = event.pageX; //左移量
	    	var left = $(outBar).offset().left;
	    	var move_left = pageX - left;	    	
	    	var sector = Math.ceil(move_left/sec_width);
	    	var cssWidth = (sector * sec_width) + "px";
	    	var tip = level_data[sector - 1];
	    	$("#sign_score_tips").find("span").html(tip);
	    	$(outBar).find("span").attr("sector",sector);
	    	$(outBar).find("span").css("width",cssWidth);	    	
	    });
	    $(outBar).bind("mouseout",function(){
	    	var current_sec = $(outBar).find("span").find("input").val();
	    	var cssWidth = (current_sec * sec_width) + "px";
	    	if(current_sec == 0 )
	    	{
	    		$("#sign_score_tips").find("span").html("点击星星为商家打分，最高5颗星");
	    	}
	    	else
	    	{
	    		$("#dp_point_tips").hide();
	    		var tip = level_data[current_sec - 1];
		    	$("#sign_score_tips").find("span").html(tip);		    	
	    	}
	    	
	    	$(outBar).find("span").css("width",cssWidth);	
	    });
	    $(outBar).bind("click",function(){
	    	var current_sec = $(outBar).find("span").attr("sector");
	    	$(outBar).find("span").find("input").val(current_sec);	
	    });
	    
	};   
	
	
})(jQuery); 

function do_sign_location(id,point)
{
	
	if(isNaN(point)||point<=0||point>5)
	{
		$.showErr("请点击星星为商家打分，最高5颗星");
		return;
	}
	var ajaxurl_ck = APP_ROOT+"/shop.php?ctl=ajax&act=check_login_status";
	$.ajax({ 
		url: ajaxurl_ck,
		dataType: "json",
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==0)
			{
				close_pop();
				ajax_login();
			}
			else
			{
				var ajaxurl = APP_ROOT+"/youhui.php?ctl=store&act=do_sign";
				var query = new Object();
				query.id = id;
				query.point = point;
				$.ajax({ 
					url: ajaxurl,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status)
						{
							$.showSuccess("签到成功",function(){
								location.reload();
							});
						}
						else
						{
							$.showErr(ajaxobj.message);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}