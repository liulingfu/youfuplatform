$(document).ready(function(){
	$("select[name='deal_cate_id']").bind("change",function(){
		load_sub_cate();
	});
	load_sub_cate();
	$("select[name='city_id']").bind("change",function(){
		load_city_area();
	});
	load_city_area();
});

function load_sub_cate()
{
	var id = $("select[name='deal_cate_id']").val();
	var ajaxurl = APP_ROOT+"/biz.php?ctl=join&act=load_sub_cate&id="+id;
	$.ajax({ 
		url: ajaxurl,
		success: function(html){
			if(html!="")
			{
				$("#sub_cate").find(".cnt").html(html);
				$("#sub_cate").show();
			}
			else
			{
				$("#sub_cate").find(".cnt").html("");
				$("#sub_cate").hide();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function load_city_area()
{
	var id = $("select[name='city_id']").val();
	var ajaxurl = APP_ROOT+"/biz.php?ctl=join&act=load_city_area&id="+id;
	$.ajax({ 
		url: ajaxurl,
		success: function(html){
			if(html!="")
			{
				$("#area").html(html);
				$("#area").show();
				load_quan_list();
				$("select[name='area_id[]']").bind("change",function(){
					load_quan_list();
				});
			}
			else
			{
				$("#area").html("");
				$("#area").hide();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function load_quan_list()
{
	var id = $("select[name='area_id[]']").val();
	var ajaxurl = APP_ROOT+"/biz.php?ctl=join&act=load_quan_list&id="+id;
	$.ajax({ 
		url: ajaxurl,
		success: function(html){
			if(html!="")
			{
				$("#region_mark").find(".cnt").html(html);
				$("#region_mark").show();
			}
			else
			{
				$("#region_mark").find(".cnt").html("");
				$("#region_mark").hide();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}