<?php echo $this->fetch('inc/header.html'); ?> 
<div class="blank"></div>
<div class="all_city">
	<span class="select_city_title"><?php echo $this->_var['LANG']['SELECT_CITY']; ?> <a href="<?php
echo parse_url_tag("u:index|index|"."city=all".""); 
?>"><?php echo $this->_var['LANG']['ALL_CITY']; ?></a></span>
	<br />
	<span class="select_city_title"><?php echo $this->_var['LANG']['SELECT_CITY_BY_PY']; ?></span>
	 <?php $_from = $this->_var['deal_city_list_zm']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'deal_city_group');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['deal_city_group']):
?>
	<div class="city_group">
						<table>
						<tr>
						<td class="zm">
						<?php echo $this->_var['key']; ?>：
						</td>
						<td>
						<?php $_from = $this->_var['deal_city_group']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal_city_item');if (count($_from)):
    foreach ($_from AS $this->_var['deal_city_item']):
?>
						<a href="<?php
echo parse_url_tag("u:index|index|"."city=".$this->_var['deal_city_item']['uname']."".""); 
?>"><?php echo $this->_var['deal_city_item']['name']; ?></a>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
						</td>
						</tr>
						</table>
	</div>
	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>
<?php echo $this->fetch('inc/footer.html'); ?>