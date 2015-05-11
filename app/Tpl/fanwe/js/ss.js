$(document).ready(function(){
	$("select[name='id']").bind("change",function(){
		load_filter_group();
	});
});

function load_filter_group()
{
	var cate_id = $("select[name='id']").val();
	if(cate_id>0)
	{
		var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=load_filter_group&cate_id="+cate_id;
		$.ajax({ 
			url: ajaxurl,
			success: function(html){
				$("#filter_row").html(html);
			}
		});	
	}
	else
	{
		$("#filter_row").html("");
	}
}