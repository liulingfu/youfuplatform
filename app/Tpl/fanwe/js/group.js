function join_group(group_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=group&act=joingroup&id="+group_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				location.reload();
			}		
			else if(obj.status == 2)
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
function exit_group(group_id)
{
	var ajaxurl = APP_ROOT+"/shop.php?ctl=group&act=exitgroup&id="+group_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				location.reload();
			}		
			else if(obj.status == 2)
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

function check_forum_content(obj)
{
	var verify = $(obj).find("*[name='verify']");
	if(verify.length>0)
	{
		if(verify.val()=='')
		{
			$.showErr("验证码不能为空");
			return false;
		}
		else
		{
			var ver = verify.val();
			var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=checkverify&verify="+ver+"&ajax=1";
			$.ajax({ 
				url: ajaxurl,
				dataType: "json",
				success: function(o){
					if(o.status == 1)
					{
						$(obj).submit();
					}		
					else 
					{
						$.showErr("验证码不正确");
					}
				},
				error:function(ajaxobj)
				{
//					if(ajaxobj.responseText!='')
//					alert(ajaxobj.responseText);
				}
			});	
			return false;
		}
	}
	if($(obj).find("*[name='forum_title']").val()=='')
	{
		$.showErr("主题标题不能为空");
		$(obj).find("*[name='forum_title']").focus();
		return false;
	}
	else if($(obj).find("*[name='content']").val()=='')
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



////////
var rec_timer;
var c_idx = 1;
var total = 0;

$(document).ready(function(){

	total = $("#rec_topic").find(".rec_image_topic_item").length;
	init_rec_topic();	
});

function init_rec_topic()
{
	$("#rec_topic").find(".rec_image_topic_item[rel='1']").show();
	$("#rec_topic").find(".img_ico[rel='1']").addClass("act");
	
	rec_timer = window.setInterval("auto_play_rec_topic()", 2000);
	$("#rec_topic").find(".img_ico").hover(function(){
		show_current_tab($(this).attr("rel"));		
	});
	
	$("#rec_topic").hover(function(){
		clearInterval(rec_timer);
	},function(){
		rec_timer = window.setInterval("auto_play_rec_topic()", 2000);
	});
}

function auto_play_rec_topic()
{	
	if(c_idx == total)
	{
		c_idx = 1;
	}
	else
	{
		c_idx++;
	}
	show_current_tab(c_idx);
}

function show_current_tab(idx)
{	

	$("#rec_topic").find(".rec_image_topic_item[rel!='"+idx+"']").hide();
	$("#rec_topic").find(".img_ico").removeClass("act");
	if($("#rec_topic").find(".rec_image_topic_item[rel='"+idx+"']").css("display")=='none')
	$("#rec_topic").find(".rec_image_topic_item[rel='"+idx+"']").fadeIn();
	$("#rec_topic").find(".img_ico[rel='"+idx+"']").addClass("act");
	c_idx = idx;
	
	
}

function do_create_group()
{
	var name = $("input[name='name']").val();
	var memo = $("textarea[name='memo']").val();
	var cate_id = $("select[name='cate_id']").val();
	
	if($.trim(name)=='')
	{
		$.showErr("请填写小组名称",function(){
			$("form[name='create_group']").find("input[name='name']").focus();
		});
		return;
	}
	if($.trim(memo)=='')
	{
		$.showErr("请填写小组说明",function(){
			$("form[name='create_group']").find("textarea[name='memo']").focus();
		});
		return;
	}
	
	var query = new Object();
	query.name = name;
	query.memo = memo;
	query.cate_id = cate_id;
	var ajaxurl = APP_ROOT+"/shop.php?ctl=group&act=do_create_group";
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status==1)
			{
				$.showSuccess("创建成功，请等待管理员审核",function(){
					location.href = obj.url;
				});
			}
			else
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
			
		}
	});	
	
}



