

$(function(){
	//首页导航
	$('.index_fenlei .sub_fenlei').parent().mouseenter(function(){
		$(this).addClass("selected");
		$(this).find('dt a').addClass('cur');
		$(this).find('dd a').addClass('a_cur');
		//$(this).find('dd').eq(0).addClass('dd_cur');
		$(this).find('.sub_fenlei').show();
	}).mouseleave(function(){
		$(this).removeClass("selected");
		$(this).find('dt a').removeClass('cur');
		$(this).find('dd a').removeClass('a_cur');
		//$(this).find('dd').eq(0).removeClass('dd_cur');
		$(this).find('.sub_fenlei').hide();
	});	


	//首页左侧切换
	//$('.leftsel dl').hover(function(){$(this).addClass('hover');},function(){$(this).removeClass('hover');});  
	$('.lefttab span').hover(function(){
		var selList = $(this).index()+1;
		var tabList = $(this).parent('.lefttab').attr('id').split('_')[1];
		$(this).parent('.lefttab').find('span').removeClass('cur');
		$(this).addClass('cur');
		$('.list'+tabList).addClass('hidden');
		$('#leftsel'+tabList+'_'+selList).removeClass('hidden');
		
	});
	
	$('.new_index_tab dt div').hover(function(){
		$(this).addClass('onhover');
		},function(){
		$(this).removeClass('onhover');

	});	
	function indexTab(){
		var _COUNT = $('.new_index_tab dt div').length;
		var selDiv =_COUNT-1;
		var selTableList = $('.new_index_tab').find('dd').eq(selDiv);
		$('.new_index_tab dt div').click(function(){
			selDiv = $(this).index();
			var selTableList = $(this).parent().parent().find('dd').eq(selDiv);
			$('.new_index_tab dt div').removeClass('cur');
			$(this).addClass('cur');
			$('.new_index_tab dd').addClass('hidden');
			selTableList.removeClass('hidden');
		});
		
		if(_COUNT >1){
			setInterval(function(){
				selDiv ++;
				if(selDiv == _COUNT){
					selDiv = 0;
				}
				selTableList = $('.new_index_tab').find('dd').eq(selDiv);
				$('.new_index_tab dt div').removeClass('cur');
				$('.new_index_tab dt div').eq(selDiv).addClass('cur');
				$('.new_index_tab dd').addClass('hidden');
				selTableList.removeClass('hidden');
			},5000)	
		}
	}
	indexTab();
	
 })