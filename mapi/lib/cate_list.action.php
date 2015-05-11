<?php
class cate_list{
	public function index()
	{
		$cate_type = intval($GLOBALS['request']['cate_type']);
		$pid = intval($GLOBALS['request']['pid']);
		/*
		$page = intval($GLOBALS['request']['page']); //分页
		$page=$page==0?1:$page;
							
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
		*/
		
		$root = array();
		$root['item'] = $this->getCateArray($cate_type,$pid);//分类
		$page = 1;
		$page_total = 1;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);	
		output($root);
	}
	
	function getCateArray($cate_type,$pid){
		if ($cate_type == 0){
			$table_name = DB_PREFIX.'deal_cate';
		}else if ($cate_type == 1){
			$table_name = DB_PREFIX.'shop_cate';
		}else if ($cate_type == 2){
			$table_name = DB_PREFIX.'event_cate';
		}

		if ($cate_type == 2){
			$sql = "select id,name,0 as pid, '' as py, '' as icon, 0 as has_child from ".$table_name. " where is_effect =1 and is_delete =0 ";
			//echo $sql ;exit;
			$tree_list = $GLOBALS['db']->getAll($sql);
		}else{
			$sql = "select id,name,pid,uname as py, '' as icon from ".$table_name. " where is_effect =1 and is_delete =0 and pid = ".$pid;
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v)
			{
				$count = intval($GLOBALS['db']->getOne("select count(*) from ".$table_name." where is_effect =1 and is_delete =0 and pid = ".$v['id']));
				if($count>0)
				$list[$k]['has_child'] = 1;
				else
				$list[$k]['has_child'] = 0;
			}
			$tree_list = m_toTree($list,"id","pid","child");		
		}
		
		if ($pid == 0){
			$all = array();
			$all[] = array("id"=>0,"name"=>"全部分类",pid=>0,py=>"",icon=>"",has_child=>0);
			$tree_list = array_merge($all, $tree_list); 
			return $tree_list;
		}else{
			return $tree_list;
		}
		
	}	
}
?>