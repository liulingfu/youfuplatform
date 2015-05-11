<?php if ($this->_var['store_list']): ?>
<div class="index_left_box">
		<div class="box_top">
			<div class="index_title_tag_box">
				<div class="index_title_tag tag_index_left_store"></div>				
			</div>
			<div class="f_r top_right_nav">
			<?php $_from = $this->_var['bcate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cate_item');if (count($_from)):
    foreach ($_from AS $this->_var['cate_item']):
?>
			<a href="<?php
echo parse_url_tag("u:youhui|store#index|"."cid=".$this->_var['cate_item']['id']."".""); 
?>" title="<?php echo $this->_var['cate_item']['name']; ?>"><?php echo $this->_var['cate_item']['name']; ?></a>&nbsp;&nbsp;
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			<a href="<?php
echo parse_url_tag("u:youhui|store|"."".""); 
?>" title="更多">更多</a>&nbsp;&nbsp;
			</div>
		</div>
		<div class="box_main clearfix">
			<?php $_from = $this->_var['store_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'store_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['store_item']):
?>
			<div class="index_store_item sector_4"  style="<?php if ($this->_var['key'] % 5 == 4): ?>border-right:none; width:176px;<?php endif; ?> <?php if ($this->_var['key'] > ( count ( $this->_var['store_list'] ) - 1 - ( count ( $this->_var['store_list'] ) % 5 ) )): ?>border-bottom:none;<?php endif; ?>">
				<a href="<?php
echo parse_url_tag("u:youhui|store#view|"."id=".$this->_var['store_item']['id']."".""); 
?>" title="<?php echo $this->_var['store_item']['name']; ?>" target="_blank"><img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['store_item']['preview'],
  'w' => '150',
  'h' => '110',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" width="150" height="110" alt="<?php echo $this->_var['store_item']['name']; ?>" class="lazy" /></a>
				<div class="blank"></div>
				<a href="<?php
echo parse_url_tag("u:youhui|store#view|"."id=".$this->_var['store_item']['id']."".""); 
?>" title="<?php echo $this->_var['store_item']['name']; ?>" target="_blank" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['store_item']['name'],
  'b' => '0',
  'e' => '8',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				<div class="blank"></div>
				
				<span class="index_start_bar f_l" style="margin-left:25px;" title="<?php echo $this->_var['LANG']['dp_point_'.ceil($this->_var['store_item']['avg_point'])];?>">
					<i style="width:<?php echo intval($this->_var['store_item']['avg_point']/5*100);?>%;"></i>
				</span>
				<span class="f_l index_dp_count">
				<?php echo $this->_var['store_item']['dp_count']; ?>封点评
				</span>				
			</div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div><!--end box_main-->
</div>
<?php endif; ?>
<adv adv_id="首页左侧商家模块广告" />
