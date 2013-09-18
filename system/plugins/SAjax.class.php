<?php if(!defined('ROOT')) die('Access denied.');

//前台Ajax类, 无授权, 无模板
class SAjax{

	public $title;
	public $sitename;
	public $langs = array(); //语言数组成员, 在子类中调用

	protected $ajax = array(); //用于ajax数据收集与输出
	protected $json; //ajax时的JSON对象

	public function __construct(){
		global $_CFG, $DB;

		include(ROOT . 'includes/functions.common.php'); //加载函数库(包括公共函数库)

		$this->config = & $_CFG;  //引用全局配置
		$this->db = & $DB;  //引用全局数据库连接实例

		$this->db->printerror = false; //数据库访问不打印错误信息

		if(IS_CHINESE){
			$this->langs = require(ROOT . 'public/languages/Chinese.php'); //将语言数组赋值给类成员
			$this->title = $this->config['siteTitle'];
			$this->sitename = $this->config['siteCopyright'];
		}else{
			$this->langs = require(ROOT . 'public/languages/English.php'); //将语言数组赋值给类成员
			$this->title = $this->config['siteTitleEn'];
			$this->sitename = $this->config['siteCopyrightEn'];
		}

		//初始化ajax返回数据
		$this->ajax['s'] = 1; // s表示状态, 默认为1(正常),  0(错误)
		$this->ajax['i'] = ''; // i指ajax提示信息
		$this->ajax['d'] = ''; // d指ajax返回的数据
		$this->json = new SJSON;
	}

}

?>