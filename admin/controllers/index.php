<?php if(!defined('ROOT')) die('Access denied.');

class c_index extends SAdmin{

    function index($Path){

		SubMenu('欢迎进入 '.APP_NAME.' 管理中心', array(
			array('添加产品', 'products/add'),
			array('添加文章', 'articles/add'),
			array('添加新闻', 'news/add'),
			array('切换模板', 'template')
		));

		$basedata = $this->db->getOne("
		SELECT (select COUNT(*)  FROM " . TABLE_PREFIX . "admin) AS totalusers, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "admin WHERE activated =0) AS noactiveusers, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "product) AS totalproducts, 
		(select COUNT(*)  FROM " . TABLE_PREFIX . "product WHERE is_show =0) AS noactiveproducts, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "gimage) AS totalgimages, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "gimage WHERE is_show =0) AS noactivegimages, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "article) AS totalarticles, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "article WHERE is_show =0) AS noactivearticles, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "content) AS totalcontents, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "news) AS totalnews, 
		(select COUNT(*) FROM " . TABLE_PREFIX . "news WHERE is_show =0) AS noactivenews");

		$securityadvise = '<ul><li>欢迎 <u>'.$this->admin->data['nickname'].'</u> 进入后台管理面板! 为了确保系统安全, 请在关闭前点击 <a href="" onclick="'.Confirm('确定退出'.APP_NAME.'系统吗?', 'index/logout').'">退出</a> 安全离开!</li>
		<li>隐私保护: <span class="note2">'.APP_NAME.'郑重承诺, 您在使用本系统时, '.APP_NAME.'开发商不会收集您的任何信息</span>.</li>
		<li>您在使用'.APP_NAME.'时有任何问题, 请访问: <a href="http://www.weentech.com/bbs/" target="_blank">闻泰网络BBS</a>! 或二次开发QQ群: 83582843.</li>
		<li>无论多少, <span class=red>您的赞助</span>将有利于此项目永久免费开源! 支付宝帐号: ofier@sina.com, 姓名: *心红</li></ul>';

		ShowTips($securityadvise, '系统信息');

		BR(2);

		TableHeader('基本数据统计');

		TableRow(array('1)', '用户总数: <font class=greenb>'.$basedata['totalusers'].'</font>', '未激活用户数: <font class='.Iif($basedata['noactiveusers'], 'redb').'>'.$basedata['noactiveusers'].'</font>', '<a class="link-btn" href="'.BURL('users').'">管理用户</a>'));

		TableRow(array('2)', '产品总数: <font class='.Iif($basedata['totalproducts'], 'greenb').'>'.$basedata['totalproducts'].'</font>', '未发布产品数: <font class='.Iif($basedata['noactiveproducts'], 'redb').'>'.$basedata['noactiveproducts'].'</font>', '<a class="link-btn" href="'.BURL('products').'">管理产品</a>'));

		TableRow(array('3)', '产品组图片数: <font class='.Iif($basedata['totalgimages'], 'greenb').'>'.$basedata['totalgimages'].'</font>', '未发布组图片数: <font class='.Iif($basedata['noactivegimages'], 'redb').'>'.$basedata['noactivegimages'].'</font>', '<a class="link-btn" href="'.BURL('products').'">管理产品</a>'));

		TableRow(array('4)', '文章总数: <font class='.Iif($basedata['totalarticles'], 'greenb').'>'.$basedata['totalarticles'].'</font>', '未发布文章数: <font class='.Iif($basedata['noactivearticles'], 'redb').'>'.$basedata['noactivearticles'].'</font>', '<a class="link-btn" href="'.BURL('articles').'">管理文章</a>'));

		TableRow(array('5)', '新闻总数: <font class='.Iif($basedata['totalnews'], 'greenb').'>'.$basedata['totalnews'].'</font>', '未发布新闻数: <font class='.Iif($basedata['noactivenews'], 'redb').'>'.$basedata['noactivenews'].'</font>', '<a class="link-btn" href="'.BURL('news').'">管理新闻</a>'));

		TableRow(array('6)', '常态内容数: <font class='.Iif($basedata['totalcontents'], 'greenb').'>'.$basedata['totalcontents'].'</font>', '&nbsp;', '<a class="link-btn" href="'.BURL('contents').'">常态内容</a>'));

		TableFooter();
    }

}

?>
