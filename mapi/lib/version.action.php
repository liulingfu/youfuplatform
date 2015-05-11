<?php
class version
{
	public function index()
	{
	
		$root = array();
		$root['serverVersion'] = $GLOBALS['m_config']['version'];
		$root['filename'] = $_FANWE['site_url'].$GLOBALS['m_config']['filename'];
		if(file_exists(APP_ROOT_PATH.$GLOBALS['m_config']['filename']))
		{
			$root['hasfile'] = 1;
			$root['filesize'] = filesize(APP_ROOT_PATH.$GLOBALS['m_config']['filename']);
		}
		else 
		{
			$root['hasfile'] = 0;
			$root['filesize'] = 0;
		}
		
		output($root);
	}
}
?>