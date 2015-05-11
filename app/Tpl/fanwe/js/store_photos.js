jQuery(function($){
	$("#album-pre").click(function(){
		if(!$(this).hasClass("none")){
			var url = $("#pic-album li.current a").attr("href");
			var idx = 0;
			var html="";
			for(i=0;i<piclist.length;i++)
			{
				if(url == piclist[i].url){
					idx = piclist[i].idx;
				}
			}
			
			var minidx =0;
			var maxidx =0;
			if(idx - 6 <= 0)
				minidx=0;
			else
				minidx = idx-6;
				
			if(minidx + 6 >=piclist.length)
				maxidx = piclist.length;
			else
				maxidx = minidx + 6;
			
			var m=0;
			for(j=minidx;j<maxidx;j++)
			{
				m++;
				var last ="";
				if(m%6 == 0)
				{
					last = "last";
				}
				if (piclist[j].url == normal_url) {
					html += '<li class="current '+last+'"><div><a href="' + piclist[j].url + '"><img alt="' + piclist[j].brief + '" src="' + piclist[j].img + '"></a></div></li>';
				}
				else {
					html += '<li class="'+last+'"><div><a href="' + piclist[j].url + '"><img alt="' + piclist[j].brief + '" src="' + piclist[j].img + '"></a></div></li>';
				}
			}
			$("#pic-album").html(html);
		
			if(minidx==0)
			{
				$(this).addClass("none");
			}
			else{
				$(this).removeClass("none");
			}
			if(maxidx==piclist.length)
			{
				$("#album-next").addClass("none");
			}
			else{
				$("#album-next").removeClass("none");
			}
		}
	});
	
	$("#album-next").click(function(){
		if(!$(this).hasClass("none")){
			var url = $("#pic-album li.current a").attr("href");
			var idx = 0;
			var html="";
			for(i=0;i<piclist.length;i++)
			{
				if(url == piclist[i].url){
					idx = piclist[i].idx;
				}
			}
			var minidx = 0;
			var maxidx =0;
			if(idx + 6 >= piclist.length)
				minidx = idx;
			else if (piclist.length - idx < 6 )
				minidx = idx;
			else
				minidx = idx+6;
				
			if(minidx + 6 >=piclist.length)
				maxidx = piclist.length;
			else
				maxidx = minidx + 6;
			
			var m=0;
			for(j=minidx;j<maxidx;j++)
			{
				m++;
				var last ="";
				if(m%6 == 0)
				{
					last = "last";
				}
				if (piclist[j].url == normal_url) {
					html += '<li class="current '+last+'"><div><a href="' + piclist[j].url + '"><img alt="' + piclist[j].brief + '" src="' + piclist[j].img + '"></a></div></li>';
				}
				else {
					html += '<li class="'+last+'"><div><a href="' + piclist[j].url + '"><img alt="' + piclist[j].brief + '" src="' + piclist[j].img + '"></a></div></li>';
				}
			}
			$("#pic-album").html(html);
			if(minidx==0)
			{
				$("#album-pre").addClass("none");
			}
			else{
				$("#album-pre").removeClass("none");
			}
			if(maxidx==piclist.length)
			{
				$(this).addClass("none");
			}
			else{
				$(this).removeClass("none");
			}
		}
	});
	
	$("a#auto-btn").click(function(){
		if($(this).hasClass("stop"))
		{
			$(this).removeClass("stop");
			$(this).addClass("current");
			$("form#auto_play_form input#auto_play").val("1");
			setIntervalautoplay = setTimeout("autoplay()",parseInt($("form#auto_play_form input#auto_play_time").val()*1000));
		}
		else{
			$(this).removeClass("current");
			$(this).addClass("stop");
			$("form#auto_play_form input#auto_play").val("0");
			clearTimeout(setIntervalautoplay);
		}
		return false;
	});
	
	$("#auto-play-five , #auto-play-three").click(function(){
		$("form#auto_play_form input#auto_play_time").val($(this).val());
		clearTimeout(setIntervalautoplay);
		setIntervalautoplay = setTimeout("autoplay()",parseInt($("form#auto_play_form input#auto_play_time").val()*1000));
	});
	setIntervalautoplay = setTimeout("autoplay()",parseInt($("form#auto_play_form input#auto_play_time").val()*1000));
});
function autoplay(){
	clearTimeout(setIntervalautoplay);
	if($("form#auto_play_form input#auto_play").val()!="1")
		return ;
	var url = null;
	if($("a#next-link").length > 0)
	{
		url = $("a#next-link").attr("href");
	}
	if(url){
		$("form#auto_play_form").attr("action",url) ;
		$("form#auto_play_form").submit();
	}
}