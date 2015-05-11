<?php
//团购模块的筛选切换单菜
class tuan_filter_nav_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		//传入参数 city_id(城市)  id(分类ID) tid(子分类ID)  qid(商圈ID)
		$end_time = get_gmtime()-(3600*24);
		$result = unserialize($GLOBALS['db']->getOne("select cache_data from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."' and cache_key = '".$key."' and cache_time > ".$end_time));
		if($result===false)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."' and cache_key = '".$key."'");
			
			$deal_quan_id = intval($param['qid']);
			$city_id = intval($param['city_id']);
			$url_param = array("id"=>$param['id'],"tid"=>$param['tid'],"qid"=>$param['qid']);
			$deal_cate_id = intval($param['id']);
			$deal_type_id = intval($param['tid']);
			//大区
			$qpid = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."area where id = ".$deal_quan_id);
			$bdeal_quan_id = $qpid == 0?$deal_quan_id:$qpid; //大分类ID
			
			if($bdeal_quan_id>0)
			$sdeal_quan_id = 0;
			else
			$sdeal_quan_id = $deal_quan_id;
			$c_param = array("cid"=>$deal_cate_id,"tid"=>$deal_type_id,"aid"=>$bdeal_quan_id,"qid"=>$sdeal_quan_id);
	
			$bquan_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=0 AND city_id=".$city_id." ORDER BY `sort` DESC ");
			foreach($bquan_list as $k=>$v)
			{
					$bquan_list[$k+1]['name'] = $v['name'];
					if($deal_quan_id==$v['id'] || $bdeal_quan_id==$v['id'])
						$bquan_list[$k+1]['current'] = 1;
					
					$tmp_url_param = $url_param;
					$tmp_url_param['qid'] = $v['id'];
					$durl = url("tuan","index",$tmp_url_param);
					$bquan_list[$k+1]['url']=$durl;
					
					$tmp_url_p = $c_param;
					$tmp_url_p['aid'] = $v['id'];
					$condition = build_deal_filter_condition($tmp_url_p);
					$bquan_list[$k+1]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
			
			
			}
			$all_current = 0;
			if($deal_quan_id == 0)
					$all_current = 1;
					
			$tmp_url_param = $url_param;
			$tmp_url_param['qid'] = 0;
			$bquan_list[0] = array("url"=>url("tuan","index",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);
			
			$tmp_url_p = $c_param;
			$tmp_url_p['aid'] = 0;
			$condition = build_deal_filter_condition($tmp_url_p);
			$bquan_list[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
			
			
			$result['bquan_list'] = $bquan_list;
			
			
			//当前城市的二级商圈
			if(($bdeal_quan_id != $qpid&&$qpid == 0) || ($bdeal_quan_id == $qpid&&$qpid!=0))
			{
				
					$squan_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=".$bdeal_quan_id." AND city_id=".$city_id." ORDER BY `sort` DESC ");
					foreach($squan_list as $k=>$v){
						$squan_list[$k]['name'] = $v['name'];
						if($deal_quan_id==$v['id'])
							$squan_list[$k]['current'] = 1;
						$tmp_url_param = $url_param;
						$tmp_url_param['qid'] = $v['id'];
						$durl = url("tuan","index",$tmp_url_param);
						$squan_list[$k]['url']=$durl;
						
						$tmp_url_p = $c_param;
						$tmp_url_p['qid'] = $v['id'];
						$condition = build_deal_filter_condition($tmp_url_p);
						$squan_list[$k]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
				
					}
			}
			$result['squan_list'] = $squan_list;
			
			
			//大类
			$bcate_list[]= array("name"=>$GLOBALS['lang']['ALL'],"qid"=>$deal_quan_id);
			$bcate_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 and pid = 0 order by sort desc");
			foreach($bcate_list as $k=>$v)
			{		
						$bcate_list[$k+1]['name'] = $v['name'];
						if($deal_cate_id==$v['id'])
						$bcate_list[$k+1]['current'] = 1;
						if($v['uname']!='')
						$durl = url("tuan","index",array("id"=>$v['uname'],"qid"=>$deal_quan_id));
						else 
						$durl = url("tuan","index",array("id"=>$v['id'],"qid"=>$deal_quan_id));
						$bcate_list[$k+1]['url'] = $durl;
						
						$tmp_url_p = $c_param;
						$tmp_url_p['cid'] = $v['id'];
						$tmp_url_p['tid'] = 0;
						$condition = build_deal_filter_condition($tmp_url_p);
						$bcate_list[$k+1]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
						
			}
			$all_current = 0;
			if($deal_cate_id == 0)
					$all_current = 1;
					
			$tmp_url_param = $url_param;
			$tmp_url_param['id'] = 0;
			$bcate_list[0] = array("url"=>url("tuan","index",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);
			
			
			$tmp_url_p = $c_param;
			$tmp_url_p['cid'] = 0;
			$condition = build_deal_filter_condition($tmp_url_p);
			$bcate_list[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
			
			$result['bcate_list'] = $bcate_list;
			
			
			//小类
			if($deal_cate_id>0)
			{
				
					//$scate_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 and pid = ".$bdeal_cate_id." order by sort desc");
					$scate_list =$GLOBALS['db']->getAll("select t.id,t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$deal_cate_id." order by t.sort desc");
					
					foreach($scate_list as $k=>$v)
					{			
							//$cate_deal_list_rs = get_deal_list(1,$v['id'],$city_id=0, $type=array(DEAL_ONLINE,DEAL_NOTICE), $where='buy_type<>1',$orderby = '',$deal_quan_id);	
							//$scate_list[$k]['count'] = $cate_deal_list_rs['count'];
							
							if($deal_type_id==$v['id'])
							$scate_list[$k]['current'] = 1;
		
							$tmp_url_param = $url_param;
							$tmp_url_param['tid'] = $v['id'];
							$durl = url("tuan","index",$tmp_url_param);						
							$scate_list[$k]['url'] = $durl;
							
							$tmp_url_p = $c_param;
							$tmp_url_p['cid'] =$deal_cate_id;
							$tmp_url_p['tid'] = $v['id'];
							$condition = build_deal_filter_condition($tmp_url_p);
							$scate_list[$k]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and is_shop = 0 and (end_time = 0 or end_time > '".get_gmtime()."') $condition ");
					
					}
			}
			$result['scate_list'] = $scate_list;
			
			$db_data['cache_key'] = $key;
			$db_data['cache_type'] = __CLASS__;
			$db_data['cache_time'] = get_gmtime();
			$db_data['cache_data'] = serialize($result);
			$GLOBALS['db']->autoExecute(DB_PREFIX."auto_cache",$db_data);
		}	
		return $result;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."' and cache_key = '".$key."'");
	}
	public function clear_all()
	{
		$GLOBALS['db']->query("delete from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."'");		
	}
}
?>