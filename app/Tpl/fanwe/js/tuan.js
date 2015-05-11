$(document).ready(function(){
	init_detail_nav();
});
function reopen(deal_id,ob)
{
	var ajaxurl = APP_ROOT+"/tuan.php?ctl=ajax&act=reopen&id="+deal_id;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.open_win == 1)
			{
				$.weeboxs.open(obj.html, {contentType:'text',showButton:false,title:LANG['LOGIN'],width:570,type:'wee'});
			}
			else
			{
				if(obj.status)
				{								
					$(ob).parent().find(".ct").html(parseInt($(ob).parent().find(".ct").html())+1);
					$.showSuccess(obj.info);
				}
				else
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
function init_detail_nav()
{
	$(".detail_nav").find("li").bind("click",function(){
		 $(".box_main").hide();
		 $(".detail_nav").find("li").removeClass("current_nav");
	     $(".box_main[rel='"+$(this).attr("rel")+"']").show();
	     $(this).addClass("current_nav");
	});
}