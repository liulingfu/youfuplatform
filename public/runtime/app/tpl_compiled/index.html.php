<?php echo $this->fetch('inc/header.html'); ?> 
<?php
$this->_var['indexjs'][] = $this->_var['TMPL_REAL']."/js/index.js";
$this->_var['indexjs'][] = $this->_var['TMPL_REAL']."/js/indexCate.js";
$this->_var['cpindexjs'][] = $this->_var['TMPL_REAL']."/js/index.js";
$this->_var['indexcss'][] = $this->_var['TMPL_REAL']."/css/index.css";
?>
<script type="text/javascript" src="<?php echo $this->_var['APP_ROOT']; ?>/public/runtime/app/deal_cate_conf.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['APP_ROOT']; ?>/public/runtime/app/deal_region_conf/<?php echo $this->_var['deal_city']['id']; ?>.js"></script>
<script type="text/javascript" src="<?php 
$k = array (
  'name' => 'parse_script',
  'v' => $this->_var['indexjs'],
  'c' => $this->_var['cpindexjs'],
);
echo $k['name']($k['v'],$k['c']);
?>"></script>

<link rel="stylesheet" type="text/css" href="<?php 
$k = array (
  'name' => 'parse_css',
  'v' => $this->_var['indexcss'],
);
echo $k['name']($k['v']);
?>" />



<div class="w960">
    <div class="index_left f_l">
     
          <div class="blank"></div>
      <div class="index_left_ad"><adv adv_id="首页推荐商家右侧广告" /></div>  
    </div>
    <div class="index_right f_r">
    <div class="blank"></div>
       
    <div class="index_right_ad"><adv adv_id="首页推荐商家左侧广告" /></div>
    <div class="blank"></div>
    </div>
    <div class="clear"></div>
</div>


<div class="index_left f_l">
	<?php echo $this->_var['left_html']; ?>
	<div class="blank"></div>
	<div class="index_active_group_box">
		<div class="box_top"></div>
		<div class="box_main">
			<?php $_from = $this->_var['hot_group']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'group');if (count($_from)):
    foreach ($_from AS $this->_var['group']):
?>
			<div class="sector_2 index_group_item">
				<div class="group_icon f_l">
				<a href="<?php
echo parse_url_tag("u:shop|group#forum|"."id=".$this->_var['group']['id']."".""); 
?>" title="<?php echo $this->_var['group']['name']; ?>">
				<img src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['group']['icon'],
  'w' => '80',
  'h' => '80',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" alt="<?php echo $this->_var['group']['name']; ?>" width="80" height="80" />
				</a>
				</div>
				<div class="group_info f_l">
					<a href="<?php
echo parse_url_tag("u:shop|group#forum|"."id=".$this->_var['group']['id']."".""); 
?>" title="<?php echo $this->_var['group']['name']; ?>" class="title_link"><?php echo $this->_var['group']['name']; ?></a>
					<div class="group_count">
						<span><?php echo $this->_var['group']['user_count']; ?></span> 成员&nbsp;&nbsp;
						<span><?php echo $this->_var['group']['topic_count']; ?></span> 分享 				
					</div>
					<div class="group_memo">
						<?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['group']['memo'],
  'b' => '0',
  'e' => '25',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?>
					</div>
				</div>
			</div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
	</div>
</div><!--end index_left-->
<div class="index_right f_r">
	 <a href="<?php
echo parse_url_tag("u:shop|mobile|"."".""); 
?>" target="_blank" title="手机客户端下载" class="index_mobile_download"></a>
	<div class="blank"></div>
	<?php if ($this->_var['notice_list']): ?>
	<div class="index_right_box">
		<div class="box_top">
			<div class="f_l">最新动态</div>
			<div class="f_r top_more"><a href="<?php
echo parse_url_tag("u:shop|notice#list|"."".""); 
?>">更多</a></div>
		</div>
		<div class="box_main">
			<ul class="notice_list">
				<?php $_from = $this->_var['notice_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'notice');if (count($_from)):
    foreach ($_from AS $this->_var['notice']):
?>
				<li><a href="<?php echo $this->_var['notice']['url']; ?>" title="<?php echo $this->_var['notice']['title']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['notice']['title'],
  'b' => '0',
  'e' => '17',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
			<div class="blank5"></div>
		</div>
	</div><!--end right_box-->
	 <div class="blank"></div>
	<?php endif; ?>
   
	<?php 
$k = array (
  'name' => 'load_index_daren_list',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?>
	<?php echo $this->_var['right_html']; ?>
	<?php echo $this->_var['right_dp_html']; ?>
	
</div>
<div class="blank"></div>

<!--hidden-->
<div id="bcate_box_drop_down" class="select_drop"></div>
<div id="scate_box_drop_down" class="select_drop"></div>
<div id="area_box_drop_down" class="select_drop"></div>
<div id="quan_box_drop_down" class="select_drop"></div>
<!--hidden-->

<?php echo $this->fetch('inc/footer.html'); ?>