<?php if ($this->_var['user_info']): ?>
	<?php echo $this->_var['LANG']['WELCOME_BACK_HD']; ?>，<?php echo $this->_var['user_info']['user_name']; ?>！
	<?php if ($this->_var['msg_count'] > 0): ?>
	<span class="new_pm"></span>&nbsp;<a href="<?php
echo parse_url_tag("u:shop|uc_msg|"."".""); 
?>" class="msg_count"><?php echo $this->_var['LANG']['MSG_COUNT']; ?>(<?php echo $this->_var['msg_count']; ?>)</a>&nbsp;
	<?php endif; ?>
	<a href="<?php
echo parse_url_tag("u:shop|uc_center|"."".""); 
?>"><?php echo $this->_var['LANG']['MY_ACCOUNT']; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp; 
	<a href="<?php
echo parse_url_tag("u:shop|user#loginout|"."".""); 
?>"><?php echo $this->_var['LANG']['LOGINOUT']; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp; 
	<?php else: ?>
	<?php echo $this->_var['LANG']['PLEASE_FIRST']; ?>
	[ <a href="javascript:void(0);" onclick="ajax_login();"><?php echo $this->_var['LANG']['LOGIN']; ?></a> ] <?php echo $this->_var['LANG']['OR']; ?>
	[ <a href="<?php
echo parse_url_tag("u:shop|user#register|"."".""); 
?>"><?php echo $this->_var['LANG']['REGISTER']; ?></a> ]&nbsp;&nbsp;
<?php endif; ?>