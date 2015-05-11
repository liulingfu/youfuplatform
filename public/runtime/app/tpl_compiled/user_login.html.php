<?php echo $this->fetch('inc/header.html'); ?> 
<div class="blank"></div>
	<div class="inc">
		<div class="user_inc_top"><?php echo $this->_var['page_title']; ?> <span>&nbsp;<?php echo $this->_var['LANG']['OR']; ?> <?php if ($this->_var['api_callback']): ?><a href="<?php
echo parse_url_tag("u:shop|user#api_create|"."".""); 
?>"><?php else: ?><a href="<?php
echo parse_url_tag("u:shop|user#register|"."".""); 
?>"><?php endif; ?><?php echo $this->_var['CREATE_TIP']; ?></a></span>	</div>
		<div class="inc_main clearfix">
			<div class="user-lr-box-left f_l">
				<?php 
$k = array (
  'name' => 'load_login_form',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
			</div>
			<div class="user-lr-box-right f_r">
				<div class="app_login_box">
				<div class="blank10"></div>
				<?php 
$k = array (
  'name' => 'get_app_login',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
				</div>
			</div>
		</div>
		<div class="inc_foot"></div>
	</div>


<?php echo $this->fetch('inc/footer.html'); ?>