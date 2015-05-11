$(document).ready(function(){
	init_select_box();
	init_right_daijin();
});

function init_select_box()
{
	$("#bcate_box").html(deal_cate_conf[0].n);
	$("#bcate_box_v").val(deal_cate_conf[0].i);
	
	//直接为小分类赋值
	var scate_box_drop_down_html = "<ul><li><a href='javascript:void(0);' rel='0' class='act'>所有分类</a></li>";
	var scate_list = [];
	for(i=0;i<deal_cate_conf.length;i++)
	{
		if($("#bcate_box_v").val()==deal_cate_conf[i].i)
		{
			scate_list = deal_cate_conf[i].s;
			break;
		}
	}
	for(i=0;i<scate_list.length;i++)
	{
		scate_box_drop_down_html+="<li><a href='javascript:void(0);' rel='"+scate_list[i].i+"'>"+scate_list[i].n+"</a></li>";
	}
	scate_box_drop_down_html+="</ul>";
	$("#scate_box_drop_down").html(scate_box_drop_down_html);
	
	
	//绑定小分类点击事件
	$("#scate_box").bind("click",function(){
		var scate_box_v = $("#scate_box_v").val();
		$("#scate_box_drop_down").css("left",$(this).offset().left);
		$("#scate_box_drop_down").css("top",$(this).offset().top+21);
		$("#scate_box_drop_down").css("width",136);
		$("#scate_box_drop_down").find("a").removeClass("act");
		$("#scate_box_drop_down").find("a[rel='"+scate_box_v+"']").addClass("act");
		$("#scate_box_drop_down").show();
		$("#scate_box_drop_down").find("ul").removeClass("scate_list");
		if($("#scate_box_drop_down").find("ul").height()>200)
		{
			$("#scate_box_drop_down").find("ul").addClass("scate_list");
		}
	});
	
	
	//初始化大分类下拉框
	var bcate_box_drop_down_html = "<ul>";
	for(i=0;i<deal_cate_conf.length;i++)
	{
		bcate_box_drop_down_html+="<li><a href='javascript:void(0);' rel='"+deal_cate_conf[i].i+"'>"+deal_cate_conf[i].n+"</a></li>";
	}
	bcate_box_drop_down_html+="</ul>";
	$("#bcate_box_drop_down").html(bcate_box_drop_down_html);
	$("#bcate_box_drop_down").find("a").bind("click",function(){
		if($(this).attr("rel")==$("#bcate_box_v").val())
		{
			
		}
		else
		{
			$("#bcate_box_v").val($(this).attr("rel"));
			$("#bcate_box").html($(this).html());
			$("#scate_box").html("所有分类");
			$("#scate_box_v").val(0);
			
			//开始为小分类下拉赋值
			var scate_box_drop_down_html = "<ul><li><a href='javascript:void(0);' rel='0' class='act'>所有分类</a></li>";
			var scate_list = [];
			for(i=0;i<deal_cate_conf.length;i++)
			{
				if($("#bcate_box_v").val()==deal_cate_conf[i].i)
				{
					scate_list = deal_cate_conf[i].s;
					break;
				}
			}
			for(i=0;i<scate_list.length;i++)
			{
				scate_box_drop_down_html+="<li><a href='javascript:void(0);' rel='"+scate_list[i].i+"'>"+scate_list[i].n+"</a></li>";
			}
			scate_box_drop_down_html+="</ul>";
			$("#scate_box_drop_down").html(scate_box_drop_down_html);	
			
			//为小分类绑定元素的点击
			$("#scate_box_drop_down").find("a").bind("click",function(){
				if($(this).attr("rel")==$("#scate_box_v").val())
				{
					
				}
				else
				{
					$("#scate_box_v").val($(this).attr("rel"));
					$("#scate_box").html($(this).html());
				}
				$("#scate_box_drop_down").hide();
			});
			
		}
		$("#bcate_box_drop_down").hide();
	});
	
	//绑定大分类点击事件
	$("#bcate_box").bind("click",function(){
		var bcate_box_v = $("#bcate_box_v").val();
		$("#bcate_box_drop_down").css("left",$(this).offset().left);
		$("#bcate_box_drop_down").css("top",$(this).offset().top+21);
		$("#bcate_box_drop_down").css("width",136);
		$("#bcate_box_drop_down").find("a").removeClass("act");
		$("#bcate_box_drop_down").find("a[rel='"+bcate_box_v+"']").addClass("act");
		$("#bcate_box_drop_down").show();
	});
	
	//为小分类绑定元素的点击
	$("#scate_box_drop_down").find("a").bind("click",function(){
		if($(this).attr("rel")==$("#scate_box_v").val())
		{
			
		}
		else
		{
			$("#scate_box_v").val($(this).attr("rel"));
			$("#scate_box").html($(this).html());
		}
		$("#scate_box_drop_down").hide();
	});
	
	
	//初始化行政区
	var area_box_drop_down_html = "<ul><li><a href='javascript:void(0);' rel='0' class='act'>所有地区</a></li>";
	for(i=0;i<deal_region_conf.length;i++)
	{
		area_box_drop_down_html+="<li><a href='javascript:void(0);' rel='"+deal_region_conf[i].i+"'>"+deal_region_conf[i].n+"</a></li>";
	}
	area_box_drop_down_html+="</ul>";
	$("#area_box_drop_down").html(area_box_drop_down_html);
	
	
	//绑定行政区点击事件
	$("#area_box").bind("click",function(){
		var area_box_v = $("#area_box_v").val();
		$("#area_box_drop_down").css("left",$(this).offset().left);
		$("#area_box_drop_down").css("top",$(this).offset().top+21);
		$("#area_box_drop_down").css("width",136);
		$("#area_box_drop_down").find("a").removeClass("act");
		$("#area_box_drop_down").find("a[rel='"+area_box_v+"']").addClass("act");
		$("#area_box_drop_down").find("li").css("cursor","pointer");
		$("#area_box_drop_down").show();

	});
	
	//为行政区元素的点击
	$("#area_box_drop_down").find("a").bind("click",function(){

		$("#area_box_v").val($(this).attr("rel"));
		$("#quan_box_v").val(0);
		$("#area_box").html($(this).html());		
		$("#area_box_drop_down").hide();
	});
	
$("#area_box_drop_down").find("li").hover(function(){
		
		if($(this).find("a").attr("rel")>0)
		{

			//为行政区赋值
			var quan_box_v = $("#quan_box_v").val();
			var quan_list = [];
			for(i=0;i<deal_region_conf.length;i++)
			{
				if($(this).find("a").attr("rel")==deal_region_conf[i].i)
				{
					quan_list = deal_region_conf[i].s;
					break;
				}
			}
			if(quan_list.length>0)
			{
				var quan_box_drop_down_html = "<ul class='quan_list' rel='"+$(this).find("a").attr("rel")+"'>";
				for(i=0;i<quan_list.length;i++)
				{
					quan_box_drop_down_html+="<li><a href='javascript:void(0);' rel='"+quan_list[i].i+"'>"+quan_list[i].n+"</a></li>";
				}
				quan_box_drop_down_html+="</ul><div class='blank1'></div>";
				$("#quan_box_drop_down").html(quan_box_drop_down_html);				
				$("#quan_box_drop_down").css("left",$(this).offset().left+135);
				$("#quan_box_drop_down").css("top",$(this).offset().top-$("#quan_box_drop_down").height()/2);		
				$("#quan_box_drop_down").find("a").removeClass("act");
				$("#quan_box_drop_down").find("a[rel='"+quan_box_v+"']").addClass("act");
				$("#quan_box_drop_down").show();
				
				//绑定所有的商圈点击
				$("#quan_box_drop_down").find("a").bind("click",function(){
					if($(this).attr("rel")==$("#quan_box_v").val())
					{
						
					}
					else
					{
						$("#area_box_v").val($(this).parent().parent().attr("rel"));
						$("#quan_box_v").val($(this).attr("rel"));
						$("#area_box").html($(this).html());
					}
					$("#area_box_drop_down").hide();
					$("#quan_box_drop_down").hide();
				});
				
			}
		}		
		
	},function(){

		$("#quan_box_drop_down").hide();
	});

	$("#quan_box_drop_down").hover(function(){
		if($(this).html()!="")
		$(this).show();			
	},function(){
		$(this).hide();
	});
	
	$("#search_store_btn").bind("click",function(){

		$("#store_form").submit();
	});
}


function init_right_daijin()
{
	$(".right_daijin_row_item").bind("mouseover",function(){
		$(".right_daijin_row_item").show();
		$(".right_daijin_item").hide();
		$(this).parent().find(".right_daijin_row_item").hide();
		$(this).parent().find(".right_daijin_item").show();
	});
}

function init_dp_roll()
{
	$("#right_dp_box").find(".index_dp_item:first").animate({marginTop:"-"+$("#right_dp_box").find(".index_dp_item:first").height()+"px"},300,function(){
        	$("#right_dp_box").append("<div class='index_dp_item'>"+$(this).html()+"</div>");
            $(this).remove();
    });
}
var dp_timer;
$(document).ready(function(){
	dp_timer = setInterval('init_dp_roll()',2000);
	$("#right_dp_box").hover(function(){
		clearInterval(dp_timer);
	},function(){
		dp_timer = setInterval('init_dp_roll()',2000);
	});
	
});

