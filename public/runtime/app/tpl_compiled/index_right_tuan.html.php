<?php if ($this->_var['tuan_list']): ?>
<div class="index_right_box">
	<div class="box_top">
			<div class="f_l">今日团购</div>
			<div class="f_r top_more"><a href="<?php
echo parse_url_tag("u:tuan|index|"."".""); 
?>">更多</a></div>
	</div>
	<div class="box_main clearfix">
		<?php $_from = $this->_var['tuan_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'right_tuan_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['right_tuan_item']):
?>
		<div class="right_tuan_item">
		<a href="<?php echo $this->_var['right_tuan_item']['url']; ?>" title="<?php echo $this->_var['right_tuan_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['right_tuan_item']['name'],
  'b' => '0',
  'e' => '35',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
			<div class="right_tuan_info">
				<a href="<?php echo $this->_var['right_tuan_item']['url']; ?>" title="<?php echo $this->_var['right_tuan_item']['name']; ?>" target="_blank"><img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['right_tuan_item']['icon'],
  'w' => '215',
  'h' => '145',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" width=215 height=145  alt="<?php echo $this->_var['right_tuan_item']['name']; ?>" class="lazy" /></a>
				<div class="blank5"></div>
				<div class="tl index_market_price">市场价：<?php echo $this->_var['right_tuan_item']['origin_price_format']; ?></div>
				<div class="index_tuan_price">
					<div class="f_l">团购价：<span style="color:#b40000; font-size:14px; font-weight:bolder; font-family:verdana;"><?php echo $this->_var['right_tuan_item']['current_price_format']; ?></span></div>
					<div class="f_r">折扣：<span style="color:#b40000;  font-size:14px; font-weight:bolder; font-family:verdana;"><?php echo $this->_var['right_tuan_item']['discount']; ?></span>折</div>
					<div class="blank1"></div>
				</div>
				
			</div>
		</div>		
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="首页右侧团购模块广告" />