<?php if ($this->_var['youhui_list']): ?>
<div class="index_right_box">
	<div class="box_top">
			<div class="f_l">热门优惠券</div>
			<div class="f_r top_more"><a href="<?php
echo parse_url_tag("u:youhui|fcate|"."".""); 
?>">更多</a></div>
	</div>
	<div class="box_main clearfix">
		<?php $_from = $this->_var['youhui_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'right_youhui_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['right_youhui_item']):
?>
			<div class="right_youhui_item">
				<div class="right_youhui_ico">
				<a href="<?php
echo parse_url_tag("u:youhui|fdetail|"."id=".$this->_var['right_youhui_item']['id']."".""); 
?>" title="<?php echo $this->_var['right_youhui_item']['name']; ?>" target="_blank"><img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['right_youhui_item']['icon'],
  'w' => '50',
  'h' => '50',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" width="50" height="50" alt="<?php echo $this->_var['right_youhui_item']['name']; ?>" class="lazy" /></a>
				</div>
				<div class="right_youhui_info">					
					<a href="<?php
echo parse_url_tag("u:youhui|fdetail|"."id=".$this->_var['right_youhui_item']['id']."".""); 
?>" title="<?php echo $this->_var['right_youhui_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['right_youhui_item']['name'],
  'b' => '0',
  'e' => '18',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
					<div class="blank1"></div>
					<div class="youhui_downcount"><span style="color:#b40000; font-weight:bolder; font-family:verdana;"><?php echo intval($this->_var['right_youhui_item']['sms_count'])+intval($this->_var['right_youhui_item']['print_count']);?></span>人已下载</div>		
				</div>
			</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="首页右侧优惠券模块广告" />