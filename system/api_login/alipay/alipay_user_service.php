<?php
/**
 *类名：alipay_service
 *功能：支付宝外部服务接口控制
 *详细：该页面是请求参数核心处理文件，不需要修改
 *版本：3.1
 *修改日期：2010-11-26
 '说明：
 '以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 '该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

*/

require_once("alipay_function.php");

class alipay_user_service {

    var $gateway;			//网关地址
    var $_key;				//安全校验码
    var $mysign;			//签名结果
    var $sign_type;			//签名类型
    var $parameter;			//需要签名的参数数组
    var $_input_charset;    //字符编码格式

    /**构造函数
	*从配置文件及入口文件中初始化变量
	*$parameter 需要签名的参数数组
	*$key 安全校验码
	*$sign_type 签名类型
    */
    function alipay_user_service($parameter,$key,$sign_type) {
        $this->gateway	      = "https://www.alipay.com/cooperate/gateway.do?";
        $this->_key  = $key;
        $this->sign_type      = $sign_type;
        $this->parameter      = para_filter($parameter);

        //设定_input_charset的值,为空值的情况下默认为utf-8
        if($parameter['_input_charset'] == '')
            $this->parameter['_input_charset'] = 'utf-8';

        $this->_input_charset   = $this->parameter['_input_charset'];

        //获得签名结果
        $sort_array   = arg_sort($this->parameter);    //得到从字母a到z排序后的签名参数数组
        $this->mysign = build_mysign($sort_array,$this->_key,$this->sign_type);
    }

    /********************************************************************************/

    /**构造表单提交HTML
	*return 表单提交HTML文本
     */
    function build_form($icon) {
		//GET方式传递
        //$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->gateway."_input_charset=".$this->parameter['_input_charset']."' method='get'>";
		
		//POST方式传递（GET与POST二必选一）
        //$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->gateway."_input_charset=".$this->parameter['_input_charset']."' method='post'>";

        $url = $this->gateway."_input_charset=".$this->parameter['_input_charset'];
        while (list ($key, $val) = each ($this->parameter)) {
            //$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            $url.="&".$key."=".$val;
        }
		$url.="&sign=".$this->mysign;
		$url.="&sign_type=".$this->sign_type;

		//submit按钮控件请不要含有name属性
        $sHtml = "<a href='".$url."'><img src='".$icon."' alt='支付宝快捷登录' /></a>";

		
        return $sHtml;
    }
    /********************************************************************************/

}
?>