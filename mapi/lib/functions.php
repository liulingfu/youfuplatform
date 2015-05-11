<?php
//输出接口数据
function output($data)
{
	header("Content-Type:text/html; charset=utf-8");
	$r_type = intval($_REQUEST['r_type']);//返回数据格式类型; 0:base64;1;json_encode;2:array
	$data['act'] = ACT;
	$data['act_2'] = ACT_2;
	if ($r_type == 0)
	{
		echo base64_encode(json_encode($data));
	}else if ($r_type == 1)
	{
		print_r(json_encode($data));
	}else if ($r_type == 2)
	{
		print_r($data);
	};
	exit;
}

//过滤SQL注入
function strim($string)
{
	return trim(addslashes($string));
}


function getMConfig(){

	$m_config = $GLOBALS['cache']->get("m_config");
	if($m_config===false)
	{
		$m_config = array();
		$sql = "select code,val from ".DB_PREFIX."m_config";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $item){
			$m_config[$item['code']] = $item['val'];
		}

		$catalog_id = intval($m_config['catalog_id']);
		$event_cate_id = intval($m_config['event_cate_id']);
		$shop_cate_id = intval($m_config['shop_cate_id']);
		
		if ($catalog_id == 0){
			$m_config["catalog_id_name"] = "全部分类";
		}else{
			$m_config["catalog_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$catalog_id);
		}
		
		if ($event_cate_id == 0){
			$m_config["event_cate_id_name"] = "全部分类";
		}else{
			$m_config["event_cate_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event_cate_id);
		}
		
		if ($shop_cate_id == 0){
			$m_config["shop_cate_id_name"] = "全部分类";
		}else{
			$m_config["shop_cate_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".$shop_cate_id);
		}		
		
		//支付列表
		$sql = "select pay_id as id, code, title as name, has_calc from ".DB_PREFIX."m_config_list where `group` = 1 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$payment_list = array();
		foreach($list as $item){
			$payment_list[] = array("id"=>$item['id'],"code"=>$item['code'],"name"=>$item['name'],"has_calc"=>$item['has_calc']);
		}
		$m_config['payment_list'] = $payment_list;

		//配置方式
		$sql = "select id, id as code, name, 1 as has_calc from ".DB_PREFIX."delivery";			
		$list = $GLOBALS['db']->getAll($sql);
		$delivery_list = array();
		foreach($list as $item){
			$delivery_list[] = array("id"=>$item['id'],"code"=>$item['code'],"name"=>$item['name'],"has_calc"=>$item['has_calc']);
		}
		$m_config['delivery_list'] = $delivery_list;		
		//$order_parm['delivery_list'] = $MConfig['delivery_list'];//$GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."delivery");
		
		//发票内容
		$sql = "select id, title as name from ".DB_PREFIX."m_config_list where `group` = 6 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$invoice_list = array();
		foreach($list as $item){
			$invoice_list[] = array("id"=>$item['id'],"name"=>$item['name']);
		}
		$m_config['invoice_list'] = $invoice_list;
		
		//配送日期选择
		$sql = "select code, title as name from ".DB_PREFIX."m_config_list where `group` = 2 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$delivery_time_list = array();
		foreach($list as $item){
			$delivery_time_list[] = array("id"=>$item['code'],"name"=>$item['name']);
		}
		$m_config['delivery_time_list'] = $delivery_time_list;



		//购物车信息提示
		$sql = "select code, title as name,money from ".DB_PREFIX."m_config_list where `group` = 3 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$yh = array();
		foreach($list as $item){
			$yh[] = array("info"=>$item['name'],"money"=>$item['money']);
		}
		$m_config['yh'] = $yh;


		//新闻公告
		$sql = "select code as title, title as content from ".DB_PREFIX."m_config_list where `group` = 4 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$newslist = array();
		foreach($list as $item){
			$newslist[] = array("title"=>$item['title'],"content"=>$item['content']);
		}
		$m_config['newslist'] = $newslist;


		//地址标题
		$sql = "select code, title from ".DB_PREFIX."m_config_list where `group` = 5 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$addrtlist = array();
		foreach($list as $item){
			$addrtlist[] = array("code"=>$item['code'],"title"=>$item['title']);
		}
		$m_config['addr_tlist'] = $addrtlist;

		$GLOBALS['cache']->set("m_config",$m_config);
	}
	return $m_config;
}


/**
* 过滤SQL查询串中的注释。该方法只过滤SQL文件中独占一行或一块的那些注释。
*
* @access  public
* @param   string      $sql        SQL查询串
* @return  string      返回已过滤掉注释的SQL查询串。
*/
function remove_comment($sql)
{
	/* 删除SQL行注释，行注释不匹配换行符 */
	$sql = preg_replace('/^\s*(?:--|#).*/m', '', $sql);

	/* 删除SQL块注释，匹配换行符，且为非贪婪匹配 */
	//$sql = preg_replace('/^\s*\/\*(?:.|\n)*\*\//m', '', $sql);
	$sql = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql);

	return $sql;
}





function m_toTree($list=null, $pk='id',$pid = 'pid',$child = '_child')
 {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();

            foreach ($list as $key => $data) {
                $_key = is_object($data)?$data->$pk:$data[$pk];
                $refer[$_key] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = is_object($data)?$data->$pid:$data[$pid];
                $is_exist_pid = false;
                foreach($refer as $k=>$v)
                {
                	if($parentId==$k)
                	{
                		$is_exist_pid = true;
                		break;
                	}
                }
                if ($is_exist_pid) {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                } else {
                    $tree[] =& $list[$key];
                }
            }
        }
        return $tree;
 }




//获取所有子集的类
class m_child
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAll("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".$pid);
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}



function getAttrArray($id){
	/**
	 *
	 * selected_attr_1: 默认选择属性a中的值
	 * selected_attr_2: 默认选择属性b中的值
	 *
	 * attr_id: 属性a 关键字 (注：可能会作为商品图片中的颜色选择，关联id。比如：选择红色时，就显示红色的商品图片)
	 * attr_name: 属性a 的显示名称如：红色、黄色等等
	 * attr_image: 属性a 的显示小图标
	 *
	 *
	 * 	价格: attr_price_{$attr_1_id}_{$attr_2_id}
	 *	积分：attr_score_{$attr_1_id}_{$attr_2_id}
	 *	购买限制数量：attr_limit_num_{$attr_1_id}_{$attr_2_id}
	 */
	//echo 'aa';exit;
	$attrArray =$GLOBALS['cache']->get("m_goods_attr_".$id);
	if($attrArray === false )
	{

	$sql = "select id,deal_goods_type as goods_type,max_bought,buy_count,current_price as shop_price,return_score as score from ".DB_PREFIX."deal where id = ".intval($id);
	$goods = $GLOBALS['db']->getRow($sql);
	$attrArray = array();

	$attrArray['has_attr_1']=0; //0:无属性; 1:有属性
	$attrArray['has_attr_2']=0; //0:无属性; 1:有属性

	//只取前面2个属性
	$sql = "select id, name from ".DB_PREFIX."goods_type_attr where goods_type_id = ". intval($goods['goods_type'])." order by id asc limit 2";

	$attrlist = $GLOBALS['db']->getAll($sql); //getAllCached
	//print_r($attrlist); exit;
	for ($i = 1; $i <= count($attrlist); $i++){
		$attrArray["has_attr_{$i}"]=1;//无商品属性
		$attrArray["attr_title_{$i}"]=$attrlist[$i - 1]['name']; //商品属性名称如：颜色,尺码
		$attrArray["selected_attr_{$i}"] = 0; //默认选择的属性值id

		//商品属性值：如红色，黄色等等
		$attr_Array = array();
		$sql = "select id, goods_type_attr_id as attr_id, name,price from ".DB_PREFIX."deal_attr where goods_type_attr_id = ".intval($attrlist[$i - 1]['id'])." and deal_id = ".intval($id);
		//echo $sql."<br>";
		$attr_list = $GLOBALS['db']->getAll($sql);
		foreach($attr_list as $value){
			$attr_value = array();
			$attr_value['attr_id'] = $value['id'];//属性值id
			$attr_value['attr_name'] = $value['name']; //属性值名称如：红色，黄色
			$attr_value['attr_image'] = '';//属性值,对应图片

			$attr_value['attr_price'] = floatval($value['price']);//只对下面计算时有效,不作标准返回值
			$attr_value['attr_price_format'] = format_price(floatval($value['price']));
			$attr_Array[] = $attr_value;
		}
		
		if(!$attr_Array){
			$attrArray["true_has_attr_{$i}"]=0;//有商品属性
		}
		$attrArray["attr_{$i}"]=$attr_Array;
	}


	//价格: attr_price_{$attr_1_id}_{$attr_2_id}
	//积分：attr_score_{$attr_1_id}_{$attr_2_id}
	//库存：attr_limit_num_{$attr_1_id}_{$attr_2_id}

	$attr_1_2_value = array();
	if ($attrArray['has_attr_1'] == 1){
	//echo 'aaa';exit;
		for ($i = 1; $i <= count($attrArray['attr_1']); $i++){
			if ($attrArray['has_attr_2'] == 1){
				for ($j = 1; $j <= count($attrArray['attr_2']); $j++){
					$attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_".$attrArray['attr_2'][$j-1]['attr_id']] = $goods['shop_price'] + $attrArray['attr_1'][$i-1]['attr_price'] + $attrArray['attr_2'][$j-1]['attr_price'];
					$attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_".$attrArray['attr_2'][$j-1]['attr_id']."_format"] = format_price(floatval($attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_".$attrArray['attr_2'][$j-1]['attr_id']]));
					$attr_1_2_value["attr_score_".$attrArray['attr_1'][$i-1]['attr_id']."_".$attrArray['attr_2'][$j-1]['attr_id']] = $goods['score'];
					$attr_str = $attrArray['attr_1'][$i-1]['attr_name'].$attrArray['attr_2'][$j-1]['attr_name'];
					$row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."attr_stock where attr_str = '".$attr_str."'");
					if($row)
					{
						if($row['stock_cfg']>0)
						$max_bought = $row['stock_cfg'] - $row['buy_count'];
						else
						$max_bought = 999;
					}
					else
					{
						if($goods['max_bought']>0)
						$max_bought = $goods['max_bought'] - $goods['buy_count'];
						else
						$max_bought = 999;
					}
					$attr_1_2_value["attr_limit_num_".$attrArray['attr_1'][$i-1]['attr_id']."_".$attrArray['attr_2'][$j-1]['attr_id']] = $max_bought;
				}
			}else{
				$attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_0"] = $goods['shop_price'] + $attrArray['attr_1'][$i-1]['attr_price'];
				$attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_0_format"] = format_price(floatval($attr_1_2_value["attr_price_".$attrArray['attr_1'][$i-1]['attr_id']."_0"]));
				$attr_1_2_value["attr_score_".$attrArray['attr_1'][$i-1]['attr_id']."_0"] = $goods['score'];

				$attr_str = $attrArray['attr_1'][$i-1]['attr_name'];
				$row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."attr_stock where attr_str = '".$attr_str."'");
					if($row)
					{
						if($row['stock_cfg']>0)
						$max_bought = $row['stock_cfg'] - $row['buy_count'];
						else
						$max_bought = 999;
					}
					else
					{
						if($goods['max_bought']>0)
						$max_bought = $goods['max_bought'] - $goods['buy_count'];
						else
						$max_bought = 999;
					}

				$attr_1_2_value["attr_limit_num_".$attrArray['attr_1'][$i-1]['attr_id']."_0"] = $max_bought;
			}
		}
	}

	$attrArray['attr_1_2']= $attr_1_2_value;
	$GLOBALS['cache']->set("m_goods_attr_".$id,$attrArray);
	}

	return	$attrArray;
}

function emptyTag($string)
{
		if(empty($string))
			return "";

		$string = strip_tags(trim($string));
		$string = preg_replace("|&.+?;|",'',$string);

		return $string;
}

function get_abs_img_root($content)
{
	

	return str_replace("./public/",get_domain().APP_ROOT."/../public/",$content);
	//return str_replace('/mapi/','/',$str);
}
function get_abs_url_root($content)
{
	$content = str_replace("./",get_domain().APP_ROOT."/../",$content);	
	return $content;
}


function getGoodsArray($item){
	/**
	 * has_attr: 0:无属性; 1:有属性
	 * 有商品属性在要购买时，要选择属性后，才能购买(用户在列表中点：购买时，要再弹出一个：商品属性选择对话框)

	 * change_cart_request_server:
	 * 编辑购买车商品时，需要提交到服务器端，让服务器端通过一些判断返回一些信息回来(如：满多少钱，可以免运费等一些提示)
	 * 0:提交，1:不提交；
	 *
	 * num_unit: 单位

	 * limit_num: 库存数量
	 *
	 */
	$goods = array();


	$goods['city_name'] = "";
	$goods['goods_id']=$item['id'];
	$goods['title']=emptyTag($item['name']);
	//$goods['image']=get_abs_img_root(make_img($item['img'],0));
	$goods['image']=get_abs_img_root(get_spec_image($item['img'],160,160,0));
	//get_abs_img_root( get_spec_image($v['o_path'],160,0,0));
	$goods['buy_count']=$item['buy_count'];
	$goods['start_date']=$item['begin_time'];
	$goods['end_date']=$item['end_time'];
	$goods['ori_price']=round($item['origin_price'],2);
	$goods['cur_price']=round($item['current_price'],2);
	$goods['goods_brief'] = $item['brief'];
	$goods['ori_price_format']=format_price($goods['ori_price']);
	$goods['cur_price_format']=format_price($goods['cur_price']);

	$goods['discount']=$item['discount'];
	$sp_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$item['supplier_id']." and is_main = 1");
	$goods['address']= $sp_info['address'];  // 地址未完

	$goods['num_unit']= "";//$item['num_unit'];
	$goods['limit_num']=$item['max_bought'];
	$goods['goods_desc']= $item['description'];

	$goods['sp_detail'] = $sp_info['name']."<br />".$item['address']."<br />".$item['tel'];  //供应商信息

	$pattern = "/<img([^>]*)\/>/i";
	$replacement = "<img width=300 $1 />";


	$goods['goods_desc'] = preg_replace($pattern, $replacement, get_abs_img_root($goods['goods_desc']));


	$goods['saving_format']= $item['save_price_format'];

	if($goods['end_date']==0)
	$goods['less_time'] = "none"; //永不过期，无倒计时
	else
	$goods['less_time'] = $goods['end_date'] - get_gmtime();

	$goods['has_attr']=0;//has_attr: 0:无属性; 1:有属性
	
	if ($item['is_delivery']== 1){
		$goods['has_delivery'] = 1;
		$goods['has_mcod'] = 1;
	}else{
		$goods['has_delivery'] = 0;
		$goods['has_mcod'] = 0;
	}

	if ($goods['cart_type'] == 0){
		$goods['has_cart']=1;	//1:可以跟其它商品一起放入购物车购买；0：不能放入购物车，只能独立购买
	}else{
		$goods['has_cart']=0;
	}

	$goods['change_cart_request_server']=1;

	$goods['attr'] = getAttrArray($item['id']);
	$goods['distance'] = $item['distance'];
	
	if (intval($goods['attr']['true_has_attr_1']) > 0 || intval($goods['attr']['true_has_attr_2']) > 0){
		$goods['has_attr']=1;
	};

	return $goods;
}

function user_check($username_email,$pwd)
{
	//$username_email = addslashes($username_email);
	//$pwd = addslashes($pwd);
	if($username_email&&$pwd)
	{
		$sql = "select *,id as uid from ".DB_PREFIX."user where (user_name='".$username_email."' or email = '".$username_email."') and is_delete = 0";
		$user_info = $GLOBALS['db']->getRow($sql);
		
		$is_use_pass = false;
		if (strlen($pwd) != 32){
			if($user_info['user_pwd']==md5($pwd.$user_info['code']) || $user_info['user_pwd']==md5($pwd)){
				$is_use_pass = true;
			}
		}
		else{
			if($user_info['user_pwd']==$pwd){
				$is_use_pass = true;
			}
		}
		if($is_use_pass)
		{
			return $user_info;
		}
		else
			return null;
	}
	else
	{
		return NULL;
	}
}

/**每个数据项的结构, 结构：
goods_id(int)  //购物车商品的商品ID  $_POST['cartdata'][][goods_id]
num(int)		  //购物车购买的数量   $_POST['cartdata'][][num]
attr_id_a(string)    //购买属性类型1的名称标识。 根据系统不同， 可以是属性类型ID，或名称(如 颜色，尺码)  $_POST['cartdata'][][attr_id_a]
attr_id_b(string)    //购买属性类型2的名称标识。 根据系统不同， 可以是属性类型ID，或名称(如 颜色，尺码)  $_POST['cartdata'][][attr_id_b]
attr_value_a(string) //购买属性1的名称标识。 根据系统不同， 可以是属性类型ID，或名称(如 红色,大码) $_POST['cartdata'][][attr_value_a]
attr_value_b(string) //购买属性2的名称标识。 根据系统不同， 可以是属性类型ID，或名称(如 红色,大码) $_POST['cartdata'][][attr_value_b]
*/
function insertCartData($user_id,$session_id,$cartdata)
{
	$GLOBALS['user_info']['id'] = $user_id;
	require APP_ROOT_PATH.'system/libs/deal.php';
	require APP_ROOT_PATH.'app/Lib/deal.php';

	$res = array('status'=>0,'info'=>'');
	$score_enough=true;
	foreach($cartdata as $key=>$cart)
	{
		//加入每个
		$id = intval($cart['goods_id']);
		$check = check_deal_time($id);
		if($check['status'] == 0)
		{
			$res['info'] .= $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
			continue;
		}

		$check = check_deal_number($id,$cart['num']);
		if($check['status']==0)
		{
			$res['info'] .= $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
			continue;
		}

		$attr_setting_str = $cart['attr_value_a'].$cart['attr_value_b'];

		if($attr_setting_str!='')
		{

			$check = check_deal_number_attr($cart['goods_id'],$attr_setting_str,$cart['num']);
			if($check['status']==0)
			{
				$res['info'] .= $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']].$check['attr'];
				continue;
			}
		}

		$deal_info = load_auto_cache("cache_deal_cart",array("id"=>$id));
		
		
		if($deal_info['return_score']<0)
		{
				//需要积分兑换
				$user_score = intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".$user_id));
				if($user_score < abs(intval($deal_info['return_score'])*$cart['num']))
				{			
					$score_enough = false;							
				}
		}
		

		if(intval($cart['attr_id_a'])>0&&intval($cart['attr_id_b'])>0)
		$attr_ids = array(intval($cart['attr_id_a']),intval($cart['attr_id_b']));
		elseif(intval($cart['attr_id_a'])>0)
		$attr_ids = array(intval($cart['attr_id_a']));



		//加入购物车处理，有提交属性， 或无属性时
		$attr_str = '0';
		$attr_name = '';
		$attr_name_str = '';
		if(count($attr_ids)>0)
		{
			$attr_str = implode(",",$attr_ids);
			$attr_names = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_attr where id in(".$attr_str.")");
			$attr_name = '';
			foreach($attr_names as $attr)
			{
				$attr_name .=$attr['name'].",";
				$attr_name_str.=$attr['name'];
			}
			$attr_name = substr($attr_name,0,-1);
		}
		$verify_code = md5($id."_".$attr_str);
		$cart_item = array();

			$attr_price = $GLOBALS['db']->getOne("select sum(price) from ".DB_PREFIX."deal_attr where id in($attr_str)");
			$cart_item['session_id'] = $session_id;
			$cart_item['user_id'] = intval($user_id);
			$cart_item['deal_id'] = $id;
			//属性
			if($attr_name != '')
			{
				$cart_item['name'] = $deal_info['name']." [".$attr_name."]";
				$cart_item['sub_name'] = $deal_info['sub_name']." [".$attr_name."]";
			}
			else
			{
				$cart_item['name'] = $deal_info['name'];
				$cart_item['sub_name'] = $deal_info['sub_name'];
			}
			$cart_item['name'] = addslashes($cart_item['name']);
			$cart_item['sub_name'] = addslashes($cart_item['sub_name']);
			$cart_item['attr'] = $attr_str;
			$cart_item['unit_price'] = $deal_info['current_price'] + $attr_price;
			$cart_item['number'] = $cart['num'];
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['number'];
			$cart_item['verify_code'] = $verify_code;
			$cart_item['create_time'] = get_gmtime();
			$cart_item['update_time'] = get_gmtime();
			$cart_item['return_score'] = $deal_info['return_score'];
			$cart_item['return_total_score'] = $deal_info['return_score'] * $cart_item['number'];
			$cart_item['return_money'] = $deal_info['return_money'];
			$cart_item['return_total_money'] = $deal_info['return_money'] * $cart_item['number'];
			$cart_item['buy_type']	=	$deal_info['buy_type'];
			$cart_item['supplier_id']	=	$deal_info['supplier_id'];
			$cart_item['attr_str'] = $attr_name_str;
			$cart_list[] = $cart_item;
		//end
	}
	if(!$score_enough)
	{
		$res['info'].= " ".$GLOBALS['lang']['NOT_ENOUGH_SCORE'];	
	}
	$res['data'] = $cart_list;
	$res['status'] = 1;
	return $res;
}



function getUserAddr($user_id,$all){
	$sql = "select uc.*, r1.name as r1_name, r2.name as r2_name, r3.name as r3_name, r4.name as r4_name from ".DB_PREFIX."user_consignee uc ".
		   "left outer join ".DB_PREFIX."delivery_region as r1 on r1.id = uc.region_lv1 ".
		   "left outer join ".DB_PREFIX."delivery_region as r2 on r2.id = uc.region_lv2 ".
		   "left outer join ".DB_PREFIX."delivery_region as r3 on r3.id = uc.region_lv3 ".
		   "left outer join ".DB_PREFIX."delivery_region as r4 on r4.id = uc.region_lv4 ".
		   "where uc.user_id = ".intval($user_id);
	if ($all){
		$list = $GLOBALS['db']->getAll($sql);
		$addr_list = array();
		foreach($list as $item)
		{
			$addr_list[] = getUserAddrItem($item);
		}
		return $addr_list;
	}else{
		$sql .= " limit 1";
		$addr = $GLOBALS['db']->getRow($sql);
		return getUserAddrItem($addr);
	}
}

function getUserAddrItem($item){
	$addr = array();
	$addr['id'] = $item['id'];//联系人姓名
	$addr['consignee'] = $item['consignee'];//联系人姓名

	//不显示国家
	$addr['delivery'] = $item['r1_name'].$item['r2_name'].$item['r3_name'].$item['r4_name'];

	$addr['region_lv1'] = $item['region_lv1'];//国家
	$addr['region_lv2'] = $item['region_lv2'];//省
	$addr['region_lv3'] = $item['region_lv3'];//城市
	$addr['region_lv4'] = $item['region_lv4'];//地区/县

	$addr['delivery_detail'] = $item['address'];//详细地址
	$addr['phone'] = $item['mobile'];//手机号码
	$addr['postcode'] = $item['zip'];//邮编

	return $addr;
}


//初始化下单时的订单参数
function init_order_parm($MConfig){
	$order_parm = array();

	$order_parm['has_delivery_time'] = intval($MConfig['has_delivery_time']);//有配送日期选择
	$order_parm['has_ecv'] = intval($MConfig['has_ecv']);//有优惠券
	$order_parm['has_moblie'] = intval($MConfig['has_moblie']);//有手机号码
	$order_parm['has_invoice'] = intval($MConfig['has_invoice']);//有发票
	$order_parm['has_message'] = intval($MConfig['has_message']);//有留言框
	$order_parm['has_delivery'] = 0;//1：有配送地区选择项；0：无

	$order_parm['select_payment_id'] = $MConfig['select_payment_id'];//默认支付方式
	$order_parm['select_delivery_time_id'] = $MConfig['select_delivery_time_id'];//默认配送日期

	/**支付方式列表
	 * id: 键值
	* name: 名称
	* code: malipay,支付宝;mtenpay,财付通;mcod,货到付款
	* has_calc: 选择该支付方式，需要重新返回服务器，计算购物车价格; 0:不需要，1:需要

	$payment_list = array();
	$payment_list[] = array("id"=>19,"code"=>"malipay","name"=>"支付宝","has_calc"=>0);
	//$payment_list[] = array("id"=>2,"code"=>"mtenpay","name"=>"财付通","has_calc"=>0);
	$payment_list[] = array("id"=>20,"code"=>"mcod","name"=>"现金支付","has_calc"=>0);
	*/
	$order_parm['payment_list'] = $MConfig['payment_list'];

	/**配送日期选择
	 * id: 键值
	* name: 名称

	$delivery_time_list = array();
	$delivery_time_list[] = array("id"=>1,"name"=>"周末");
	$delivery_time_list[] = array("id"=>2,"name"=>"都可以");
	*/
	$order_parm['delivery_time_list'] = $MConfig['delivery_time_list'];
	$order_parm['delivery_list'] = $MConfig['delivery_list'];
	$order_parm['invoice_list'] = $MConfig['invoice_list'];
	
	return $order_parm;
}

function getFeeItem($cart_total){
	$feeinfo[] = array("item"=>"应付总额","value"=>format_price($cart_total['pay_total_price']));


	if ($cart_total['return_total_score'] <> 0){
		if($cart_total['return_total_score']>0)
		{
			$score = "增加".format_score($cart_total['return_total_score']);
		}
		else
		{
			$score = "消费".format_score(abs($cart_total['return_total_score']));
		}
		$feeinfo[] = array("item"=>"积分变动","value"=>$score);
	}

	if ($cart_total['total_price'] > 0){
		$feeinfo[] = array("item"=>"商品总金额","value"=>format_price($cart_total['total_price']));
	}

	if ($cart_total['delivery_fee'] <> 0){
		$feeinfo[] = array("item"=>"运费","value"=>format_price($cart_total['delivery_fee']));
	}

	if ($cart_total['account_money'] <> 0){
		$feeinfo[] = array("item"=>"余额支付","value"=>format_price($cart_total['account_money']));
	}

	if ($cart_total['ecv_money'] <> 0){
		$feeinfo[] = array("item"=>"代金券支付","value"=>format_price($cart_total['ecv_money']));
	}

	if ($cart_total['paid_account_money'] <> 0 || $cart_total['paid_ecv_money'] <> 0){
		$feeinfo[] = array("item"=>"已收金额","value"=>format_price($cart_total['paid_account_money']+$cart_total['paid_ecv_money']));
	}

	$feeinfo[] = array("item"=>"应付金额","value"=>format_price($cart_total['pay_price']));

	return $feeinfo;
}

function get_order_goods($order_info)
{

	/**
	id(int)		//订单ID
	sn(string)	//订单序列号
	create_time(int)		//下单时间
	create_time_format(string)	//下单时间格式化
	total_money(float)		//订单总金额
	money(float)		//剩余应付金额
	total_money_format(string)  //订单总金额格式化
	money_format(string)		//剩余应付金额格式化
	status(string)		//订单状态(包含 付款状态与配送状态的文字描述)
	num(int)		//订单商品总量
	orderGoods(Array<HashMap>)		//订单商品
	HashMap结构，订单商品结构

		id(int)		//订单商品数据表ID
		goods_id(int)		//商品原ID
		name(string)		//商品名称
		num(int)			//商品数量
		price(float)		//单价
		price_format(string)		//格式化单价
		total_money(float)	//商品总价
		total_money_format(string)	//商品总价格式化
		image(string)		//商品缩略图片
		attr_content(string)	//商品属性描述
	*/
	$data['id'] = $order_info['id'];
	$data['sn'] = $order_info['order_sn'];
	$data['create_time'] = $order_info['create_time'];
	$data['create_time_format'] = to_date($order_info['create_time']);
	$data['total_money'] = $order_info['total_price'];
	$data['money'] = $order_info['total_price'] - $order_info['pay_amount'];
	$data['total_money_format'] = format_price($order_info['total_price']);
	$data['money_format'] = format_price($data['money']);
	$data['status'] = "";

	if($order_info['pay_status']==0)
	$data['status'].="未付款";
	elseif($order_info['pay_status']==1)
	$data['status'].="部份付款";
	else
	$data['status'].="全部付款";

	if($order_info['delivery_status']==0)
	$data['delivery_status'].="未发货";
	elseif($order_info['delivery_status']==2)
	$data['status'].="已发货";
	else
	$data['status'].="";

	$data['num'] =  $GLOBALS['db']->getOne("select sum(number) from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']);
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_info['id']);
	foreach($goods_list as $order_goods)
	{
		$goods_item = array();
		$goods_item['id'] = $order_goods['id'];
		$goods_item['goods_id'] = $order_goods['deal_id'];
		$goods_item['name'] = $order_goods['name'];
		$goods_item['num'] = $order_goods['number'];
		$goods_item['price'] = $order_goods['unit_price'];
		$goods_item['price_format'] =format_price($order_goods['unit_price']);
		$goods_item['total_money'] = $order_goods['total_price'];
		$goods_item['total_money_format'] = format_price($order_goods['total_price']);
		if(preg_match("/\[([^\]]+)\]/i",$order_goods['name'],$matches))
		$goods_item['attr_content'] = $matches[1];
		else
		$goods_item['attr_content'] = "";
		$image = $GLOBALS['db']->getOne("select img from ".DB_PREFIX."deal where id = ".$goods_item['goods_id']);

		//$goods_item['image'] = get_abs_img_root(make_img($image,0));
		$goods_item['image']=get_abs_img_root(get_spec_image($image,160,160,0));
		$data['orderGoods'][] = $goods_item;
	}
	return $data;

}

/**
* 获取指定时间与当前时间的时间间隔
*
* @access  public
* @param   integer      $time
*
* @return  string
*/
function getBeforeTimelag($time)
{
	if($time == 0)
	return "";

	static $today_time = NULL,
	$before_lang = NULL,
	$beforeday_lang = NULL,
	$today_lang = NULL,
	$yesterday_lang = NULL,
	$hours_lang = NULL,
	$minutes_lang = NULL,
	$months_lang = NULL,
	$date_lang = NULL,
	$sdate = 86400;

	if($today_time === NULL)
	{
		$today_time = get_gmtime();
		$before_lang = '前';//lang('time','before');
		$beforeday_lang = '前天';//lang('time','beforeday');
		$today_lang = '今天';//lang('time','today');
		$yesterday_lang = '昨天';//lang('time','yesterday');
		$hours_lang = '小时';//lang('time','hours');
		$minutes_lang = '分钟';//lang('time','minutes');
		$months_lang = '月';//lang('time','months');
		$date_lang = '日';//lang('time','date');
	}

	$now_day = to_timespan(to_date($today_time,"Y-m-d")); //今天零点时间
	$pub_day = to_timespan(to_date($time,"Y-m-d")); //发布期零点时间

	$timelag = $now_day - $pub_day;

	$year_time = to_date($time,'Y');
	$today_year = to_date($today_time,'Y');

	if($year_time < $today_year)
		return to_date($time,'Y:m:d H:i');

	$timelag_str = to_date($time,' H:i');

	$day_time = 0;
	if($timelag / $sdate >= 1)
	{
		$day_time = floor($timelag / $sdate);
		$timelag = $timelag % $sdate;
	}

	switch($day_time)
	{
		case '0':
			$timelag_str = $today_lang.$timelag_str;
			break;

		case '1':
			$timelag_str = $yesterday_lang.$timelag_str;
			break;

		case '2':
			$timelag_str = $beforeday_lang.$timelag_str;
			break;

		default:
			$timelag_str = to_date($time,'m'.$months_lang.'d'.$date_lang.' H:i');
		break;
	}
	return $timelag_str;
}
//优惠券信息
function m_youhuiLogItem($item){
	$is_sc = intval($item['is_sc']);
	if ($is_sc > 0) $is_sc = 1;//1:已收藏; 0:未收藏

	if (intval($item['begin_time']) > 0 && intval($item['end_time'])){
		$days = round(($item['end_time']-$item['begin_time'])/3600/24);
		if ($days < 0){
			$ycq = to_date($item['begin_time'],'Y-m-d').'至'.to_date($item['end_time'],'Y-m-d').',已过期';
		}else{
			$ycq = to_date($item['begin_time'],'Y-m-d').'至'.to_date($item['end_time'],'Y-m-d').',还有'.$days.'天';
		}
	}else{
		$ycq = '';
	}

	return array("id"=>$item['id'],
									"title"=>$item['title'],
									"logo"=> get_abs_img_root($item['image_1']),
									//"logo_1"	=>	get_abs_img_root($item['image_2']),
									//"logo_2"	=>	get_abs_img_root($item['image_3']),
                                    //"image_3_w"=>intval($item['image_3_w']),
                                    //"image_3_h"=>intval($item['image_3_h']),
											"merchant_logo"=> get_abs_img_root($item['merchant_logo']),
											"create_time"=>$item['create_time'],
											"create_time_format"=>getBeforeTimelag($item['create_time']),
											"yl_create_time"=>$item['yl_create_time'],
											"yl_create_time_format"=>getBeforeTimelag($item['yl_create_time']),
											"yl_confirm_time"=>$item['yl_confirm_time'],
											"yl_confirm_time_format"=>getBeforeTimelag($item['yl_confirm_time']),
											"yl_sn"=>$item['yl_sn'],
											//"xpoint"=>$item['xpoint'],
											//"ypoint"=>$item['ypoint'],
											//"address"=>$item['api_address'],
											"content"=>preg_replace('/\.\//i',"http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].'/',$item['content']),
									"is_sc"=>$is_sc,
									'info'=>'您的优惠券验证码为:'.$item['yl_sn'],
								//	"distance" => $item['distance'],
									//"comment_count"=>intval($item['comment_count']),
									//"merchant_id"=>intval($item['merchant_id']),
									//"begin_time_format"=>to_date($item['begin_time'],'Y-m-d'),
									//"end_time_format"=>to_date($item['end_time'],'Y-m-d'),
									//"ycq"=>$ycq,
									//"adv_url"=>$item['url'],
									//"city_name"=>$item['city_name']

	);
}
function m_youhuiItem($item){
	$is_sc = intval($item['is_sc']);
	if ($is_sc > 0) $is_sc = 1;//1:已收藏; 0:未收藏

	if (intval($item['begin_time']) > 0 && intval($item['end_time'])){
		$days = round(($item['end_time']-$item['begin_time'])/3600/24);
		if ($days < 0){
			$ycq = to_date($item['begin_time'],'Y-m-d').'至'.to_date($item['end_time'],'Y-m-d').',已过期';
		}else{
			$ycq = to_date($item['begin_time'],'Y-m-d').'至'.to_date($item['end_time'],'Y-m-d').',还有'.$days.'天';
		}
	}else{
		$ycq = '';
	}
	$logo=get_spec_image($item['image_1'],$width=160,$height=0,$gen=0,$is_preview=true);
	$merchant_logo=get_spec_image($item['merchant_logo'],$width=160,$height=0,$gen=0,$is_preview=true);
	return array("id"=>$item['id'],
									"title"=>$item['title'],
									"logo"=> get_abs_img_root(get_spec_image($item['image_1'],160,0)),
									"logo_1"	=>	get_abs_img_root($item['image_2']),
									"logo_2"	=>	get_abs_img_root($item['image_3']),
                                    "image_3_w"=>intval($item['image_3_w']),
                                    "image_3_h"=>intval($item['image_3_h']),
											"merchant_logo"=> get_abs_img_root(get_spec_image($item['merchant_logo'],160,0)),
											"create_time"=>$item['create_time'],
											"create_time_format"=>getBeforeTimelag($item['create_time']),
											"xpoint"=>$item['xpoint'],
											"ypoint"=>$item['ypoint'],
											"address"=>$item['api_address'],
											"content"=>$item['content'],
									"is_sc"=>$is_sc,
									"distance" => round($item['distance']),
									"comment_count"=>intval($item['comment_count']),
									"merchant_id"=>intval($item['merchant_id']),
									"begin_time_format"=>to_date($item['begin_time'],'Y-m-d'),
									"end_time_format"=>to_date($item['end_time'],'Y-m-d'),
									"ycq"=>$ycq,
									"adv_url"=>$item['url'],
									"city_name"=>$item['city_name']

	);
}

function m_merchantItem($item){

	$is_dy = intval($item['is_dy']);
	if ($is_dy > 0) $is_dy = 1;
	
	$logo=get_spec_image($item['logo'],$width=160,$height=0,$gen=0,$is_preview=true);
	return array("id"=>$item['id'],
								   "name"=>$item['name'],
									"logo"=> get_abs_img_root($logo),
									"xpoint"=>$item['xpoint'],
									"ypoint"=>$item['ypoint'],
									"api_address"=>$item['api_address'],
									"address"	=>	$item['api_address'],
									"tel"=>$item['tel'],
									"is_dy"=>$is_dy,
									"city_name"=>$item['city_name'],
									"comment_count"=>intval($item['comment_count']),
									"brand_id"=>intval($item['brand_id']),
									"distance"=>round($item['distance']),
									"brief"	=>	get_abs_url_root($item['brief']),

	);
}


function m_adv_youhui($city_id){

return array();

}

function get_parse_expres($cnt)
{
	$expression_replace_array = $GLOBALS['cache']->get("MOBILE_EXPRESSION_REPLACE_ARRAY");
	if($expression_replace_array===false)
	{
		require_once APP_ROOT_PATH."system/utils/es_image.php";
		$result = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."expression");
		foreach($result as $item)
		{
			$img_info = es_image::getImageInfo(APP_ROOT_PATH."public/expression/".$item['type']."/".$item['filename']);
			$expression_replace_array[$item['emotion']] = array(
				"key" => $item['emotion'],
				"value" =>  get_abs_img_root("./public/expression/".$item['type']."/".$item['filename']),
				"width"	=>	$img_info['width'],
				"height"	=>	$img_info['height']
			);		
		}
		$GLOBALS['cache']->set("MOBILE_EXPRESSION_REPLACE_ARRAY",$expression_replace_array);
	}
	$result = array();
	if(preg_match_all("/\[[^\]]+\]/i",$cnt,$matches))
	{
		$matches[0] = array_unique($matches[0]);
		foreach($matches[0] as $key)
		{
			$result[] = $expression_replace_array[$key];
		}		
	}
	$result[] = $expression_replace_array['[爱心]'];
	return $result;	
}

function get_parse_user($cnt)
{
	$result = array();
	$name_count = preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$cnt,$name_matches);
	if($name_count > 0)
	{
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $k=>$user_name)
		{				
			$uinfo = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user where user_name = '".$user_name."' and is_effect = 1 and is_delete = 0");			
			if($uinfo)
			{
				$result[] = array("key"=>$user_name,"value"=>$uinfo['id']);					
			}
		}
			
	}
	return $result;		
}

function get_muser_avatar($id,$type)
{
	$uid = sprintf("%09d", $id);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir1.'/'.$dir2.'/'.$dir3;
				
	$id = str_pad($id, 2, "0", STR_PAD_LEFT); 
	$id = substr($id,-2);
	$avatar_file = "./public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	$avatar_check_file = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";

	if(file_exists($avatar_check_file))	
	return $avatar_file;
	else
	return "./public/avatar/noavatar_".$type.".gif";
}

function m_get_topic_reply($topic_id,$page)
{
	if($page==0)
	$page = 1;
	$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;		
			
	$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_reply where topic_id = ".$topic_id." and is_effect = 1 and is_delete = 0 order by create_time asc limit ".$limit);		
	$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_reply where topic_id = ".$topic_id." and is_effect = 1 and is_delete = 0");
	$reply_list = array();
	foreach($list as $k=>$v)
	{
		$reply_list[$k]['comment_id'] = $v['id'];
		$reply_list[$k]['share_id'] = $v['topic_id'];
		$reply_list[$k]['uid'] = $v['user_id'];
		$reply_list[$k]['parent_id'] = $v['reply_id'];
		$reply_list[$k]['content'] = $v['content'];
		$reply_list[$k]['create_time'] = $v['create_time'];
		$reply_list[$k]['user_name'] = $v['user_name'];
		$reply_list[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['user_id'],"big"));
		$reply_list[$k]['time'] = pass_date($v['create_time']);
		$reply_list[$k]['parse_expres'] = get_parse_expres($v['content']);
        $reply_list[$k]['parse_user'] = get_parse_user($v['content']);
		
	}	
	$page_info = array("page"=>$page,"page_total"=>ceil($count/PAGE_SIZE));
	return array("list"=>$reply_list,"page"=>$page_info);	
}


function m_get_event_reply($event_id,$page)
{
	if($page==0)
	$page = 1;
	$limit = (($page-1)*PAGE_SIZE).",".PAGE_SIZE;		
	require_once APP_ROOT_PATH."app/Lib/message.php";
	$res = get_message_list_shop($limit," rel_table='event' and rel_id = ".$event_id." and is_effect = 1");			
	$list = $res['list'];
	$count = $res['count'];
	$reply_list = array(); 
	foreach($list as $k=>$v)
	{
		$reply_list[$k]['content'] = $v['content'];
		$reply_list[$k]['create_time'] = $v['create_time'];
		$reply_list[$k]['user_name'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
		$reply_list[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['user_id'],"big"));
		$reply_list[$k]['time'] = pass_date($v['create_time']);
		$reply_list[$k]['parse_expres'] = get_parse_expres($v['content']);
        $reply_list[$k]['parse_user'] = get_parse_user($v['content']);        
		$reply_list[$k]['user_id'] =$v['user_id'];
	}	
	$page_info = array("page"=>$page,"page_total"=>ceil($count/PAGE_SIZE));
	return array("list"=>$reply_list,"page"=>$page_info);	
}

function m_get_topic_fav($topic_id)
{
	$list = $GLOBALS['db']->getAll("select user_id as uid,user_name from ".DB_PREFIX."topic where fav_id = ".$topic_id." order by create_time desc limit 20");
	foreach($list as $k=>$v)
	{
		$list[$k]['user_avatar'] = get_abs_img_root(get_muser_avatar($v['uid'],"big"));
	}
	return $list;
}

function m_get_topic_img($topic)
{
	$images = $GLOBALS['db']->getAll("select path,o_path,width,height,id from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
	$image_list = array();
	foreach($images as $k=>$v)
	{
		$image_list[$k]['share_id'] = $topic['id'];
		$image_list[$k]['id'] = $v['id'];
		$image_list[$k]['img'] = get_abs_img_root($v['o_path']);
		$image_list[$k]['small_img'] = get_abs_img_root($v['o_path']);
		$image_list[$k]['type'] = "m";
		$image_list[$k]['img_width'] = $v['width'];
		$image_list[$k]['img_height'] = $v['height'];
		if($k==0)
		{
			$group = $topic['topic_group'];
			if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
			{
				require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
				$class_name = $group."_fetch_topic";
				if(class_exists($class_name))
				{
					$fetch_obj = new $class_name;
					$topic = $fetch_obj->decode_mobile($topic);
					$image_list[$k]['type'] = $topic['type'];
					$image_list[$k]['data_id'] = $topic['group_data']['data']['id'];
					$image_list[$k]['data_name'] = $topic['group_data']['data']['name'];
					$image_list[$k]['price_format'] =  "￥".round($topic['group_data']['data']['current_price'],2);					
				}
			}	
		}
	}
	return $image_list;
}

function m_get_topic_list_img($topic)
{
	$images = $GLOBALS['db']->getAll("select path,o_path,width,height,id from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']." limit 3");
	$image_list = array();
	foreach($images as $k=>$v)
	{		
		$image_list[$k]['share_id'] = $topic['id'];
		$image_list[$k]['id'] = $v['id'];
		$image_list[$k]['img'] = get_abs_img_root($v['o_path']);
		$image_list[$k]['small_img'] = get_abs_img_root( get_spec_image($v['o_path'],160,0,0));
		$image_list[$k]['type'] = "m";
		$image_list[$k]['img_width'] = $v['width'];
		$image_list[$k]['img_height'] = $v['height'];
		$image_list[$k]['width'] = 160;
		if($k==0)
		{
			$group = $topic['topic_group'];
			if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
			{
				require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
				$class_name = $group."_fetch_topic";
				if(class_exists($class_name))
				{
					$fetch_obj = new $class_name;
					$topic = $fetch_obj->decode_mobile($topic);
					$image_list[$k]['type'] = $topic['type'];
					$image_list[$k]['data_id'] = $topic['group_data']['data']['id'];
					$image_list[$k]['data_name'] = $topic['group_data']['data']['name'];
					$image_list[$k]['price_format'] =  "￥".round($topic['group_data']['data']['current_price'],2);					
				}
			}	
		}
		
	}
	return $image_list;
}

function m_get_topic_item($topic)
{
		$share_item['share_id'] = $topic['id'];
		$share_item['uid'] = $topic['user_id'];
		$share_item['user_name'] = $topic['user_name'];
		if($topic['fav_id']>0)
		{
			$share_item['content'] = "我喜欢这个，谢谢你的分享[爱心]";	
		}
		else
		$share_item['content'] = $topic['content'];	
		$share_item['share_content'] =  msubstr($topic['content']).get_domain().str_replace("mapi/","",url("shop","topic",array("id"=>$topic['id']))) ;	
		$share_item['collect_count'] = $topic['fav_count'];	
		$share_item['comment_count'] = $topic['reply_count'];
		$share_item['relay_count'] = $topic['relay_count'];
		$share_item['click_count'] = $topic['click_count'];
		$share_item['title'] = $topic['title'];
		$share_item['type'] = 'default';
        $share_item['share_data'] ='photo';
        if($topic['source_type']==0)
        $source_name = "来自".app_conf("SHOP_TITLE").$topic['source_name'];
        else
        $source_name = "来自".$topic['source_name'];
        $share_item['source'] = $source_name;
        $share_item['time'] =  pass_date($topic['create_time']);
        $share_item['parse_expres'] = get_parse_expres($topic['content']);
        $share_item['parse_user'] = get_parse_user($topic['content']);
        $share_item['user_avatar'] =	get_abs_img_root(get_muser_avatar($topic['user_id'],"big"));
        $share_item['imgs'] = m_get_topic_list_img($topic);
        if($topic['fav_id']>0||$topic['relay_id']>0)
        $share_item['is_relay'] = 1;
        
        $share_item['user'] = array("uid"=>$topic['user_id'],"user_name"=>$topic['user_name'],"user_avatar"=>$share_item['user_avatar']);
        
        return $share_item;
	
}


function m_search_event_list($limit, $cate_id=0, $city_id=0, $where='',$orderby = '',$field_append="")
{		

			
			$count_sql = "select count(*) from ".DB_PREFIX."event " ;
			$sql = "select * $field_append from ".DB_PREFIX."event ";

	
			$count_sql .= " where is_effect = 1 ";
			$sql .= " where is_effect = 1  ";
			
			if($cate_id>0)
			{
				
				$sql .= " and cate_id = ".$cate_id." ";
				$count_sql .= " and cate_id = ".$cate_id." ";
			}
				
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}

			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
				}
			}
			
			$merchant_id = intval($GLOBALS['request']['merchant_id']);
			if($merchant_id>0)
			{
				$event_ids = $GLOBALS['db']->getOne("select group_concat(event_id) from ".DB_PREFIX."event_location_link where location_id = ".$merchant_id);
				if($event_ids)
				{
					$sql .= " and id in (".$event_ids.")";
					$count_sql .= " and id in (".$event_ids.")";
				}
				else
				{
					$sql .= " and id = 0 ";
					$count_sql .= " and id = 0 ";
				}
			}
			
			if($where != '')
			{
				$sql.=" and ".$where;
				$count_sql.=" and ".$where;
			}
			
			if($orderby=='')
			$sql.=" order by is_recommend desc,sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;
						
			$events = $GLOBALS['db']->getAll($sql);				
			$events_count = $GLOBALS['db']->getOne($count_sql);
			
			$res = array('list'=>$events,'count'=>$events_count);	
		
		return $res;
}



/**
 * 获取正在团购的产品列表
 */
function m_get_deal_list($limit,$cate_id=0,$city_id=0, $type=array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE), $where='',$orderby = '' , $quan_id=0,$field_append="")
{		
		
		$time = get_gmtime();
		$time_condition = ' and is_shop = 0 and ( 1<>1 ';
		if(in_array(DEAL_ONLINE,$type))
		{			
			//进行中的团购
			$time_condition .= " or ((".$time.">= begin_time or begin_time = 0) and (".$time."< end_time or end_time = 0) and buy_status <> 2) ";
		}
		
		if(in_array(DEAL_HISTORY,$type))
		{
			//往期团购
			$time_condition .= " or ((".$time.">=end_time and end_time <> 0) or buy_status = 2) ";
		}
		if(in_array(DEAL_NOTICE,$type))
		{			
			//预告
			$time_condition .= " or ((".$time." < begin_time and begin_time <> 0 and notice = 1)) ";
		}
		
		$time_condition .= ')';
		
			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			$sql = "select * $field_append from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}
			
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
				}
			}
		
		if($quan_id > 0)
		{
			$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$quan_id));
			$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
			$unicode_quans = array();
			foreach($quan_list as $k=>$v){
				$unicode_quans[] = str_to_unicode_string($v['name']);
			}
			$kw_unicode = implode(" ", $unicode_quans);
			$sql .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			$count_sql .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
		}
		
			$merchant_id = intval($GLOBALS['request']['merchant_id']);
			if($merchant_id>0)
			{
				$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_location_link where location_id = ".$merchant_id);
				if($deal_ids)
				{
					$sql .= " and id in (".$deal_ids.")";
					$count_sql .= " and id in (".$deal_ids.")";
				}
				else
				{
					$sql .= " and id = 0 ";
					$count_sql .= " and id = 0 ";
				}
			}
		
		if($where != '')
		{
			$sql.=" and ".$where;
			$count_sql.=" and ".$where;
		}
		
		if($orderby=='')
		$sql.=" order by sort desc limit ".$limit;
		else
		$sql.=" order by ".$orderby." limit ".$limit;
		

		$deals = $GLOBALS['db']->getAll($sql);		
		$deals_count = $GLOBALS['db']->getOne($count_sql);
		
 		if($deals)
		{
			foreach($deals as $k=>$deal)
			{
				//团购图片集
				$img_list = array();
				$img_list[] = array('img'=>$deal['img']);
				$deal['image_list'] = $img_list;
			
				//格式化数据
				$deal['begin_time_format'] = to_date($deal['begin_time']);
				$deal['end_time_format'] = to_date($deal['end_time']);
				$deal['origin_price_format'] = format_price($deal['origin_price']);
				$deal['current_price_format'] = format_price($deal['current_price']);
				$deal['success_time_format']  = to_date($deal['success_time']);
				
				if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
				$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
				else
				$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
				{
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);					
				}
				
				$deal['discount'] = round($deal['discount'],2);
				
				if($deal['uname']!='')
				$durl = url("tuan","deal",array("id"=>$deal['uname']));
				else
				$durl = url("tuan","deal",array("id"=>$deal['id']));				
				$deal['share_url'] = get_domain().$durl;
				
				
				if($GLOBALS['user_info'])
					{
						if(app_conf("URL_MODEL")==0)
						{
							$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
						}
						else
						{
							$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
						}
				}	
			
				

				$deal['save_price_format'] = format_price($deal['save_price']);
				if($deal['uname']!='')
				$durl = url("tuan","deal",array("id"=>$deal['uname']));
				else
				$durl = url("tuan","deal",array("id"=>$deal['id']));
				$deal['url'] = $durl;
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				//查询抽奖号
				if($deal['is_lottery']==1)
				$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
				$deals[$k] = $deal;
			}
		}				
		return array('list'=>$deals,'count'=>$deals_count);	
}



function m_search_youhui_list($limit, $cate_id=0, $where='',$orderby = '',$city_id=0,$field_append="")
{		
	
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			
			$count_sql = "select count(*) from ".DB_PREFIX."deal " ;
			$sql = "select * $field_append from ".DB_PREFIX."deal ";

			
			$time = get_gmtime();
			$time_condition = '  and (end_time = 0 or end_time > '.$time.') ';
	
			$count_sql .= " where is_effect = 1 and is_delete = 0 and is_shop = 2 ".$time_condition;
			$sql .= " where is_effect = 1 and is_delete = 0 and is_shop = 2 ".$time_condition;
			
			if($cate_id>0)
			{
				$ids =load_auto_cache("deal_sub_cate_ids",array("cate_id"=>$cate_id));
				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}			

			if($city_id>0)
			{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
				}
			}
			
			$merchant_id = intval($GLOBALS['request']['merchant_id']);
			if($merchant_id>0)
			{
				$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_location_link where location_id = ".$merchant_id);
				if($deal_ids)
				{
					$sql .= " and id in (".$deal_ids.")";
					$count_sql .= " and id in (".$deal_ids.")";
				}
				else
				{
					$sql .= " and id = 0 ";
					$count_sql .= " and id = 0 ";
				}
			}
			
			if($where != '')
			{
				$sql.=" and ".$where;
				$count_sql.=" and ".$where;
			}
			
			if($orderby=='')
			$sql.=" order by sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;
						
			
			$deals = $GLOBALS['db']->getAll($sql);				
			$deals_count = $GLOBALS['db']->getOne($count_sql);
			
	 		if($deals)
			{
				foreach($deals as $k=>$deal)
				{
				
					//格式化数据
					$deal['origin_price_format'] = format_price($deal['origin_price']);
					$deal['current_price_format'] = format_price($deal['current_price']);
	
					
					if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
					$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];			
					else
					$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
					if($deal['origin_price']>0&&floatval($deal['discount'])==0)
					{
						$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);					
					}
					
					$deal['discount'] = round($deal['discount'],2);
	
	
	
					$deal['save_price_format'] = format_price($deal['save_price']);
					if($deal['uname']!='')
					$durl = url("youhui","ydetail",array("id"=>$deal['uname']));
					else
					$durl = url("youhui","ydetail",array("id"=>$deal['id']));
					$deal['url'] = $durl;
					
					$deals[$k] = $deal;
				}
			}	
			$res = array('list'=>$deals,'count'=>$deals_count);	
			
		return $res;
}
?>