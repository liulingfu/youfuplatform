<?php if ($this->_var['store_list']): ?>
<div class="index_right_box">
	<div class="box_top">
			<div class="f_l">好评商家排行</div>
			<div class="f_r top_more"><a href="<?php
echo parse_url_tag("u:youhui|store|"."".""); 
?>">更多</a></div>
	</div>
	<div class="box_main clearfix">
		<?php $_from = $this->_var['store_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'right_store_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['right_store_item']):
?>
		<div class="right_store_item">
			<span class="right_store_num num_<?php echo intval($this->_var['key'])+1;?>"></span>
			<div class="right_store_info">
				<a href="<?php
echo parse_url_tag("u:youhui|store#view|"."id=".$this->_var['right_store_item']['id']."".""); 
?>" title="<?php echo $this->_var['right_store_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['right_store_item']['name'],
  'b' => '0',
  'e' => '8',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				<div class="blank5"></div>				
				<span class="index_start_bar f_l" title="<?php echo $this->_var['LANG']['dp_point_'.ceil($this->_var['right_store_item']['avg_point'])];?>">
					<i style="width:<?php echo intval($this->_var['right_store_item']['avg_point']/5*100);?>%;"></i>
				</span>
				<span class="f_l index_dp_count">
				<?php echo $this->_var['right_store_item']['dp_count']; ?>封点评
				</span>			
			</div><!--end store_info-->
		</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="首页右侧商家模块广告" />