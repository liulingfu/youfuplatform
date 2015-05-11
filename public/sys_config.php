<?php
return array(
'DEFAULT_ADMIN'=>'admin',
'URL_MODEL'=>'0',
'AUTH_KEY'=>'fanwe',
'TIME_ZONE'=>'8',
'ADMIN_LOG'=>'1',
'DB_VERSION'=>'2.9',
'DB_VOL_MAXSIZE'=>'8000000',
'WATER_MARK'=>'./public/attachment/201505/11/00/554f8519b1a34.png',
'CURRENCY_UNIT'=>'￥',
'BIG_WIDTH'=>'500',
'BIG_HEIGHT'=>'500',
'SMALL_WIDTH'=>'200',
'SMALL_HEIGHT'=>'200',
'WATER_ALPHA'=>'75',
'WATER_POSITION'=>'4',
'MAX_IMAGE_SIZE'=>'300000',
'ALLOW_IMAGE_EXT'=>'jpg,gif,png',
'MAX_FILE_SIZE'=>'1',
'ALLOW_FILE_EXT'=>'1',
'BG_COLOR'=>'#ffffff',
'IS_WATER_MARK'=>'1',
'TEMPLATE'=>'fanwe',
'YOUHUI_SEND_LIMIT'=>'5',
'SCORE_UNIT'=>'积分',
'USER_VERIFY'=>'1',
'SHOP_LOGO'=>'./public/attachment/201505/11/00/554f8519b1a34.png',
'SHOP_LANG'=>'zh-cn',
'SHOP_TITLE'=>'优辅平台O2O商业系统',
'SHOP_KEYWORD'=>'优辅o2o商业系统关键词',
'SHOP_DESCRIPTION'=>'优辅o2o商业系统描述',
'SHOP_TEL'=>'400-800-8888',
'SIDE_DEAL_COUNT'=>'3',
'SIDE_MESSAGE_COUNT'=>'3',
'INVITE_REFERRALS'=>'20',
'INVITE_REFERRALS_TYPE'=>'0',
'ONLINE_MSN'=>'msn@fanwe.com|msn2@fanwe.com',
'ONLINE_QQ'=>'88888888|9999999',
'ONLINE_TIME'=>'周一至周六 9:00-18:00',
'DEAL_PAGE_SIZE'=>'24',
'PAGE_SIZE'=>'24',
'HELP_CATE_LIMIT'=>'4',
'HELP_ITEM_LIMIT'=>'4',
'SHOP_FOOTER'=>'<div style=\"text-align:center;\">[优辅o2o商业系统]<br />
</div>
',
'USER_MESSAGE_AUTO_EFFECT'=>'1',
'SHOP_REFERRAL_HELP'=>'当好友接受您的邀请，在 [优辅网] 上首次成功购买，系统会在 1 小时内返还 ¥20 到您的 [优辅网] 电子账户，下次团购时可直接用于支付。没有数量限制，邀请越多，返利越多。<br />
<br />
<span style=\"color:#f10b00;\">友情接示：购买部份团购将不会产生返利或返利特定金额，请查看相关团购的具体说明							</span>',
'SHOP_REFERRAL_SIDE_HELP'=>'<div class=\"side-tip referrals-side\">							<h3 class=\"first\">在哪里可以看到我的返利？</h3>
							<p>如果邀请成功，在本页面会看到成功邀请列表。在\"账户余额\"页，可看到您目前电子账户的余额。返利金额不返现，可在下次团购时用于支付。</p>
							<h3>我邀请好友了，什么时候收到返利？</h3>
							<p>返利会在 24 小时内返还到您的帐户，并会发邮件通知您。</p>
							<h3>哪些情况会导致邀请返利失效？</h3>
							<ul class=\"invalid\">								<li>好友点击邀请链接后超过 72 小时才购买</li>
								<li>好友购买之前点击了其他人的邀请链接</li>
								<li>好友的本次购买不是首次购买</li>
								<li>由于最终团购人数没有达到人数下限，本次团购取消</li>
							</ul>
							<h3>自己邀请自己也能获得返利吗？</h3>
							<p>不可以。我们会人工核查，对于查实的作弊行为，扣除一切返利，并取消邀请返利的资格。</p>
						</div>
',
'MAIL_SEND_COUPON'=>'0',
'SMS_SEND_COUPON'=>'0',
'MAIL_SEND_PAYMENT'=>'0',
'SMS_SEND_PAYMENT'=>'0',
'REPLY_ADDRESS'=>'info@fanwe.com',
'MAIL_SEND_DELIVERY'=>'0',
'SMS_SEND_DELIVERY'=>'0',
'MAIL_ON'=>'0',
'SMS_ON'=>'0',
'REFERRAL_LIMIT'=>'1',
'SMS_COUPON_LIMIT'=>'3',
'MAIL_COUPON_LIMIT'=>'3',
'COUPON_NAME'=>'优辅券',
'BATCH_PAGE_SIZE'=>'500',
'COUPON_PRINT_TPL'=>'<div style=\"margin:0px auto;padding:10px;border:1px solid #000000;border-image:none;width:600px;font-size:14px;\"><table class=\"dataEdit\" cellspacing=\"0\" cellpadding=\"0\">	<tbody><tr>    <td width=\"400\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
  <td width=\"43%\" style=\"font-family:verdana;font-size:22px;font-weight:bolder;\">    序列号：{$bond.sn}<br />
    密码：{$bond.password}    </td>
</tr>
<tr><td height=\"1\" colspan=\"2\">  <div style=\"width:100%;border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid;\">&nbsp;</div>
  </td>
</tr>
<tr><td height=\"8\" colspan=\"2\"><br />
</td>
</tr>
<tr><td style=\"padding:5px;height:50px;font-family:微软雅黑;font-size:28px;font-weight:bolder;\" colspan=\"2\">{$bond.name}</td>
</tr>
<tr><td width=\"400\" style=\"line-height:22px;padding-right:20px;\">{$bond.user_name}<br />
  生效时间:{$bond.begin_time_format}<br />
  过期时间:{$bond.end_time_format}<br />
  商家电话：<br />
  {$bond.tel}<br />
  商家地址:<br />
  {$bond.address}<br />
  交通路线:<br />
  {$bond.route}<br />
  营业时间：<br />
  {$bond.open_time}<br />
  </td>
  <td><div id=\"map_canvas\" style=\"width:255px;height:255px;\"></div>
  <br />
  </td>
</tr>
</tbody>
</table>
</div>
',
'PUBLIC_DOMAIN_ROOT'=>'',
'SHOW_DEAL_CATE'=>'1',
'REFERRAL_IP_LIMIT'=>'0',
'UNSUBSCRIBE_MAIL_TIP'=>'您收到此邮件是因为您订阅了%s每日推荐更新。如果您不想继续接收此类邮件，可随时%s',
'CART_ON'=>'1',
'REFERRALS_DELAY'=>'1',
'SUBMIT_DELAY'=>'5',
'APP_MSG_SENDER_OPEN'=>'0',
'ADMIN_MSG_SENDER_OPEN'=>'0',
'SHOP_OPEN'=>'1',
'SHOP_CLOSE_HTML'=>'',
'FOOTER_LOGO'=>'./public/attachment/201505/11/00/554f8519b1a34.png',
'GZIP_ON'=>'0',
'INTEGRATE_CODE'=>'',
'INTEGRATE_CFG'=>'',
'SHOP_SEO_TITLE'=>'优辅o2o商业系统,国内最优秀的PHP开源o2o系统',
'CACHE_ON'=>'1',
'EXPIRED_TIME'=>'0',
'FILTER_WORD'=>'',
'STYLE_OPEN'=>'0',
'STYLE_DEFAULT'=>'1',
'TMPL_DOMAIN_ROOT'=>'',
'CACHE_TYPE'=>'File',
'MEMCACHE_HOST'=>'127.0.0.1:11211',
'IMAGE_USERNAME'=>'admin',
'IMAGE_PASSWORD'=>'admin',
'MOBILE_MUST'=>'0',
'ATTR_SELECT'=>'0',
'ICP_LICENSE'=>'',
'COUNT_CODE'=>'',
'DEAL_MSG_LOCK'=>'0',
'PROMOTE_MSG_LOCK'=>'0',
'LIST_TYPE'=>'1',
'SUPPLIER_DETAIL'=>'1',
'KUAIDI_APP_KEY'=>'',
'KUAIDI_TYPE'=>'2',
'SEND_SPAN'=>'2',
'MAIL_USE_COUPON'=>'0',
'SMS_USE_COUPON'=>'0',
'LOTTERY_SMS_VERIFY'=>'0',
'LOTTERY_SN_SMS'=>'0',
'EDM_ON'=>'0',
'EDM_USERNAME'=>'',
'EDM_PASSWORD'=>'',
'SHOP_SEARCH_KEYWORD'=>'服装,手机,零食',
'REC_HOT_LIMIT'=>'4',
'REC_NEW_LIMIT'=>'4',
'REC_BEST_LIMIT'=>'4',
'REC_CATE_GOODS_LIMIT'=>'4',
'SALE_LIST'=>'5',
'INDEX_NOTICE_COUNT'=>'8',
'RELATE_GOODS_LIMIT'=>'5',
'TMPL_CACHE_ON'=>'1',
'USER_LOGIN_SCORE'=>'0',
'USER_LOGIN_MONEY'=>'0',
'USER_REGISTER_SCORE'=>'100',
'USER_REGISTER_MONEY'=>'0',
'DOMAIN_ROOT'=>'',
'MAIN_APP'=>'shop',
'VERIFY_IMAGE'=>'0',
'TUAN_SHOP_TITLE'=>'优辅团购',
'MALL_SHOP_TITLE'=>'优辅商城',
'APNS_MSG_LOCK'=>'0',
'PROMOTE_MSG_PAGE'=>'0',
'APNS_MSG_PAGE'=>'0',
'STORE_SEND_LIMIT'=>'5',
'USER_LOGIN_POINT'=>'10',
'USER_REGISTER_POINT'=>'100',
'USER_LOGIN_KEEP_MONEY'=>'0',
'USER_LOGIN_KEEP_SCORE'=>'5',
'USER_LOGIN_KEEP_POINT'=>'50',
'USER_ACTIVE_MONEY'=>'0',
'USER_ACTIVE_SCORE'=>'0',
'USER_ACTIVE_POINT'=>'10',
'USER_ACTIVE_MONEY_MAX'=>'0',
'USER_ACTIVE_SCORE_MAX'=>'0',
'USER_ACTIVE_POINT_MAX'=>'100',
'USER_DELETE_MONEY'=>'0',
'USER_DELETE_POINT'=>'-10',
'USER_ADD_MONEY'=>'0',
'USER_ADD_SCORE'=>'0',
'USER_ADD_POINT'=>'10',
'USER_DELETE_SCORE'=>'0',
'BIZ_AGREEMENT'=>'<ul>                                	<li>                                    &nbsp;&nbsp;&nbsp;&nbsp;您确认，在开始\"实名认证\"前，您已详细阅读了本协议所有内容，一旦您开始认证流程，即表示您充分理解并同意接受本协议的全部内容。                                    </li>
                                    <li>                                    &nbsp;&nbsp;&nbsp;&nbsp;为了提高服务的安全性和我们的商户身份的可信度，我们向您提供认证服务。在您申请认证前，您必须先注册成为用户。商户认证成功后，我们将给予每个商户一个认证标识。本公司有权采取各种其认为必要手段对商户的身份进行识别。但是，作为普通的网络服务提供商，本公司所能采取的方法有限，而且在网络上进行商户身份识别也存在一定的困难，因此，本公司对完成认证的商户身份的准确性和绝对真实性不做任何保证。                                    </li>
                                    <li>                                    &nbsp;&nbsp;&nbsp;&nbsp;本公司有权记录并保存您提供给本公司的信息和本公司获取的结果信息，亦有权根据本协议的约定向您或第三方提供您是否通过认证的结论以及您的身份信息。                                         </li>
									<li>										<h3>一、关于认证服务的理解与认同</h3>
										<ol class=\"decimal\">											<li>认证服务是由本公司提供的一项身份识别服务。除非本协议另有约定，一旦您的账户完成了认证，相应的身份信息和认证结果将不因任何原因被修改或取消；如果您的身份信息在完成认证后发生了变更，您应向本公司提供相应有权部门出具的凭证，由本公司协助您变更账户的对应认证信息。</li>
											<li>本公司有权单方随时修改或变更本协议内容，并通过网站公告变更后的协议文本，无需单独通知您。本协议进行任何修改或变更后，您还继续使用我们的服务和/或认证服务的，即代表您已阅读、了解并同意接受变更后的协议内容；您如果不同意变更后的协议内容，应立即停用我们的服务和认证服务。</li>
										</ol>
																</li>
<li>										<h3>二、实名认证</h3>
										<ol class=\"decimal\">											<li>个体工商户类商户向本公司申请认证服务时，应向本公司提供以下资料：中华人民共和国工商登记机关颁发的个体工商户营业执照或者其他身份证明文件。</li>
											<li>企业类商户向本公司申请认证服务时，应向本公司提供以下资料：中华人民共和国工商登记机关颁发的企业营业执照或者其他身份证明文件。</li>
                                            <li>                                            其他类商户向本公司申请认证服务时，应向本公司提供以下资料：能够证明商户合法身份的证明文件，或者其他本公司认为必要的身份证明文件。                                            </li>
                                            <li>                                            如商户在认证后变更任何身份信息，则应在变更发生后三个工作日内书面通知本公司变更认证，否则本公司有权随时单方终止提供服务，且因此造成的全部后果，由商户自行承担。                                            </li>
                                            <li>                                            通过实名认证的商户不能自行修改已经认证的信息，包括但不限于企业名称、姓名以及身份证件号码等。                                            </li>
										</ol>
									</li>
									<li>										<h3>三、特别声明</h3>
												<ol class=\"decimal\">																						<li>认证信息共享：<br />
为了使您享有便捷的服务，您经由其它网站向本公司提交认证申请即表示您同意本公司为您核对所提交的全部认证信息，并同意本公司将是否通过认证的结果及相关认证信息提供给该网站。</li>
											<li>												认证资料的管理：<br />
     您在认证时提交给本公司的认证资料，即不可撤销的授权由本公司保留。本公司承诺除法定或约定的事由外，不公开或编辑或透露您的认证资料及保存在本公司的非公开内容用于商业目的，但本条第1项规定以及以下情形除外：												<ol class=\"lower-roman\">													<li>您授权本公司透露的相关信息；</li>
													<li>本公司向国家司法及行政机关提供；</li>
                                                    <li>本公司向本公司关联企业提供；</li>
                                                    <li>第三方和本公司一起为商户提供服务时，该第三方向您提供服务所需的相关信息；</li>
                                                    <li>基于解决您与第三方民事纠纷的需要，本公司有权向该第三方提供您的身份信息。</li>
												</ol>
														</li>
										</ol>
									</li>
																<li>										<h3>四、第三方网站的链接</h3>
                                    </li>
											<li>&nbsp;&nbsp;&nbsp;&nbsp;为实现认证信息审查，我们网站上可能包含了指向第三方网站的链接（以下简称\"链接网站\"）。\"链接网站\"非由本公司控制，对于任何\"链接网站\"的内容，包含但不限于\"链接网站\"内含的任何链接，或\"链接网站\"的任何改变或更新，本公司均不予负责。自\"链接网站\"接收的网络传播或其它形式之传送，本公司不予负责。</li>
									<li>										<h3>五、不得为非法或禁止的使用</h3>
                                    </li>
                                    <li>&nbsp;&nbsp;&nbsp;&nbsp;接受本协议全部的说明、条款、条件是您申请认证的先决条件。您声明并保证，您不得为任何非法或为本协议、条件及须知所禁止之目的进行认证申请。您不得以任何可能损害、使瘫痪、使过度负荷或损害网站或其他网站的服务、或干扰本公司或他人对于认证申请的使用等方式使用认证服务。您不得经由非本公司许可提供的任何方式取得或试图取得任何资料或信息。									</li>
									<li>										<h3>六、有关免责</h3>
                                     </li>
                                     <li>                                     &nbsp;&nbsp;&nbsp;&nbsp;下列情况时本公司无需承担任何责任：                                     </li>
                                     <li>											<ol class=\"decimal\">												<li>由于您将账户密码告知他人或未保管好自己的密码或与他人共享账户或任何其他非本公司的过错，导致您的个人资料泄露。</li>
												<li>													任何由于黑客攻击、计算机病毒侵入或发作、电信部门技术调整导致之影响、因政府管制而造成的暂时性关闭、由于第三方原因(包括不可抗力，例如国际出口的主干线路及国际出口电信提供商一方出现故障、火灾、水灾、雷击、地震、洪水、台风、龙卷风、火山爆发、瘟疫和传染病流行、罢工、战争或暴力行为或类似事件等)及其他非因本公司过错而造成的认证信息泄露、丢失、被盗用或被篡改等。															</li>
												<li>由于与本公司链接的其它网站所造成的商户身份信息泄露及由此而导致的任何法律争议和后果。</li>
                                                <li>任何商户向本公司提供错误、不完整、不实信息等造成不能通过认证或遭受任何其他损失，概与本公司无关。</li>
											</ol>
									</li>
																</ul>
',
'INDEX_LEFT_STORE'=>'1',
'INDEX_LEFT_TUAN'=>'1',
'INDEX_LEFT_YOUHUI'=>'1',
'INDEX_LEFT_DAIJIN'=>'1',
'INDEX_LEFT_EVENT'=>'1',
'INDEX_RIGHT_STORE'=>'1',
'INDEX_RIGHT_TUAN'=>'1',
'INDEX_RIGHT_YOUHUI'=>'1',
'INDEX_RIGHT_DAIJIN'=>'1',
'INDEX_RIGHT_EVENT'=>'1',
'USER_YOUHUI_DOWN_MONEY'=>'0',
'USER_YOUHUI_DOWN_SCORE'=>'0',
'USER_YOUHUI_DOWN_POINT'=>'0',
'COOKIE_PATH'=>'/',
'APPLE_PATH'=>'ios',
'ANDROID_PATH'=>'android',
'QRCODE_SIZE'=>'2',
'SEND_SCORE_SMS'=>'0',
'SEND_SCORE_MAIL'=>'0',
'YOUHUI_SEND_TEL_LIMIT'=>'2',
'IP_LIMIT_NUM'=>'2',
'INDEX_SUPPLIER_COUNT'=>'8',
);
 ?>