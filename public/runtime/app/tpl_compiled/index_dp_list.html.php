	<div class="index_right_box">
	<div class="box_top">
			<div class="f_l">用户最新点评</div>
	</div>
	<div class="box_main right_dp_box" id="right_dp_box">
		
		<?php $_from = $this->_var['dp_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'dp_item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['dp_item']):
?>
			<div class="index_dp_item">
				<?php 
$k = array (
  'name' => 'get_user_name',
  'v' => $this->_var['dp_item']['user_id'],
);
echo $k['name']($k['v']);
?> 点评 <a href="<?php
echo parse_url_tag("u:youhui|store#view|"."id=".$this->_var['dp_item']['supplier_location_id']."".""); 
?>"  class="title_link" title="<?php echo $this->_var['dp_item']['sp_name']; ?>">[ <?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['dp_item']['sp_name'],
  'b' => '0',
  'e' => '10',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?> ]</a>：<a href="<?php
echo parse_url_tag("u:youhui|review#detail|"."id=".$this->_var['dp_item']['id']."".""); 
?>" title="<?php echo $this->_var['dp_item']['title']; ?>" class="title_link"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['dp_item']['title'],
  'b' => '0',
  'e' => '8',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?> - <?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['dp_item']['content'],
  'b' => '0',
  'e' => '30',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
			</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		
	</div>
	</div>