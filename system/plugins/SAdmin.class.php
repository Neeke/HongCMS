<?php if(!defined('ROOT')) die('Access denied.');

class SAdmin{

	protected $admin = null;

	protected $ajax = array(); //用于ajax数据收集与输出
	protected $json; //ajax时的JSON对象

	public function __construct($path){
		global $_CFG, $DB;

		include(ROOT . 'includes/functions.admin.php'); //加载函数库(包括前后台公共函数库)

		$this->config = & $_CFG;  //引用全局配置
		$this->db = & $DB;  //引用全局数据库连接实例

		if($path[1] == 'ajax') { //任意控制器的动作为ajax时, 执行ajax动作, 禁止输出页头, 页尾及数据库访问错误
			$this->db->printerror = false; //ajax数据库访问不打印错误信息
			$this->admin = new admin(1); //ajax时实例化admin模型类

			$this->ajax['s'] = 1; //初始化ajax返回数据, s表示状态
			$this->ajax['i'] = ''; //i指ajax提示信息
			$this->ajax['d'] = ''; //d指ajax返回的数据
			$this->json = new SJSON;

			if(!$this->admin->data){//管理员验证不成功, 直接输出ajax信息, 并终止ajax其它程序程序运行
				$this->ajax['s'] = 0;
				$this->ajax['i'] = "管理员授权错误! 请确认已成功登录后台.";

				die($this->json->encode($this->ajax));
			}

		}else{
			$this->admin = new admin; //实例化admin模型类
			if($path[1] == 'logout') $this->admin->logout(); //无论哪个控制器, 只要是logout动作, admin用户退出

			$this->page_header(); //授权成功输出页头
		}
	}

	/**
	 * 输出页头 page_header
	 */
	protected function page_header() {
		echo '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>'.APP_NAME.' - 后台管理</title>
<link rel="stylesheet" type="text/css" href="'. SYSDIR .'public/admin/admin.css">
<link rel="stylesheet" type="text/css" href="'. SYSDIR .'public/js/artDialog/black.css">
<script src="'. SYSDIR .'public/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="'. SYSDIR .'public/js/jquery.cookie.js" type="text/javascript"></script>
<script src="'. SYSDIR .'public/js/artDialog/jquery.artDialog.min.js" type="text/javascript"></script>
<script src="'. SYSDIR .'public/admin/admin.js" type="text/javascript"></script>
<script type="text/javascript">
var this_uri = "' . $_SERVER['REQUEST_URI'] . '";
</script>
</head>
<body>
<div id="header">
	<div class="logo" ><a href="' . BURL() . '"><img src="'. SYSDIR .'public/admin/images/logo.gif" title="后台首页"></a></div>
	<div class="loading"><div id="ajax-loader" title="Ajax数据更新中..."></div></div>
	<div id="topbar">
		<div id="topmenu">
			<dl class="first"></dl>
			<dl>
				<dt><a href="' . BURL('articles') . '">文章</a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('articles/add') . '">添加文章</a></li>
						<li><a href="' . BURL('articles') . '">文章列表</a></li>
						<li class="last"><a href="' . BURL('acategory') . '">文章类别</a></li>
					</div>
				</dd>
			</dl>
			<dl>
				<dt><a href="' . BURL('products') . '">产品</a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('products/add') . '">添加产品</a></li>
						<li><a href="' . BURL('products') . '">产品列表</a></li>
						<li class="last"><a href="' . BURL('pcategory') . '">产品类别</a></li>
					</div>
				</dd>
			</dl>
			<dl>
				<dt><a href="' . BURL('users') . '">用户</a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('users/add') . '">添加用户</a></li>
						<li class="last"><a href="' . BURL('users') . '">用户列表</a></li>
					</div>
				</dd>
			</dl>
			<dl>
				<dt><a href="' . BURL('news') . '">其它</a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('news') . '">站点新闻</a></li>
						<li class="last"><a href="' . BURL('contents') . '">常态内容</a></li>
					</div>
				</dd>
			</dl>
			<dl>
				<dt><a href="' . BURL('settings') . '">系统</a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('settings') . '">网站设置</a></li>
						<li><a href="' . BURL('language') . '">语言管理</a></li>
						<li><a href="' . BURL('template') . '">模板管理</a></li>
						<li><a href="' . BURL('database') . '">数据维护</a></li>
						<li><a href="' . BURL('phpinfo') . '">环境信息</a></li>
						<li class="last"><a href="' . BURL('upgrade') . '">系统升级</a></li>
					</div>
				</dd>
			</dl>
			<dl class="last"></dl>
		</div>


		<div id="topuser">
			<dl class="first"></dl>
			<dl class="info"><!-- 如果没有信息 class=info none -->
				<dt><a href="' . BURL() . '"><i></i><span>18</span></a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL() . '">预留样式!!</a></li>
						<li><a href="' . BURL() . '">有8个新订单</a></li>
						<li class="last"><a href="' . BURL() . '">有10篇文章待审</a></li>
					</div>
				</dd>
			</dl>
			<dl class="msg none"><!-- 如果没有信息 class=msg none -->
				<dt><a href="' . BURL() . '"><i></i><span>0</span></a></dt>
				<dd>
					<div>
						<div class=right>暂无站内短信.</div>
					</div>
				</dd>
			</dl>
			<dl class="admin">
				<dt><a onclick="'.Confirm('确定退出 '.APP_NAME.' 后台管理吗?', 'index/logout').'"><i></i></a></dt>
				<dd>
					<div>
						<li class="first"><a href="' . BURL('index/logout') . '">'.$this->admin->data['nickname'].' 退出?</a></li>
						<li><a href="' . URL() . '" target="_blank">网站首页</a></li>
						<li class="last"><a href="' . BURL('users/edit?userid=' . $this->admin->data['userid']) . '">修改我的资料</a></li>
					</div>
				</dd>
			</dl>
			<dl class="last"></dl>
		</div>
		<div></div>
	</div>
</div>

<div><!-- 外层添加一个DIV解决IE8下margin-top的问题 -->
<table cellpadding="0" cellspacing="0" id="maintable">
<tr>
<td id="container" valign="top">
<div id="sidebar">
	<div class="sidebar-toggler" title="收拢菜单(Ctrl <)"><i></i></div>
	<ul>
		<li class="start">
		   <a href="' . BURL() . '">
		   <i class="i-home"></i> 
		   <span class="title">首 页</span>
		   </a>
		</li>
		<li class="has-sub">
		   <a href="#">
		   <i class="i-articles"></i> 
		   <span class="title">文章</span>
		   <span class="arrow"></span>
		   </a>
		   <ul class="sub">
			  <li><a href="' . BURL('articles/add') . '">添加文章</a></li>
			  <li><a href="' . BURL('articles') . '">文章列表</a></li>
			  <li><a href="' . BURL('acategory') . '">文章类别</a></li>
		   </ul>
		</li>
		<li class="has-sub">
		   <a href="#">
		   <i class="i-pros"></i> 
		   <span class="title">产品</span>
		   <span class="arrow"></span>
		   </a>
		   <ul class="sub">
			  <li><a href="' . BURL('products/add') . '">添加产品</a></li>
			  <li><a href="' . BURL('products') . '">产品列表</a></li>
			  <li><a href="' . BURL('pcategory') . '">产品类别</a></li>
		   </ul>
		</li>
		<li class="has-sub">
		   <a href="#">
		   <i class="i-users"></i> 
		   <span class="title">用户</span>
		   <span class="arrow"></span>
		   </a>
		   <ul class="sub">
			  <li><a href="' . BURL('users/add') . '">添加用户</a></li>
			  <li><a href="' . BURL('users') . '">用户列表</a></li>
		   </ul>
		</li>
		<li class="has-sub">
		   <a href="#">
		   <i class="i-others"></i> 
		   <span class="title">其它</span>
		   <span class="arrow"></span>
		   </a>
		   <ul class="sub">
			  <li><a href="' . BURL('news') . '">站点新闻</a></li>
			  <li><a href="' . BURL('contents') . '">常态内容</a></li>
		   </ul>
		</li>
		<li class="has-sub">
		   <a href="#">
		   <i class="i-settings"></i> 
		   <span class="title">系统</span>
		   <span class="arrow"></span>
		   </a>
		   <ul class="sub">
			  <li><a href="' . BURL('settings') . '">网站设置</a></li>
			  <li><a href="' . BURL('language') . '">语言管理</a></li>
			  <li><a href="' . BURL('template') . '">模板管理</a></li>
			  <li><a href="' . BURL('database') . '">数据维护</a></li>
			  <li><a href="' . BURL('phpinfo') . '">环境信息</a></li>
			  <li><a href="' . BURL('upgrade') . '">系统升级</a></li>
		   </ul>
		</li>
		<li class="end"></li>
	</ul>
</div>
</td>

<td valign="top" class="maintd">
  <div class="maindiv">
	 <div id="main">';
	}

	/**
	 * 输出页脚 page_footer
	 */
    protected function page_footer($sysinfo = ''){
		global $sys_starttime;

		$mtime = explode(' ', microtime());
		$sys_runtime = number_format(($mtime[1] + $mtime[0] - $sys_starttime), 3);
		echo '<div class=sysinfo>'.date("Y").' &copy; '.APP_NAME.'('.APP_VERSION.') <a href="http://www.weentech.com" target="_blank">weentech.com</a> Done in '.$sys_runtime.' second(s), '.$this->db->query_nums.' queries, GMT' .$this->config['siteTimezone'].' ' .DisplayDate('', '', 1).'</div>
		</div>
  </div>
</td>
</tr>
</table>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		//调整高度
		$("#container").height($(window).height()-40); 
		$(window).resize(function() {
			$("#container").height($(window).height()-40);
		});

		App.init();//左侧菜单初始化

		var a101 = $("#topbar"); //顶部下接菜单
		a101.find("dl").Jdropdown({delay: 0}, function(a){});

		//全选checkbox
		$("#checkAll").click(function(e){
			$("input[name=\'" + $(this).attr("for") + "\']").attr("checked", this.checked);
		});
	});
</script>

</body>
</html>';
	}


	/**
	 * 析构函数 输出页脚
	 */
	public function __destruct(){
		//登录成功才允许在析构函数中输出面页底部. 未登录时, 有登录页面, 互不冲突
		if($this->admin AND !$this->ajax) $this->page_footer();
	}

}

?>