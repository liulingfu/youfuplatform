<?php if ($this->_var['tuan_list']): ?>
<div class="blank"></div>
<div class="index_left_box">
		<div class="box_top">
			<div class="index_title_tag_box">
				<div class="index_title_tag tag_index_left_tuan"></div>				
			</div>
			<div class="f_r top_right_nav">
			<?php $_from = $this->_var['bcate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tuan_cate_item');if (count($_from)):
    foreach ($_from AS $this->_var['tuan_cate_item']):
?>
			<a href="<?php
echo parse_url_tag("u:tuan|index|"."id=".$this->_var['tuan_cate_item']['id']."".""); 
?>" title="<?php echo $this->_var['tuan_cate_item']['name']; ?>"><?php echo $this->_var['tuan_cate_item']['name']; ?></a>&nbsp;&nbsp;
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			<a href="<?php
echo parse_url_tag("u:tuan|index|"."".""); 
?>" title="更多">更多</a>&nbsp;&nbsp;
			</div>
		</div>
		<div class="box_main clearfix">
			<?php $_from = $this->_var['tuan_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'tuan_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['tuan_item']):
?>
			<div class="index_tuan_item sector_3" style="<?php if ($this->_var['key'] % 3 == 2): ?>border-right:none; width:295px;<?php endif; ?> <?php if ($this->_var['key'] > ( count ( $this->_var['tuan_list'] ) - 1 - ( count ( $this->_var['tuan_list'] ) % 3 ) )): ?>border-bottom:none;<?php endif; ?>">
               <!--<div class="top_lab">立省<br><?php echo $this->_var['tuan_item']['save_price_format']; ?></div>-->
				<a href="<?php echo $this->_var['tuan_item']['url']; ?>" title="<?php echo $this->_var['tuan_item']['name']; ?>" target="_blank"><img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['tuan_item']['icon'],
  'w' => '275',
  'h' => '200',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" width=275 height=200 class="lazy" alt="<?php echo $this->_var['tuan_item']['name']; ?>" /></a>
				<div class="blank5"></div>
				<a href="<?php echo $this->_var['tuan_item']['url']; ?>" title="<?php echo $this->_var['tuan_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['tuan_item']['name'],
  'b' => '0',
  'e' => '35',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				<div class="blank5"></div>
				<div class="tl index_market_price">市场价：<?php echo $this->_var['tuan_item']['origin_price_format']; ?></div>
				<div class="index_tuan_price">
					<div class="f_l">团购价：<span style="color:#b40000; font-size:14px; font-weight:bolder; font-family:verdana;"><?php echo $this->_var['tuan_item']['current_price_format']; ?></span></div>
					<div class="f_r">折扣：<span style="color:#b40000;  font-size:14px; font-weight:bolder; font-family:verdana;"><?php echo $this->_var['tuan_item']['discount']; ?></span>折</div>
				</div>
			</div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div><!--end box_main-->
	</div>
	<?php endif; ?>
<adv adv_id="首页左侧团购模块广告" />