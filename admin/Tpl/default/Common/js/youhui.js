function init_sub_cate()
{
	var cate_id = $("select[name='deal_cate_id']").val();
	var youhui_id = $("input[name='id']").val();
	
	if(cate_id>0)
	{		
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_sub_cate&cate_id="+cate_id+"&youhui_id="+youhui_id, 
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
	var youhui_id = $("input[name='id']").val();	
	if(supplier_id>0)
	{		
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_supplier_location&supplier_id="+supplier_id+"&youhui_id="+youhui_id, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				if(obj.status)
				{
					$("#supplier_location").show();
					$("#supplier_location").find(".item_input").html(obj.data);
					$("input[name='location_id[]']").bind("click",function(){
						$("input[name='address']").val("");
						if($(this).attr("checked"))
						load_location_info($(this).val());
					});
					if(!youhui_id>0)
					{
						var location_id = $("input[name='location_id[]']:checked").val();
						if(!isNaN(location_id))
						{
							load_location_info(location_id);
						}
					}
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

function load_location_info(location_id)
{
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"=Youhui&"+VAR_ACTION+"=get_location_info&id="+location_id, 
		data: "ajax=1",
		dataType: "json", 
		success: function(obj){
			if(obj.status)
			{
				var data = obj.data;
				draw_map(data.xpoint,data.ypoint);
				$("input[name='xpoint']").val(data.xpoint);
				$("input[name='ypoint']").val(data.ypoint);
				$("input[name='address']").val(data.address);
			}	
		}
	});
}

$(document).ready(function(){
	$("select[name='deal_cate_id']").bind("change",function(){
		init_sub_cate();
	});
	init_sub_cate();
	
	$("select[name='supplier_id']").bind("change",function(){
		init_supplier_location();
	});
	init_supplier_location();
});