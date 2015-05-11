function open_download_win()
{
	$.weeboxs.open(APP_ROOT+"/shop.php?ctl=mobile&act=download", {contentType:'ajax',showButton:false,title:"二维码直接下载",width:400,type:'wee'});	
}