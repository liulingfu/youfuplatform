//顶部伸展广告
if ($("#js_ads_banner_top_slide").length){
	var $slidebannertop = $("#js_ads_banner_top_slide"),$bannertop = $("#js_ads_banner_top");
	setTimeout(function(){$bannertop.slideUp(1000);$slidebannertop.slideDown(1000);},2500);
	setTimeout(function(){$slidebannertop.slideUp(1000,function (){$bannertop.slideDown(1000);});},8500);
}