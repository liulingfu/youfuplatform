<?php
class newslist
{
	public function index()
	{
		$root = array();
		$root['return'] = 1;

		$root['newslist'] = $GLOBALS['m_config']['newslist'];	
		
		output($root);
	}
}
?>