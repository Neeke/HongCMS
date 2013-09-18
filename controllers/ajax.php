<?php if(!defined('ROOT')) die('Access denied.');

class c_ajax extends SAjax{

    public function myshop(){

		//AJAX测试, JSONP解决跨域调用的问题
		if(isset($_GET['callback']) AND $_GET['callback'] != ''){
			$function = $_GET['callback'];
		}else{
			$function = false;
		}

		$arr = array('info' => '<div class="prompt">test</div>');

		if($function){
			echo $function . '(' . $this->json->encode($arr) . ')'; //jsonp返回数据的格式
		}else{
			echo $this->json->encode($arr); //json返回数据的格式
		}
	} 

}

?>