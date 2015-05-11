<?php


$sms_lang = array(
	'ECODE'	=>	'企业代码',

);
$config = array(
    'ECODE'	=>	array(
	'INPUT_TYPE'	=>	'0',
	),
	
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Ewing';
    /* 名称 */
    $module['name']    = "翼锋短信平台";
    $module['lang']  = $sms_lang;
    $module['config'] = $config;	
    $module['server_url'] = 'http://agentin.zhidian3g.cn/MSMSEND.ewing';
    return $module;
}

// 翼锋短信平台
require_once APP_ROOT_PATH."system/libs/sms.php";  //引入接口
require_once APP_ROOT_PATH."system/sms/Ewing/transport.php"; 

class Ewing_sms implements sms
{
	public $sms;
	public $message = "";
   	
	private $statusStr = array(
		"1"	=>	"发送成功",
		"-1"	=>	"不能初始化SO",	
		"-2"	=>	"网络不通  ",	
		"-3"	=>	"一次发送的手机号码过多",	
		"-4"	=>	"内容包含不合法文字",	
		"-5"	=>	"登录账户错误",	
		"-6"	=>	"通信数据传送",	
		"-7"	=>	"没有进行参数初始化",	
		"-8"	=>	"扩展号码长度不对",	
		"-9"	=>	"手机号码不合",	
		"-10"	=>	"号码太长",	
		"-11"	=>	"内容太长",	
		"-12"	=>	"内部错误",	
		"-13"	=>	"余额不足",	
		"-14"	=>	"扩展号不正确",	
		"-17"	=>	"发送内容为空",	
		"-19"	=>	"没有找到该动作（不存在的url地址）",	
		"-20"	=>	"手机号格式不正确",	
		"-21"	=>	"不允许发送时段",
		"-50"	=>	"配置参数错误",
		"-52"	=>	"URL编码错误",
		"-53"	=>	"参数大小写错误",		   
	);
	
    public function __construct($smsInfo = '')
    { 	    	
		if(!empty($smsInfo))
		{			
			$this->sms = $smsInfo;
		}
    }
	
	public function sendSMS($mobile_number,$content)
	{
		if(is_array($mobile_number))
		{
			$mobile_number = implode(",",$mobile_number);
		}
		$sms = new transport();
				
				$params = array(
					"ECODE"=>$this->sms['config']['ECODE'],
					"USERNAME"=>$this->sms['user_name'],
					"PASSWORD"=>$this->sms['password'],
					"EXTNO"=>'',
					"MOBILE"=>$mobile_number,
					"CONTENT"=>$content,
					"SEQ"=>1000
				);
				
				$result = $sms->request($this->sms['server_url'],$params);
				$code = intval(trim($result['body']));
				
				if($code==1)
				{
							$result['status'] = 1;
				}
				else
				{
							$result['status'] = 0;
							$result['msg'] = $this->statusStr[$code];
				}
		return $result;
	}
	
		public function getSmsInfo()
	{	

		return "翼锋短信平台";	
		
	}
	
	public function check_fee()
	{
		$sms = new transport();
				
		$params = array(
					"ECODE"=>$this->sms['config']['ECODE'],
					"USERNAME"=>$this->sms['user_name'],
					"PASSWORD"=>$this->sms['password']
				);
				
		$url = "http://agentin.zhidian3g.cn/ACCOUNT.ewing";
		$result = $sms->request($url,$params);

		
		return "翼锋短信平台&nbsp;&nbsp;剩余：".$result['body']."条";
	}
}
?>