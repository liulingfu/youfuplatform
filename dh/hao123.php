<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

/* API的基本信息 */
if (isset($read_api) && $read_api == true)
{
    $api['info']    =  array(
    						array('name'=>'Hao123API[http://tuan.hao123.com/]','url'=>'hao123.php')    						
    				   );
    return $api;
}

require_once "api.php";
	
header('Content-type: text/xml; charset=utf-8');
		$now = get_gmtime();
		$sql = "SELECT d.id,d.discount,d.city_id,c.name as cate_name,d.name as goods_name,d.img,d.icon,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count,sl.tel as sp_tel,sl.address as sp_address,sl.xpoint,sl.ypoint   ".
					'FROM '.DB_PREFIX.'deal as d '.
					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.
					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.
					'left join '.DB_PREFIX.'supplier_location as sl on sl.supplier_id = s.id '.
					'left join '.DB_PREFIX.'deal_cate as c on c.id = d.cate_id '.
					"where d.is_effect = 1 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";
		

	$list = $GLOBALS['db']->getAll($sql);
	
	$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xml.="<urlset>\r\n";
		
		foreach($list as $item)
		{
                    
			$xml.="<url>\r\n";
		
			$url = get_domain().url("tuan","deal",array("id"=>$item['id']));	
			//商品折扣
			if($item['discount']>0)
			{
				$rebate = number_format($item['discount'],1);
			}
			if ($item['origin_price'] > 0)
				$rebate = number_format($item['current_price']/$item['origin_price'] * 10, 1);
			else
				$rebate = 0;
				
			
			$begin_time = intval($item['begin_time'])>0?(intval($item['begin_time'])+(8*3600)):0; 
			$end_tiime = intval($item['end_time'])>0?(intval($item['end_time'])+(8*3600)):0; 
			
			$xml.="<loc>".convertUrl($url)."</loc>\r\n";
			$xml.="<data>\r\n";
			$xml.="<display>\r\n";
			$xml.="<website>".app_conf("SHOP_TITLE")."</website>\r\n";
			$xml.="<siteurl>".get_domain().APP_ROOT."</siteurl>\r\n";
			$xml.="<city>".$item['city_name']."</city>\r\n";
			$gcatename=$item['cate_name'];
                        $goodsname=$item['goods_name'];
			   if(!preg_match('/^((?!餐|美食|饮).)*$/is',$gcatename))
			{
				$class = 1;
                                if(!preg_match('/^((?!火锅|羊蝎子).)*$/is',$goodsname))
                                { 
                                    $classl= 火锅;
                                }
                                 else if(!preg_match('/^((?!西点|西餐).)*$/is',$goodsname))
                                {
                                        $classl = 西餐;
                                }
                                else if(!preg_match('/^((?!海鲜).)*$/is',$goodsname))
                                {
                                        $classl = 海鲜;
                                }
                                 else if(!preg_match('/^((?!地方菜|北京菜|山东菜|四川菜|广东菜|淮扬菜|浙江菜|福建菜|湖北菜|徽菜|湖南菜|上海菜|天津菜).)*$/is',$goodsname))
                                {
                                        $classl = 地方菜;
                                }
                                 else if(!preg_match('/^((?!蛋糕|甜点).)*$/is',$goodsname))
                                {
                                        $classl = 蛋糕;
                                }
                                 else if(!preg_match('/^((?!烧烤|烤串|烤羊腿).)*$/is',$goodsname))
                                {
                                        $classl = 烧烤;
                                }
                                  else if(!preg_match('/^((?!肯德基|麦当劳|咖啡厅|冷饮).)*$/is',$goodsname))
                                {
                                        $classl = 快餐休闲;
                                }
                               
                                else{$classl= 其他;}
			}
			else if(!preg_match('/^((?!休闲|娱乐).)*$/is',$gcatename))
			{
                            
				$class = 2;
                                if(!preg_match('/^((?!影院门票|电影卡).)*$/is',$goodsname))
                                { 
                                    $classl= 电影;
                                }
                                 else if(!preg_match('/^((?!KTV).)*$/is',$goodsname))
                                {
                                        $classl = KTV;
                                }
                                 else if(!preg_match('/^((?!健身中心|瑜伽|舞蹈|击剑).)*$/is',$goodsname))
                                {
                                        $classl = 运动健身;
                                }
                                 else if(!preg_match('/^((?!电玩城|桌游|真人CS|陶艺吧).)*$/is',$goodsname))
                                {
                                        $classl = 游乐电玩;
                                }else if(!preg_match('/^((?!话剧|演唱会|相声|展览|赛事).)*$/is',$goodsname))
                                {
                                        $classl = 展览演出;
                                }else if(!preg_match('/^((?!洗浴中心|会馆|会所|汗蒸|温泉).)*$/is',$goodsname))
                                {
                                        $classl = 洗浴;
                                }
                                else if(!preg_match('/^((?!足疗|推拿|艾灸).)*$/is',$goodsname))
                                {
                                        $classl = 足疗按摩;
                                }
                                
                               else{$classl= 其他;}
			}
			else if(!preg_match('/^((?!生活|服务).)*$/is',$gcatename))
			{
				$class = 3;
                                
                                if(!preg_match('/^((?!个人写真|孕妇写真).)*$/is',$goodsname))
                                { 
                                    $classl= 足疗按摩;
                                }
                                 else if(!preg_match('/^((?!语言学校|儿童兴趣培训|成人兴趣).)*$/is',$goodsname))
                                {
                                        $classl = 教育培训;
                                }
                                else if(!preg_match('/^((?!婚纱摄影).)*$/is',$goodsname))
                                {
                                        $classl = 婚纱摄影;
                                }
                                else if(!preg_match('/^((?!儿童摄影).)*$/is',$goodsname))
                                {
                                        $classl = 儿童摄影;
                                }
                                else if(!preg_match('/^((?!洗牙|补牙|正畸|口腔.)*$/is',$goodsname))
                                {
                                        $classl = 口腔;
                                }
                                else if(!preg_match('/^((?!体检机构|专科医院体检).)*$/is',$goodsname))
                                {
                                        $classl = 体检;
                                }
                                else if(!preg_match('/^((?!加油卡|洗车|保养).)*$/is',$goodsname))
                                {
                                        $classl = 汽车养护;
                                }
                                else{$classl= 其他;}
			}
			else if(!preg_match('/^((?!网上|购物).)*$/is',$gcatename))
			{
				$class = 4;
                                if(!preg_match('/^((?!男装|女装|童装|内衣|袜子).)*$/is',$goodsname))
                                { 
                                    $classl= 服装;
                                }
                                 else if(!preg_match('/^((?!男鞋|女鞋|童鞋).)*$/is',$goodsname))
                                {
                                        $classl = 鞋靴;
                                }
                                 else if(!preg_match('/^((?!男包|女包|单肩包|手提包|钱包|运动包|功能箱包).)*$/is',$goodsname))
                                {
                                        $classl = 箱包;
                                }
                                 else if(!preg_match('/^((?!眼镜|围巾|皮带|首饰).)*$/is',$goodsname))
                                {
                                        $classl = 饰品;
                                }
                                 else if(!preg_match('/^((?!面部保养|眼唇保养|彩妆|身体护理|香水|美容工具).)*$/is',$goodsname))
                                {
                                        $classl = 化妆品;
                                }
                                 else if(!preg_match('/^((?!床上用品|厨具|生活日用|清洁用品|成人用品).)*$/is',$goodsname))
                                {
                                        $classl = 生活家居;
                                }
                                  else if(!preg_match('/^((?!生活电器|厨房电器|个人护理|健康电器).)*$/is',$goodsname))
                                {
                                        $classl = 家用电器;
                                }
                                  else if(!preg_match('/^((?!手机|手机配件|摄影摄像|电脑数码|时尚影音).)*$/is',$goodsname))
                                {
                                        $classl = 手机数码;
                                }
                                  else if(!preg_match('/^((?!保健品|粮油蔬果|零食|茶酒饮料).)*$/is',$goodsname))
                                {
                                        $classl = 食品饮料;
                                }
                                else if(!preg_match('/^((?!妈妈用品|宝宝用品).)*$/is',$goodsname))
                                {
                                        $classl = 母婴用品;
                                }
                                else if(!preg_match('/^((?!玩具|摆件|布偶|模型||成人玩具).)*$/is',$goodsname))
                                {
                                        $classl = 玩具;
                                }
                                else if(!preg_match('/^((?!0元抽奖).)*$/is',$goodsname))
                                {
                                        $classl = 抽奖;
                                }
                                else if(!preg_match('/^((?!鲜花|礼盒|礼品).)*$/is',$goodsname))
                                {
                                        $classl = 礼品;
                                }
                                else{$classl= 其他;}
			}
			else if(!preg_match('/^((?!旅游|住宿 ).)*$/is',$gcatename))
			{
				$class = 5;
                                if(!preg_match('/^((?!酒店|旅馆|快捷酒店|酒店代金券).)*$/is',$goodsname))
                                { 
                                    $classl= 酒店;
                                }
                                 else if(!preg_match('/^((?!旅游团|自助游|近郊游|度假村|旅游代金券|旅游).)*$/is',$goodsname))
                                {
                                        $classl = 旅游;
                                }
                                 else if(!preg_match('/^((?!景点门票|全国景点|联票).)*$/is',$goodsname))
                                {
                                        $classl = 景点公园;
                                }
                                
                                else{$classl= 其他;}
			}
                        else if(!preg_match('/^((?!美容|美发|丽人 ).)*$/is',$gcatename))
			{
				$class = 6;
                                if(!preg_match('/^((?!烫发|剪发|染发|造型|美发店).)*$/is',$goodsname))
                                { 
                                    $classl= 美发;
                                }
                                else if(!preg_match('/^((?!美容中心|减肥中心|产后瘦身|美体).)*$/is',$goodsname))
                                {
                                        $classl = 美容美体;
                                }
                                else if(!preg_match('/^((?!美甲门店).)*$/is',$goodsname))
                                {
                                        $classl = 美甲;
                                }
                                else{$classl= 其他;}
			}
			
			$xml.="<category>".$class."</category>\r\n";
                        $xml.="<subcategory>".$classl."</subcategory>\r\n";
                        $xml.="<characteristic></characteristic>\r\n";
                        $xml.="<destination></destination>\r\n";
                        $xml.="<thrcategory ></thrcategory >\r\n";
			$xml.="<dpshopid>".$item['xpoint'].",".$item['ypoint']."</dpshopid>\r\n";
			$xml.="<range>".$item['sp_address']."</range>\r\n";
			$xml.="<address>".$item['sp_address']."</address>\r\n";
			$xml.="<major>1</major>\r\n";
			$xml.="<title>".addslashes(emptyTag($item['goods_name']))."</title>\r\n";

			$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
                        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	
                        $img = str_replace("./public/",$domain."/public/",$item['img']);
			
			$xml.="<image>".$img."</image>\r\n";
			$xml.="<startTime>".$begin_time."</startTime>\r\n";
			$xml.="<endTime>".$end_tiime."</endTime>\r\n";
			$xml.="<value>".round($item['origin_price'],2)."</value>\r\n";
			$xml.="<price>".round($item['current_price'],2)."</price>\r\n";
			$xml.="<rebate>".$rebate."</rebate>\r\n";
			$xml.="<bought>".$item['buy_count']."</bought>\r\n";
			$xml.="<name>".$item['goods_name']."</name>\r\n";
			$xml.="<seller>".$item['supplier_name']."</seller>\r\n";
			$xml.="<phone>".$item['sp_tel']."</phone>\r\n";
			
			$xml.="</display>\r\n";
			$xml.="</data>\r\n";
			$xml.="</url>\r\n";
		}
		
		$xml.="</urlset>\r\n";
		echo $xml;

?>