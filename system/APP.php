<?php if(!defined('ROOT')) die('Access denied.');

class APP{
	/**
	 * 应用程序默认路径
	 * @var string
	 */
	public static $appDir="./";

	/**
	 * 默认控制器名
	 * @var string
	 */
	public static $defaultController="index";

	/**
	 * 默认动作(方法)名
	 * @var string
	 */
	public static $defaultAction="index";

	/**
	 * 默认的URI分隔符(controller,action等)
	 * @var string
	 */
	public static $splitFlag="/";

	/**
	 * 设置默认的控制器名称
	 * 
	 * @param string $controller
	 * @return boolean
	 */
	public static function setDefaultController($controller){
		self::$defaultController = $controller;
		return true;
	}

	/**
	 * 获取默认的控制器名称
	 * 
	 * @return string
	 */
	public static function getDefaultController(){
		return self::$defaultController;
	}

	/**
	 * 设置默认的动作(方法)名称
	 * 
	 * @param string $ation
	 * @return boolean
	 */
	public static function setDefaultAction($ation){
		self::$defaultAction = $ation;
		return true;
	}

	/**
	 * 获取默认的动作(方法)名称
	 * 
	 * @return string $action
	 */
	public static function getDefaultAction(){
		return self::$defaultAction;
	}

	/**
	 * 设置URI分隔符(controller,action等)
	 * 
	 * @param string $flag
	 * @return boolean
	 */
	public static function setSplitFlag($flag){
		self::$splitFlag = $flag;
		return true;
	}

	/**
	 * 获取URI分隔符(controller,action等)
	 * 
	 * @return string
	 */
	public static function getSplitFlag(){
		return self::$splitFlag;
	}

	/**
	 * 设置应用程序的路径, 要求绝对路径且以 / 结尾
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public static function setAppDir($dir){
		self::$appDir = $dir;
		return true;
	}

	/**
	 * 获取应用程序的路径
	 * 
	 * @return string
	 */
	public static function getAppDir(){
		return self::$appDir;
	}

	/**
	 * 设置debug状态
	 *
	 * @param boolean $debug
	 * @return boolean
	 */
	public static function setDebug($debug){
		self::$_debug = $debug;
		return true;
	}

	/**
	 * 获取debug状态
	 * 
	 * @return boolean 
	 */
	public static function getDebug(){
		return self::$_debug;
	}

	/**
	 * 框架主方法 !!!
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function run(){
		$splitFlag = preg_quote(self::$splitFlag,"/");
		$path_array = array();

		$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
		if(!empty($path)){
			if($path[0]=="/") $path=strtolower(substr($path,1));
			$path_array = preg_split("/[$splitFlag\/]/",$path,-1);
		}

		$controller	= !empty($path_array[0]) ? $path_array[0] : self::$defaultController ;
		$action	= !empty($path_array[1]) ? $path_array[1] : self::$defaultAction ;

		$app_file = self::$appDir . "controllers/" . $controller . ".php";
		if(!is_file($app_file)){
			self::debug("file[$app_file] does not exists.", $controller);
			return false;
		}else{
			require_once(realpath($app_file));
		}

		$classname = 'c_' . $controller;
		if(!class_exists($classname, false)){
			self::debug("class[$classname] does not exists.", $controller);
			return false;
		}

		$path_array[0] = $controller;
		$path_array[1] = $action;
		$classInstance = new $classname($path_array);
		if(!method_exists($classInstance,$action)){
			self::debug("method[$action] does not exists in class[$classname].", $controller);
			return false;
		}

		return call_user_func(array(&$classInstance,$action),$path_array);
	}

	/**
	 * @var boolean 默认显示调试信息
	 */
	private static $_debug = 1;

	private function debug($debugmsg, $controller){
		if(self::$_debug || $controller == 'index'){
			include('errors/404.php');
		}else{
			header("location: ./"); //不显示debug信息时, 自动跳转到当前目录首页
		}
	}
}

?>