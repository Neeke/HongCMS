<?php if(!defined('ROOT')) die('Access denied.');

error_reporting(E_ALL & ~E_NOTICE);

$mtime = explode(' ', microtime());
$sys_starttime = $mtime[1] + $mtime[0];

@include(ROOT . 'config/config.php');

//自动加载函数
function __autoload($class){
	if($class{0} === "S"){
		$file = ROOT . "system/plugins/$class.class.php"; //自动加载系统扩展类
	}else{
		//自动加载模型, 模型类名: name, 文件名必须小写, 文件路径如: ./models/name.php
		$file ="./models/$class.php";
	}
	
	require_once($file);
}

require(ROOT . 'config/settings.php');
require(ROOT . 'system/APP.php');

define('APP_NAME', $_CFG['siteAppName']);
define('APP_VERSION', $_CFG['siteAppVersion']);

define('BASEURL', $_CFG['siteBaseUrl']);  //网站的完整URL
define('BACKURL', $_SERVER['HTTP_REFERER']); //前一个页面的URL

define('T_PATH', ROOT . 'public/templates/' . $_CFG['siteDefaultTemplate'].'/'); //前台当前模板绝对路径
define('T_URL', SYSDIR . 'public/templates/' . $_CFG['siteDefaultTemplate'].'/'); //前台当前模板相对URL

define('T_CACHEPATH', ROOT . 'cache/' . $_CFG['siteDefaultTemplate'].'/'); //当前模板的缓存路径

define('COOKIE_WEB', COOKIE_KEY.'web');  //前台用户的COOKIE名称
define('COOKIE_ADMIN', COOKIE_KEY.'admin');  //后台用户的COOKIE名称

//定义前台语言
if(isset($_COOKIE[COOKIE_KEY.'lang'])){
	$lang = $_COOKIE[COOKIE_KEY.'lang'];
}else{
	if($_CFG['siteDefaultLang'] == 'Auto'){
		if (strstr(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-cn') OR strstr(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-tw'))
		{
			$lang = 'Chinese';
		}else{
			$lang = 'English';
		}
	}else{
		$lang = $_CFG['siteDefaultLang'];
	}
}

define('IS_CHINESE', ($lang == 'Chinese') ? true : false);


$DB = new SMysql($dbusername, $dbpassword, $dbname,  $servername, true);
$dbpassword   = ''; //将config.php文件中的密码赋值为空, 增加安全性

?>