<?php if(!defined('ROOT')) die('Access denied.');

//前台基础类继承模板类
class SWeb extends STpl{
	/**
	 * 前台用户
	 * @var array
	 */
	protected $user;
	public $title;
	public $description;
	public $keywords;
	public $pcategories = array(); //产品分类数组
	public $pcat_ids = array(); //产品分类cat_id - 父p_id数组
	public $pcats_ok = array(); //有效(未隐藏)的产品分类cat_id - 父p_id数组
	public $langs = array(); //语言数组成员, 在子类中调用

	public function __construct(){
		global $_CFG, $DB;

		include(ROOT . 'includes/functions.common.php'); //加载函数库(包括公共函数库)

		$this->config = & $_CFG;  //引用全局配置
		$this->db = & $DB;  //引用全局数据库连接实例

		if(IS_CHINESE){
			$this->langs = require(ROOT . 'public/languages/Chinese.php'); //将语言数组赋值给类成员
			$this->title = $this->config['siteTitle'];
			$this->description = $this->config['siteKeywords'];
			$sitename = $this->config['siteCopyright'];

			$c_sql = "SELECT cat_id, p_id, is_show, show_sub, name, keywords, counts ";
		}else{
			$this->langs = require(ROOT . 'public/languages/English.php'); //将语言数组赋值给类成员
			$this->title = $this->config['siteTitleEn'];
			$this->description = $this->config['siteKeywordsEn'];
			$sitename = $this->config['siteCopyrightEn'];

			$c_sql = "SELECT cat_id, p_id, is_show, show_sub, name_en AS name, keywords_en AS keywords, counts ";
		}

		$this->keywords = $this->description;

		$this->tpl_compile_dir = T_CACHEPATH;  //定义STpl模板缓存路径
		$this->tpl_template_dir = T_PATH;  //定义STpl模板路径
		$this->tpl_check = $this->config['siteTemplateCheck'];  //定义STpl模板是否检测文件更新

		//常用变量模板赋值
		$this->assign('baseurl',  BASEURL); //网址URL
		$this->assign('public',  SYSDIR . 'public/'); //公共文件URL
		$this->assign('t_url',  T_URL); //当前模板URL
		$this->assign('t_url',  T_URL); //当前模板URL
		$this->assign('title',  $this->title); //默认网站标题名称
		$this->assign('description',  $this->description);
		$this->assign('keywords',  $this->keywords);
		$this->assign('sitename',  $sitename); //版权名称
		$this->assign('sitebeian',  $this->config['siteBeian']); //备案信息

		$this->assign('langs', $this->langs); //将语言数组分配给模板

		//判断网站是否关闭
		if(!$this->config['siteActived']){
			$this->assign('errorinfo', $this->config['siteOffTitle'] . '<br>' . $this->config['siteOffTitleEn']); //错误信息
			$this->display('offline.tpl');

			exit();
		}

		//获取产品分类
		$getpcats = $this->db->query($c_sql . " FROM " . TABLE_PREFIX . "pcat  WHERE is_show = 1 ORDER BY sort ASC");
		while($cat = $this->db->fetch($getpcats))	{
			$this->pcategories[$cat['cat_id']] = $cat;
			$this->pcat_ids[$cat['cat_id']] = $cat['p_id'];
		}

		$this->assign('pcategories', $this->map_cats($this->pcat_ids)); //分配多级产品分类

		//$this->auth(); //授权
	}

	//生成顶部菜单多级分类字符串
	protected function map_cats($pcat_ids, $pid = 0){
		$sReturn = '';

		foreach($pcat_ids as $cat_id => $p_id){
			if($pid == $p_id){
				$this->pcats_ok[$cat_id] = $p_id; //记录有效(未隐藏)的产品分类cat_id - 父p_id数组

				$sReturn .= '<li><a href="'.URL('products?cat=' . $cat_id).'">' . $this->pcategories[$cat_id]['name'] . '</a>';

				//存在子分类时递归获取
				if(in_array($cat_id, $this->pcat_ids)){
					$sReturn .= $this->map_cats($pcat_ids, $cat_id);
				}

				$sReturn .= '</li>';
			}
		}

		if($sReturn) $sReturn = "<ul>$sReturn</ul>";

		return $sReturn;
	}

	/**
	 * protected 前台授权函数 auth
	 */
	protected function auth(){}

	/**
	 * protected 操作权限验证函数 CheckAccess 无输出
	 */
	protected function CheckAccess($action = '') {}

	/**
	 * protected 操作授权验证输出并输出错误信息 CheckAction
	 */
	protected function CheckAction($action = '') {}

	/**
	 * 析构函数
	 */
	public function __destruct(){}

}

?>