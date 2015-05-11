	<div style="margin:10px;">
	<!--登录表单-->
	
								<form method="post" action="<?php
echo parse_url_tag("u:shop|user#dologin|"."".""); 
?>" name="page_login_form" id="page_login_form">
								<div class="field email">
									<label for="login-email-address"><?php echo $this->_var['LANG']['USER_TITLE_EMAIL']; ?>/<?php echo $this->_var['LANG']['USER_TITLE_USER_NAME']; ?></label>
									<input type="text" value="" class="f-input ipttxt" id="login-email-address" name="email" size="30" tabindex="1">
								</div>
								<div class="field password">
									<label for="login-password"><?php echo $this->_var['LANG']['USER_TITLE_USER_PWD']; ?></label>
									<input type="password" value="" class="f-input ipttxt" id="login-password" name="user_pwd" size="30" tabindex="2">
									<span class="lostpassword">&nbsp;&nbsp;<a href="<?php
echo parse_url_tag("u:shop|user#getpassword|"."".""); 
?>"><?php echo $this->_var['LANG']['FORGET_PASSWORD']; ?></a></span> </div>
								<?php if (app_conf ( "VERIFY_IMAGE" ) == 1): ?>
								<div class="field autologin">
									<div class="verify_row">								
									<input type="text" value="" class="f-input ipttxt" name="verify" />	
									<img src="<?php echo $this->_var['APP_ROOT']; ?>/verify.php?rand=<?php 
$k = array (
  'name' => 'rand',
);
echo $k['name']();
?>" onclick="this.src='<?php echo $this->_var['APP_ROOT']; ?>/verify.php?rand='+ Math.random();" title="看不清楚？换一张" />			
									</div>
								</div>
								<?php endif; ?>
								<div class="field autologin">
									<input type="checkbox" id="autologin" name="auto_login" value="1" tabindex="3"><?php echo $this->_var['LANG']['AUTO_LOGIN']; ?>									
								</div>
								<div class="clear"></div>
								
								<div class="act">
									<input type="hidden" name="ajax" value="1">
									<input type="submit" class="login-submit-btn" id="user-login-submit" name="commit" value="<?php echo $this->_var['LANG']['LOGIN']; ?>">
									<span class="to_regsiter"><?php echo sprintf($this->_var['LANG']["REGSITER_NOW"],url("shop","user#register")); ?></span>
								</div>
							</form>
		<!--登录表单-->	
		<script type="text/javascript">		
		$(document).ready(function(){
				$("#user-login-submit").click(function(){
				
				if($.trim($("#login-email-address").val()).length == 0)
				{
					$.showErr("<?php 
$k = array (
  'name' => 'sprintf',
  'format' => $this->_var['LANG']['FORMAT_ERROR_TIP'],
  'value' => $this->_var['LANG']['USER_TITLE_EMAIL'],
);
echo $k['name']($k['format'],$k['value']);
?><?php echo $this->_var['LANG']['OR']; ?><?php 
$k = array (
  'name' => 'sprintf',
  'format' => $this->_var['LANG']['FORMAT_ERROR_TIP'],
  'value' => $this->_var['LANG']['USER_TITLE_USER_NAME'],
);
echo $k['name']($k['format'],$k['value']);
?>",function(){
						$("#login-email-address").focus();
					});					
					return false;
				}
		
				if(!$.minLength($("#login-password").val(),4,false))
				{
					$.showErr("<?php 
$k = array (
  'name' => 'sprintf',
  'format' => $this->_var['LANG']['FORMAT_ERROR_TIP'],
  'value' => $this->_var['LANG']['USER_TITLE_USER_PWD'],
);
echo $k['name']($k['format'],$k['value']);
?>",function(){
						$("#login-password").focus();
					});					
					return false;
				}
				
				var ajaxurl = $("form[name='page_login_form']").attr("action");
				var query = $("form[name='page_login_form']").serialize() ;

				$.ajax({ 
					url: ajaxurl,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==1)
						{
							var integrate = $("<span id='integrate'>"+ajaxobj.data+"</span>");
							$("body").append(integrate);
							update_page_user_tip();
							$("#integrate").remove();				
									
							$.showSuccess(ajaxobj.info,function(){
								if(ajaxobj.jump!='')
								location.href = ajaxobj.jump;
								else
								location.reload();
							});							
						}
						else
						{
							$.showErr(ajaxobj.info);							
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
				
				return false;
				
			});	
		});

		function update_page_user_tip()
		{
			var ajaxurl = APP_ROOT+"/shop.php?ctl=ajax&act=update_user_tip";
			$.ajax({ 
			url: ajaxurl,
			type: "POST",
			success: function(ajaxobj){
				$("#user_head_tip").html(ajaxobj);
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
		}
		
		</script>
		</div>