$(document).ready(function(){
	$("select[name='brand_promote']").bind("change",function(){
		init_brand_promote();
	});
	init_brand_promote();
});

function init_brand_promote()
{
	var is_brand_promote = $("select[name='brand_promote']").val();
	if(is_brand_promote==0)
	{
		$(".brand_promote").show();
	}
	else
	{
		$(".brand_promote").hide();
	}
}