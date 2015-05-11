$(document).ready(function(){
	$("select[name='brand_promote']").bind("change",function(){
		init_brand_promote();
	});
	init_brand_promote();
});

function init_brand_promote()
{
	var is_brand_promote = $("select[name='brand_promote']").val();
	if(is_brand_promote==1)
	{
		$(".brand_promote").show();
	}
	else
	{
//		$(".brand_promote").find("*[name='begin_time'],*[name='end_time'],*[name='brand_promote_logo']").val("");
//		$("#img_del_brand_promote_logo").click();
		$(".brand_promote").hide();
	}
}