var timer;
var c_idx = 1;
var total = $("#main_adv_box").find("span").length;

$(document).ready(function(){
	init_main_adv();	
});

function init_main_adv()
{
	$("#main_adv_box").find("span[rel='1']").show();
	$("#main_adv_box").find("li[rel='1']").addClass("act");
	
	timer = window.setInterval("auto_play()", 5000);
	$("#main_adv_box").find("li").hover(function(){
		show_current_adv($(this).attr("rel"));		
	});
	
	$("#main_adv_box").hover(function(){
		clearInterval(timer);
	},function(){
		timer = window.setInterval("auto_play()", 5000);
	});
}

function auto_play()
{	
	if(c_idx == total)
	{
		c_idx = 1;
	}
	else
	{
		c_idx++;
	}
	show_current_adv(c_idx);
}

function show_current_adv(idx)
{	
	$("#main_adv_box").find("span[rel!='"+idx+"']").hide();
	$("#main_adv_box").find("li").removeClass("act");
	$("#main_adv_box").find("li").find("div div div div").css("background-color","#fff");
	if($("#main_adv_box").find("span[rel='"+idx+"']").css("display")=='none')
	$("#main_adv_box").find("span[rel='"+idx+"']").fadeIn();
	$("#main_adv_box").find("li[rel='"+idx+"']").addClass("act");
	$("#main_adv_box").find("li[rel='"+idx+"']").find("div div div div").css("background-color","#b40001");
	c_idx = idx;
	
	
}