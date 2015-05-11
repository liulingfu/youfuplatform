<?php if ($this->_var['event_list']): ?>
<div class="index_right_box">
	<div class="box_top">
			<div class="f_l">大家关注的活动</div>
			<div class="f_r top_more"><a href="<?php
echo parse_url_tag("u:youhui|event|"."".""); 
?>">更多</a></div>
	</div>
	<div class="box_main clearfix">
		<?php $_from = $this->_var['event_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'right_event_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['right_event_item']):
?>
			<div class="right_event_item">
				<a href="<?php
echo parse_url_tag("u:youhui|edetail|"."id=".$this->_var['right_event_item']['id']."".""); 
?>" class="title_link" title="<?php echo $this->_var['right_event_item']['name']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['right_event_item']['name'],
  'b' => '0',
  'e' => '16',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				<div class="blank5"></div>
				时间：<?php 
$k = array (
  'name' => 'to_date',
  'v' => $this->_var['right_event_item']['event_begin_time'],
  'f' => 'Y-m-d',
);
echo $k['name']($k['v'],$k['f']);
?> - <?php 
$k = array (
  'name' => 'to_date',
  'v' => $this->_var['right_event_item']['event_end_time'],
  'f' => 'Y-m-d',
);
echo $k['name']($k['v'],$k['f']);
?>
				<div class="blank5"></div>
				地址：<span title="<?php echo $this->_var['right_event_item']['address']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['right_event_item']['address'],
  'b' => '0',
  'e' => '12',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></span>
			</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="首页右侧活动模块广告" />