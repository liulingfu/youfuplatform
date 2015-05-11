</div>
<div class="blank"></div>
<div id="ftw">
        <div id="ft">
            <ul class="cf">
            	<?php $_from = $this->_var['deal_help']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help_cate');$this->_foreach['help_cate'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['help_cate']['total'] > 0):
    foreach ($_from AS $this->_var['help_cate']):
        $this->_foreach['help_cate']['iteration']++;
?>
				<li class="col hp<?php echo $this->_foreach['help_cate']['iteration']; ?>">
                    <h3><?php echo $this->_var['help_cate']['title']; ?></h3>
                    <ul class="sub-list">
						<?php $_from = $this->_var['help_cate']['help_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help_item');if (count($_from)):
    foreach ($_from AS $this->_var['help_item']):
?>
						<li><a href="<?php echo $this->_var['help_item']['url']; ?>" <?php if ($this->_var['help_item']['new'] == 1): ?>target="_blank"<?php endif; ?>><?php echo $this->_var['help_item']['title']; ?></a></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>             
					</ul>
                </li>  
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>            
				<li class="col end">
                    <div class=logo-footer>
                    	<a href="<?php echo $this->_var['APP_ROOT']; ?>/" title="<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TITLE',
);
echo $k['name']($k['value']);
?>">
                    	<?php
							$this->_var['logo_image'] = app_conf("FOOTER_LOGO");
						?>
						<?php 
$k = array (
  'name' => 'load_page_png',
  'v' => $this->_var['logo_image'],
);
echo $k['name']($k['v']);
?>
						</a>
					</div>
                </li>
            </ul>
			<div class="blank"></div>
			<?php if ($this->_var['f_link_data']): ?>
			<div class="flink">
			<?php $_from = $this->_var['f_link_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link_group');if (count($_from)):
    foreach ($_from AS $this->_var['link_group']):
?>			
				<span style="color:#ccc; float:left; padding:5px 10px 5px 0px;"><?php echo $this->_var['link_group']['name']; ?></span>
				<?php $_from = $this->_var['link_group']['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
					<a href="http://<?php echo $this->_var['link']['url']; ?>" target="_blank" title="<?php if ($this->_var['link']['description']): ?><?php echo $this->_var['link']['description']; ?><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?>"><?php if ($this->_var['link']['img'] != ''): ?><img src='<?php echo $this->_var['link']['img']; ?>' alt="<?php if ($this->_var['link']['description']): ?><?php echo $this->_var['link']['description']; ?><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?>" /><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?></a>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<div class="blank1"></div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</div>		
			<div class="blank"></div>
			<?php endif; ?>
			<div class="tc">
			<?php $_from = $this->_var['system_article']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'sys_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['sys_item']):
?>
			<?php if ($this->_var['key'] > 0): ?>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<?php endif; ?><a href="<?php echo $this->_var['sys_item']['url']; ?>" title="<?php echo $this->_var['sys_item']['title']; ?>"><?php echo $this->_var['sys_item']['title']; ?></a>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</div>
            <div class=copyright>
            	<?php if (app_conf ( "SHOP_TEL" ) != ''): ?>
				<?php echo $this->_var['LANG']['TEL']; ?>：<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TEL',
);
echo $k['name']($k['value']);
?> <?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ONLINE_TIME',
);
echo $k['name']($k['value']);
?>  
				&nbsp;&nbsp;
				<?php endif; ?>				
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ICP_LICENSE',
);
echo $k['name']($k['value']);
?>&nbsp;&nbsp;
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'COUNT_CODE',
);
echo $k['name']($k['value']);
?>
				<div class="blank1"></div>	
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_FOOTER',
);
echo $k['name']($k['value']);
?> 
				<div class="blank"></div>				
				<?php if (app_conf ( "ONLINE_QQ" ) != '' || app_conf ( "ONLINE_MSN" ) != ''): ?>
				<?php $_from = $this->_var['online_msn']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msn');if (count($_from)):
    foreach ($_from AS $this->_var['msn']):
?>
				<?php if ($this->_var['msn'] != ''): ?>
				<a id=service-msn-help href="msnim:chat?contact=<?php echo $this->_var['msn']; ?>" target=_blank>
					<img src="<?php echo $this->_var['TMPL']; ?>/images/button-custom-msn.gif">
				</a> 
				<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<?php $_from = $this->_var['online_qq']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qq');if (count($_from)):
    foreach ($_from AS $this->_var['qq']):
?>
				<?php if ($this->_var['qq'] != ''): ?>
				<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $this->_var['qq']; ?>&site=qq&menu=yes" target=_blank>
					<img alt="" src="<?php echo $this->_var['TMPL']; ?>/images/button-custom-qq.gif">
				</a>
				<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>				
				<div class="blank1"></div>
				<?php endif; ?>
				
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
    <adv adv_id="左侧漂浮广告" />
    <br><a href="javascript:close_left();" title="关闭上面的广告">×</a>
    </div>
</div>

<div id="rightDiv" style="top:160px;right:5px">
    <div id="right2" class="itemFloat">
    <adv adv_id="右侧漂浮广告" />
    <br><a href="javascript:close_right();" title="关闭上面的广告">×</a>
    </div>
</div>


	<div id="gotop"></div>
</body>
</html>