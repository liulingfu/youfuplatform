<?php
class uploadyouhui
{
    public function index()
	{

		$email = addslashes($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = addslashes($GLOBALS['request']['pwd']);//密码
		//检查用户,用户密码
		$user_info = user_check($email,$pwd);
		$user_id  = intval($user_info['id']);


        if(!$user_info)
        {
        	$root['status'] = 0;
        	$root['message'] = "用户已失效，无法上传";
        	output($root);
        }
        else
        {
	        //上传
	        $content = addslashes(htmlspecialchars(trim($GLOBALS['request']['content'])));
			if($content=='')
			{
				$root['status'] = 0;
				$root['message'] = "发布内容不能为空";
				output($root);
			}

			$dir = "u_".to_date(get_gmtime(),"Ym");
			if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
				@mkdir(APP_ROOT_PATH."public/attachment/".$dir);
				@chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
			}

			$img_result = save_image_upload($_FILES,'image_1','attachment/'.$dir,array('origin'=>array(0,0,0,0)),0,1);
			if(intval($img_result['error'])!=0){
				$root['status'] = 0;
				$root['message'] = "图片上传失败:".$img_result['message'];
				output($root);
			}
			$image_1 = $img_result['image_1']['url'];

			$youhui['user_id'] = $user_id;
			$youhui['icon'] = $image_1;
			$youhui['image'] = $image_1;
			$youhui['is_effect'] = 0;
			$youhui['name'] = $content;
			$youhui['content'] = $content;
			$youhui['create_time'] = get_gmtime();
			$youhui['pub_by'] = 1;

			$GLOBALS['db']->autoExecute(DB_PREFIX."youhui", $youhui, 'INSERT');
			$id = $GLOBALS['db']->insert_id();

			if($id)
			{
				$root['status'] = 1;
				$root['message'] = "发布信息成功";
				output($root);
			}
			else
			{
				$root['status'] = 0;
				$root['message'] = "发布信息失败,请稍候再发";
				output($root);
			}
        	//上传
        }
	}
}

?>