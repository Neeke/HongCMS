<?php if(!defined('ROOT')) die('Access denied.');

class c_phpinfo extends SAdmin{

    public function index(){
		SubMenu('环境信息');

		//如果不使用iframe输出phpinfo, 将破坏系统页面的样式
		echo '<div><iframe src="'.BURL('phpinfo/ajax') .'" id="iframe" width="100%" scrolling="no" style="border:0;display:block;overflow:hidden;" onload="this.height=this.contentWindow.document.body.scrollHeight;"></iframe></div>';
	}

	//伪装成ajax
    public function ajax(){
		if(!@phpinfo()){
			echo '<div style="font-size:14px;color:red;height:200px;display:block;">基于安全考虑, 您的服务器禁止执行phpinfo()函数, 系统无法显示服务器环境信息!</div>';
		}
	}

} 

?>