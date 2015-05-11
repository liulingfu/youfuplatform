<?php 

/* API的基本信息 */

if (isset($read_api) && $read_api == true)

{

    $api['info']    =  array(

    						array('name'=>'团800API[http://www.tuan800.com/]','url'=>'tuan800.php')    						

    				   );

    return $api;

}

require_once "api.php";



	header('Content-type: text/xml; charset=utf-8');							

		$sql = "SELECT d.id,d.supplier_id,d.discount,d.sub_name as sub_name,c.name as cate_name,d.city_id,d.name as goods_name,d.img,d.current_price,d.origin_price,d.begin_time,d.end_time,d.brief as goodsbrief,dc.name as city_name,s.name as supplier_name,d.buy_count,s.content,sl.tel as sp_tel,sl.address as sp_address  ".

					'FROM '.DB_PREFIX.'deal as d '.

					'left join '.DB_PREFIX.'deal_city as dc on dc.id = d.city_id '.

					'left join '.DB_PREFIX.'supplier as s on s.id = d.supplier_id '.

					'left join '.DB_PREFIX.'deal_cate as c on c.id = d.cate_id '.

					'left join '.DB_PREFIX.'supplier_location as sl on sl.supplier_id = s.id '.

					"where d.is_effect = 1 and d.is_shop = 0 and d.is_delete = 0 and d.time_status = 1 and d.buy_status < 2  group by d.id order by d.sort desc,d.id desc";

		

		$list = $GLOBALS['db']->getAll($sql);

		$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";

		$xml.="<urlset>\r\n";

		

		foreach($list as $item)

		{

			$xml.="<url>\r\n";

			$url = get_domain().url("tuan","deal",array("id"=>$item['id']));	

				

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

				

			$item_brief = $item['goodsbrief']==''?htmlspecialchars($item['goods_name']):htmlspecialchars($item['goodsbrief']);

				$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");

	        $img = str_replace(APP_ROOT."./public/",$domain."/public/",$item['img']);	

	        $img = str_replace("./public/",$domain."/public/",$item['img']);
				
				
			if((strstr($item['cate_name'],'餐饮')!=false)||strstr($item['cate_name'],'美食')!=false){
			$cate_name='美食,邮购';
			}
			elseif((strstr($item['cate_name'],'休闲')!=false)||strstr($item['cate_name'],'娱乐')!=false){
			$cate_name='生活,娱乐';
			}
			elseif((strstr($item['cate_name'],'美容')!=false)||strstr($item['cate_name'],'保健')!=false){
			$cate_name='美容,美发';
			}
			elseif((strstr($item['cate_name'],'精品')!=false)||strstr($item['cate_name'],'购物')!=false){
			$cate_name='精品,购物';
			}
			elseif((strstr($item['cate_name'],'健身')!=false)||strstr($item['cate_name'],'运动')!=false){
			$cate_name='健身运动';
			}
			elseif((strstr($item['cate_name'],'日常服务')!=false)||strstr($item['cate_name'],'运动')!=false){
			$cate_name='日常服务';
			}
			elseif((strstr($item['cate_name'],'优惠')!=false)||strstr($item['cate_name'],'券票')!=false){
			$cate_name='票务,购物卡券';
                        }
			else
				$cate_name='其它';

			$xml.="<loc>".convertUrl($url)."</loc>\r\n";

			$xml.="<data>\r\n";

			$xml.="<display>\r\n";

			$xml.="<website>".app_conf("SHOP_TITLE")."</website>\r\n";

			$xml.="<identifier>$item[id]</identifier>\r\n";

			$xml.="<siteurl>".get_domain().APP_ROOT."</siteurl>\r\n";

			$xml.="<city>".$item[city_name]."</city>\r\n";

			$xml.="<title>".emptyTag(htmlspecialchars($item['goods_name']))."</title>\r\n";
                     
                        $xml.="<shortTitle>".$item[sub_name]."</shortTitle>\r\n";

			$xml.="<image>".$img."</image>\r\n";

			$xml.="<tag>".$cate_name."</tag>\r\n";

			$xml.="<startTime>".date("Y-m-d",$begin_time)." 00:00:00"."</startTime>\r\n";

			$xml.="<endTime>".date("Y-m-d",$end_tiime)." 00:00:00"."</endTime>\r\n";

			$xml.="<value>".number_format(round($item['origin_price'],2), 2, '.', '0')."</value>\r\n";

			$xml.="<price>".number_format(round($item['current_price'],2),2,'.','0')."</price>\r\n";
                         
                        $xml.="<rebate>".$rebate."</rebate>\r\n";
			
                        $xml.="<bought>".$item['buy_count']."</bought>\r\n";

			$xml.="<maxQuota></maxQuota>\r\n";

			$xml.="<minQuota></minQuota>\r\n";

			$xml.="<post></post>\r\n";

			$xml.="<soldOut></soldOut>\r\n";

			$xml.="<priority>0</priority>\r\n";

//			$xml.="<merchantEndTime></merchantEndTime>\r\n";

			$xml.="<tip><![CDATA[".$item_brief."]]></tip>\r\n";

//			$xml.="<detail><![CDATA[$item_brief]]></detail>\r\n";

			$xml.="</display>\r\n";

			$xml.="<shops>\r\n";

			$xml.="<shop>\r\n";

			$xml.="<name>".emptyTag(htmlspecialchars($item['supplier_name']))."</name>\r\n";

			$xml.="<tel>".emptyTag($item['sp_tel'])."</tel>\r\n";

			$xml.="<addr>".emptyTag($item['sp_address'])."</addr>\r\n";

			$xml.="<area></area>\r\n";
			$xml.="<openTime></openTime>\r\n";
                        
			$xml.="<longitude></longitude>\r\n";

			$xml.="<latitude></latitude>\r\n";
                        
			$xml.="<trafficInfo></trafficInfo>\r\n";
                        
			$xml.="</shop>\r\n";

			$xml.="</shops>\r\n";

			$xml.="</data>\r\n";

			$xml.="</url>\r\n";

		}

		

		$xml.="</urlset>\r\n";

		echo $xml;





?>