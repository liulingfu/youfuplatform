<?php exit;?>a:3:{s:8:"template";a:3:{i:0;s:42:"D:/wamp2/www/app/Tpl/fanwe/city_index.html";i:1;s:42:"D:/wamp2/www/app/Tpl/fanwe/inc/header.html";i:2;s:42:"D:/wamp2/www/app/Tpl/fanwe/inc/footer.html";}s:7:"expires";i:1431278579;s:8:"maketime";i:1431274979;}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> 优辅平台O2O商业系统 - 福州站 - 优辅o2o商业系统,国内最优秀的PHP开源o2o系统</title>
<meta name="keywords" content="优辅o2o商业系统关键词" />
<meta name="description" content="优辅o2o商业系统描述" />
<link rel="stylesheet" type="text/css" href="http://localhost/public/runtime/statics/8254bce650289a28628110a77cca3748.css" />
<script language="JavaScript">
//屏蔽可忽略的js脚本错误
function killErr(){
	return true;
}
window.onerror=killErr;
</script>
<script type="text/javascript">
var APP_ROOT = '';
var CART_URL = '/index.php?ctl=cart';
var CART_CHECK_URL = '/index.php?ctl=cart&act=check';
var LOADER_IMG = 'http://localhost/app/Tpl/fanwe/images/lazy_loading.gif';
var ERROR_IMG = 'http://localhost/app/Tpl/fanwe/images/image_err.gif';
</script>
<script type="text/javascript" src="/public/runtime/app/lang.js"></script>
<script type="text/javascript" src="http://localhost/public/runtime/statics/fedffcbf767f8d50015acab1d2cd4cf1.js"></script>
</head>
<body>
<div id="dropdown">	
	<a href="javascript:void(0);" ctl="fcate" act="index" action="/youhui.php" id="search_fcate">搜优惠</a>
	<a href="javascript:void(0);" ctl="ycate" act="index" action="/youhui.php" id="search_ycate">搜代金</a>
	<a href="javascript:void(0);" ctl="tuan" act="index" action="/youhui.php" id="search_tuan">搜团购</a>
	<a href="javascript:void(0);" ctl="store" act="index" action="/youhui.php" id="search_store">搜商家</a>
	<a href="javascript:void(0);" ctl="event" act="index" action="/youhui.php" id="search_event">搜活动</a>
	<a href="javascript:void(0);" ctl="ss" act="pick" action="/shop.php" id="search_ss" >搜商品</a>
	<a href="javascript:void(0);" ctl="topic" act="search" action="/shop.php" id="search_topic">搜分享</a>
</div>	
	<div class="header">
		<div class="top_nav">
			<div class="wrap">
				
				<div class="f_r">
                    <div class="f_l">
                    欢迎来到优辅平台O2O商业系统&nbsp;				
                    </div>
                    <i class="line"></i>
					<span id="user_head_tip">
					554fcae493e564ee0dc75bdf2ebf94caload_user_tip|YToxOntzOjQ6Im5hbWUiO3M6MTM6ImxvYWRfdXNlcl90aXAiO30=554fcae493e564ee0dc75bdf2ebf94ca					</span>
															<span class="cart_ico"><a href="/index.php?ctl=cart">购物车 <span class="cart_count" id="cart_count">554fcae493e564ee0dc75bdf2ebf94caload_cart_count|YToxOntzOjQ6Im5hbWUiO3M6MTU6ImxvYWRfY2FydF9jb3VudCI7fQ==554fcae493e564ee0dc75bdf2ebf94ca</span> 件</a></span>
				</div>
			</div>
		</div><!--end top_nav-->
		<div class="blank1"></div>
		<div class="wrap logo_row">
			<div class="logo f_l">
			<a class="link" href="/">
								<span style='display:inline-block; width:191px; height:60px; background:url(http://localhost/public/attachment/201505/11/00/554f8519b1a34.png) no-repeat; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=http://localhost/public/attachment/201505/11/00/554f8519b1a34.png, sizingMethod=scale);_background-image:none;'></span>			</a>
			</div>
			<div class="f_l" style="padding-top:20px; margin-left:10px;">
				         			 
					 <a href="/index.php?ctl=city" class="switch_city">福州</a>
					 <br />
					<div style="color:#999;"> 我要切换城市</div>
 							</div>
			<script type="text/javascript">
				$(document).ready(function(){
										$("#search_city").click();
										
				});
			</script>
						
			<form id="header_search_box" action="/youhui.php" method="post">
				<div class="search_box f_r">					
					<div class="search_input f_l">						
					<span class="search_type_select" id="select_search_type">
						搜优惠					</span>									
					<input type="text" class="search_txt" name="keyword" id="header_kw" value="554fcae493e564ee0dc75bdf2ebf94caload_keyword|YToxOntzOjQ6Im5hbWUiO3M6MTI6ImxvYWRfa2V5d29yZCI7fQ==554fcae493e564ee0dc75bdf2ebf94ca" />
					<input type="button" class="search_btn" id="search_btn" value="" />
					</div>
					<div class="blank1"></div>
					<div class="keyword_box f_l">
										<a href="/index.php?ctl=discover&tag=%E6%9C%8D%E8%A3%85">服装</a>
										<a href="/index.php?ctl=discover&tag=%E6%89%8B%E6%9C%BA">手机</a>
										<a href="/index.php?ctl=discover&tag=%E9%9B%B6%E9%A3%9F">零食</a>
										</div>
				</div>		
				<input type="hidden" name="act" id="search_act" value="index" />
				<input type="hidden" name="ctl" id="search_ctl" value="fcate" />				
				<input type="hidden" name="is_redirect" value="1" />
			</form>					
		</div><!--end wrap-->
		<div class="main_bar">
			<div class="wrap">
                     
                               
               			
				<ul class="main_nav">
                    
											<li >
							<span class="nav_left" ></span>
								<a href="/index.php"  target="">首页</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/tuan.php"  target="">团购</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/index.php?ctl=mall"  target="">商城</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/youhui.php?ctl=event"  target="">活动</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/youhui.php?ctl=store"  target="">商家</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/index.php?ctl=daren"  target="">达人秀</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/index.php?ctl=group"  target="">小组</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/index.php?ctl=discover"  target="">发现</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/youhui.php?ctl=store&act=brand"  target="">品牌商家</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/youhui.php"  target="">优惠券</a>
							<span class="nav_right" ></span>
						</li>
											<li >
							<span class="nav_left" ></span>
								<a href="/daijin.php"  target="">代金券</a>
							<span class="nav_right" ></span>
						</li>
									</ul>	
				<span class="merchant_join">
				<a href="/biz.php?ctl=join" title="我是商家，我要入驻" target="_blank">我是商家，我要入驻</a>	
				</span>	
			</div>
		</div><!--end main_nav-->
	</div>
	
	
	     
<div class="wrap" >
	 
<div class="blank"></div>
<div class="all_city">
	<span class="select_city_title">您可以进入 <a href="/index.php?city=all">全国</a></span>
	<br />
	<span class="select_city_title">或者按拼音首字母选择：</span>
	 	<div class="city_group">
						<table>
						<tr>
						<td class="zm">
						B：
						</td>
						<td>
												<a href="/index.php?city=beijing">北京</a>
												</td>
						</tr>
						</table>
	</div>
		<div class="city_group">
						<table>
						<tr>
						<td class="zm">
						F：
						</td>
						<td>
												<a href="/index.php?city=fuzhou">福州</a>
												</td>
						</tr>
						</table>
	</div>
		<div class="city_group">
						<table>
						<tr>
						<td class="zm">
						S：
						</td>
						<td>
												<a href="/index.php?city=shanghai">上海</a>
												</td>
						</tr>
						</table>
	</div>
	</div>
</div>
<div class="blank"></div>
<div id="ftw">
        <div id="ft">
            <ul class="cf">
            					<li class="col hp1">
                    <h3>公司信息</h3>
                    <ul class="sub-list">
												<li><a href="/index.php?ctl=help&id=20" >关于我们</a></li>
												<li><a href="/index.php?ctl=user&act=register" target="_blank">加入我们</a></li>
						             
					</ul>
                </li>  
								<li class="col hp2">
                    <h3>获取更新</h3>
                    <ul class="sub-list">
												<li><a href="/tuan.php?ctl=rss" target="_blank">RSS订阅</a></li>
												<li><a href="/tuan.php?ctl=dhapi" target="_blank">开放API</a></li>
						             
					</ul>
                </li>  
								<li class="col hp3">
                    <h3>商务合作</h3>
                    <ul class="sub-list">
												<li><a href="/index.php?ctl=link" target="_blank">友情链接</a></li>
												<li><a href="/tuan.php?ctl=coupon&act=supplier_login" target="_blank">商家登录</a></li>
						             
					</ul>
                </li>  
								<li class="col hp4">
                    <h3>用户帮助</h3>
                    <ul class="sub-list">
						             
					</ul>
                </li>  
				            
				<li class="col end">
                    <div class=logo-footer>
                    	<a href="/" title="优辅平台O2O商业系统">
                    							<span style='display:inline-block; width:191px; height:60px; background:url(http://localhost/public/attachment/201505/11/00/554f8519b1a34.png) no-repeat; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=http://localhost/public/attachment/201505/11/00/554f8519b1a34.png, sizingMethod=scale);_background-image:none;'></span>						</a>
					</div>
                </li>
            </ul>
			<div class="blank"></div>
						<div class="tc">
						<a href="/index.php?ctl=sys&id=31" title="公司简介">公司简介</a>
						&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?ctl=sys&id=30" title="联系我们">联系我们</a>
						&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?ctl=sys&id=29" title="咨询热点">咨询热点</a>
						&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?ctl=sys&id=28" title="隐私保护">隐私保护</a>
						&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?ctl=sys&id=27" title="免责条款">免责条款</a>
						&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?ctl=sys&id=5" title="如何抽奖">如何抽奖</a>
						</div>
            <div class=copyright>
            					电话：400-800-8888 周一至周六 9:00-18:00  
				&nbsp;&nbsp;
								
				&nbsp;&nbsp;
								<div class="blank1"></div>	
				<div style="text-align:center;">[优辅o2o商业系统]<br />
</div>
 
				<div class="blank"></div>				
																<a id=service-msn-help href="msnim:chat?contact=msn@fanwe.com" target=_blank>
					<img src="http://localhost/app/Tpl/fanwe/images/button-custom-msn.gif">
				</a> 
																<a id=service-msn-help href="msnim:chat?contact=msn2@fanwe.com" target=_blank>
					<img src="http://localhost/app/Tpl/fanwe/images/button-custom-msn.gif">
				</a> 
																				<a href="http://wpa.qq.com/msgrd?v=3&uin=88888888&site=qq&menu=yes" target=_blank>
					<img alt="" src="http://localhost/app/Tpl/fanwe/images/button-custom-qq.gif">
				</a>
																<a href="http://wpa.qq.com/msgrd?v=3&uin=9999999&site=qq&menu=yes" target=_blank>
					<img alt="" src="http://localhost/app/Tpl/fanwe/images/button-custom-qq.gif">
				</a>
												
				<div class="blank1"></div>
								
            </div>
        </div>
    </div>
<SCRIPT language="JavaScript">
lastScrollY = 0;
function heartBeat(){
var diffY;
if (document.documentElement && document.documentElement.scrollTop)
diffY = document.documentElement.scrollTop;
else if (document.body)
diffY = document.body.scrollTop
else
{/*Netscape stuff*/}
//alert(diffY);
percent=.1*(diffY-lastScrollY);
if(percent>0)percent=Math.ceil(percent);
else percent=Math.floor(percent);
document.getElementById("leftDiv").style.top = parseInt(document.getElementById("leftDiv").style.top)+percent+"px";
document.getElementById("rightDiv").style.top = parseInt(document.getElementById("rightDiv").style.top)+percent+"px";
lastScrollY=lastScrollY+percent;
//alert(lastScrollY);
}
//下面这段删除后，对联将不跟随屏幕而移动。
window.setInterval("heartBeat()",1);
//-->
//关闭按钮
function close_left(){
    leftDiv.style.visibility='hidden';
}
function close_right(){
    rightDiv.style.visibility='hidden';
}
</SCRIPT>
<style type="text/css">
#leftDiv,#rightDiv{width:100px;position:absolute;}
.itemFloat{width:100px;height:auto;line-height:5px}
.itemFloat img{width:100px;}
</style>
<div id="leftDiv" style="top:160px;left:5px">
    <div id="left2" class="itemFloat">
    
    <br><a href="javascript:close_left();" title="关闭上面的广告">×</a>
    </div>
</div>
<div id="rightDiv" style="top:160px;right:5px">
    <div id="right2" class="itemFloat">
    
    <br><a href="javascript:close_right();" title="关闭上面的广告">×</a>
    </div>
</div>
	<div id="gotop"></div>
</body>
</html>