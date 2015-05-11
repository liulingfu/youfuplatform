<?php if ($this->_var['rnd_daren_list']): ?>
<div class="index_right_box">
	<div class="box_top">推荐达人</div>
	<div class="box_main daren-list clearfix">
		<?php $_from = $this->_var['rnd_daren_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'daren');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['daren']):
?>
		<?php if ($this->_var['key'] < 12): ?>
		<?php 
$k = array (
  'name' => 'show_avatar',
  'uid' => $this->_var['daren']['id'],
  'type' => 'middle',
);
echo $k['name']($k['uid'],$k['type']);
?>
		<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="blank"></div>
<?php endif; ?>