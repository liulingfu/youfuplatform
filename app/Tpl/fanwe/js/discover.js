$(document).ready(function(){
	$(".pages").hide();
	$("#hd_step").val(1);
	$("#ajax_wait").val(0);
	bind_event();
	load_topic();	
});
function bind_event()
{
	$(window).bind("scroll", function(e){
		load_topic();
	});
}


function load_topic()
{
	var scrolltop = $(window).scrollTop();
	var loadheight = $("#loading").offset().top;
	var windheight = $(window).height();
	
	var cid = $("#hd_cid").val();
	var tag = $("#hd_tag").val();
	var page = $("#hd_page").val();
	var step = $("#hd_step").val();
	var ajax_wait = $("#ajax_wait").val();
	var step_size = $("#hd_step_size").val();
	
	//滚动到位置+分段加载未结束+ajax未在运行
    if(windheight+scrolltop>=loadheight+50&&parseInt(step)>0&&ajax_wait==0)
    {
    	var query = new Object();
    	query.cid = cid;
    	query.tag = tag;
    	query.page = page;
    	query.step = step;
    	query.step_size = step_size;
    	$("#ajax_wait").val(1);  //表示开始加载
    	$("#loading").css("visibility","visible");
    	
    	var ajaxurl = APP_ROOT+"/shop.php?ctl=discover&act=load_topic";
    	$.ajax({ 
    		url: ajaxurl,
    		data:query,
    		type: "POST",
    		dataType: "json",
    		success: function(data){
    			$("#loading").css("visibility","hidden");
//    			$("body").append(data.sql+"<br />");
    			$.each(data.doms, function(i,dom){
					add_item(dom);
    			});
    			if(data.status)  //继续加载
    			{    				
	    			$("#hd_step").val(data.step);    			
    				$("#ajax_wait").val(0);    		
       			}
    			else //加载结束
    			{    			
    				$("#ajax_wait").val(0); 
    				$("#hd_step").val(0);
    				$(".pages").show();
    			}
    		},
    		error:function(ajaxobj)
    		{
//    			if(ajaxobj.responseText!='')
//    			alert(ajaxobj.responseText);
    		}
    	});	

    	
    }	
}
//为最短的列加元素
function add_item(dom_html)
{
	var colheight = new Array();
	colheight[0] = $("#discover_col1").height();
	colheight[1] = $("#discover_col2").height();
	colheight[2] = $("#discover_col3").height();
	colheight[3] = $("#discover_col4").height();
	
	var min_height = Math.min(colheight[0],colheight[1],colheight[2],colheight[3]);
	for(i=0;i<colheight.length;i++)
	{
		if(min_height==colheight[i])
		{
			$("#discover_col"+(i+1)).append(dom_html);
			break;
		}
	}
}

function reply_discover_topic(id,obj)
{
	if($(obj).parent().parent().find(".topic_item_reply_box").html()=='')
	{
		$(".topic_item_reply_box").html("");
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_reply_col_form&id="+id;
		$.ajax({ 
			url: ajaxurl,
			data:"ajax=1",
			type: "POST",
			success: function(html){
				$(obj).parent().parent().find(".topic_item_reply_box").html(html);		
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	}
	else
	$(obj).parent().parent().find(".topic_item_reply_box").html("");	

}