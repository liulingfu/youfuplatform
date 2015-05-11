<?php if ($this->_var['event_list']): ?>
<div class="blank"></div>
<div class="index_left_box">
		<div class="box_top">
			<div class="index_title_tag_box">
				<div class="index_title_tag tag_index_left_event"></div>				
			</div>
			<div class="f_r top_right_nav">
				<?php $_from = $this->_var['bcate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'event_cate_item');if (count($_from)):
    foreach ($_from AS $this->_var['event_cate_item']):
?>
				<a href="<?php
echo parse_url_tag("u:youhui|event|"."cid=".$this->_var['event_cate_item']['id']."".""); 
?>" title="<?php echo $this->_var['event_cate_item']['name']; ?>"><?php echo $this->_var['event_cate_item']['name']; ?></a>&nbsp;&nbsp;
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<a href="<?php
echo parse_url_tag("u:youhui|event|"."".""); 
?>" title="更多">更多</a>&nbsp;&nbsp;
			</div>
		</div>
		<div class="box_main clearfix">
			<?php $_from = $this->_var['event_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'event_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['event_item']):
?>
			<div class="index_event_item sector_3" style="<?php if ($this->_var['key'] % 3 == 2): ?>border-right:none; width:295px;<?php endif; ?> <?php if ($this->_var['key'] > ( count ( $this->_var['event_list'] ) - 1 - ( count ( $this->_var['event_list'] ) % 3 ) )): ?>border-bottom:none;<?php endif; ?>">
				<a href="<?php
echo parse_url_tag("u:youhui|edetail|"."id=".$this->_var['event_item']['id']."".""); 
?>" title="<?php echo $this->_var['event_item']['name']; ?>" target="_blank"><img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['event_item']['icon'],
  'w' => '215',
  'h' => '145',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" width=275 height=200 class="lazy" alt="<?php echo $this->_var['event_item']['name']; ?>" /></a>
				<div class="blank"></div>
				<a href="<?php
echo parse_url_tag("u:youhui|edetail|"."id=".$this->_var['event_item']['id']."".""); 
?>" title="<?php echo $this->_var['event_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['event_item']['name'],
  'b' => '0',
  'e' => '24',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				<div class="blank5"></div>
				<div class="event_info">时间：<?php 
$k = array (
  'name' => 'to_date',
  'v' => $this->_var['event_item']['event_begin_time'],
  'f' => 'Y-m-d',
);
echo $k['name']($k['v'],$k['f']);
?> - <?php 
$k = array (
  'name' => 'to_date',
  'v' => $this->_var['event_item']['event_end_time'],
  'f' => 'Y-m-d',
);
echo $k['name']($k['v'],$k['f']);
?></div>
				<div class="blank1"></div>
				<div class="event_info">地址：<?php echo $this->_var['event_item']['address']; ?></div>
			</div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div><!--end box_main-->
	</div>
<?php endif; ?>
<adv adv_id="首页左侧活动模块广告" />