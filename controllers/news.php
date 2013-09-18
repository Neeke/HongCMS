<?php if(!defined('ROOT')) die('Access denied.');

class c_news extends SWeb{
	public function __construct(){
		parent::__construct();

		$this->id = ForceIntFrom('id'); //当前新闻ID
	}

    public function index(){
		//如果有新闻ID则显示新闻, 其它情况所有新闻
		if($this->id){
			$this->show_new();
		}else{
			$this->show_list();
		}
	}

	//显示新闻
    private function show_new(){
		$id = $this->id; //当前新闻ID
		$this->assign('menu', 'news'); //菜单样式

		if(IS_CHINESE){
			$news_sql = "SELECT n_id, title, linkurl, keywords, content, clicks, created ";
			$prev_next_sql = " title, linkurl ";
			$product_sql = "SELECT pro_id, cat_id, path, filename, price, title, clicks, created "; //随机产品
		}else{
			$news_sql = "SELECT n_id, title_en AS title, linkurl_en AS linkurl, keywords_en AS keywords, content_en AS content, clicks, created ";
			$prev_next_sql = " title_en AS title, linkurl_en AS linkurl ";
			$product_sql = "SELECT pro_id, cat_id, path, filename, price_en AS price, title_en AS title, clicks, created ";
		}

		$news = $this->db->getOne($news_sql . " FROM " . TABLE_PREFIX . "news WHERE is_show = 1 AND n_id='$id'");

		if(!$news){
			$this->assign('errorinfo', $this->langs['er_nonew']); //错误信息
		}else{
			if($news['linkurl']){//如果新闻有链接则跳转
				header("Location: $news[linkurl]");
				exit();
			}

			//获取上一个和下一个新闻
			$prev_news = $this->db->getOne("SELECT n_id, " . $prev_next_sql . " FROM " . TABLE_PREFIX . "news WHERE is_show = 1 AND n_id > '$id' ORDER BY n_id ASC");
			$next_news = $this->db->getOne("SELECT n_id, " . $prev_next_sql . " FROM " . TABLE_PREFIX . "news WHERE is_show = 1 AND n_id < '$id' ORDER BY n_id DESC");

			//获取当前新闻分类的导航栏链接
			$pagenav_more = $this->langs['nav'] . '<span class=title>' . $news['title'] . '</span>';

			$this->assign('description',  $news['keywords'] . ','. $this->description);
			$this->assign('keywords',  $news['keywords'] . ','. $this->keywords);
			$this->assign('title', $news['title'] . ' - ' . $this->langs['news'] . ' - ' . $this->title); //标题

			$this->assign('news', $news); //分配新闻
			$this->assign('prev_news', $prev_news); //上一个新闻
			$this->assign('next_news', $next_news); //下一个新闻

			add_clicks($id, 'news'); //增加点击次数
		}

		$products = $this->db->getAll($product_sql . " FROM " . TABLE_PREFIX . "product  WHERE is_show = 1 ORDER BY rand() LIMIT 10");
		$this->assign('products', $products); //分配随机产品

		$pagenav = '<a href="' . URL() . '">' . $this->langs['home'] . '</a>' . $this->langs['nav'] . '<a href="' . URL('news') . '">' . $this->langs['news'] . '</a>' . $pagenav_more;

		$this->assign('pagenav', $pagenav); //分配导航栏

		$this->display('new.tpl');
	}

	//显示全部新闻
    private function show_list(){
		$page = ForceIntFrom('p', 1); //当前页
		$NumPerPage = 20;   //每页显示的新闻数量
		$start = $NumPerPage * ($page-1);  //分页的每页起始位置

		$this->assign('menu', 'news'); //菜单样式

		$this->assign('title', $this->langs['news'] . ' - ' . $this->title); //分配标题

		if(IS_CHINESE){
			$news_sql = "SELECT n_id, title, linkurl, clicks, created ";
			$new_pro_sql = "SELECT pro_id, cat_id, path, filename, price, title, clicks, created "; //最新产品
		}else{
			$news_sql = "SELECT n_id, title_en AS title, linkurl_en AS linkurl, clicks, created ";
			$new_pro_sql = "SELECT pro_id, cat_id, path, filename, price_en AS price, title_en AS title, clicks, created ";
		}

		$news = $this->db->getAll($news_sql . " FROM " . TABLE_PREFIX . "news WHERE is_show = 1 ORDER BY sort DESC LIMIT $start, $NumPerPage");
		$maxrows = $this->db->getOne("SELECT COUNT(n_id) AS value FROM " . TABLE_PREFIX . "news WHERE is_show = 1");
		$totalpages = ceil($maxrows['value'] / $NumPerPage);

		if(!$news){
			$this->assign('errorinfo', $this->langs['er_nonews']); //错误信息
		}

		$this->assign('news', $news); //分配新闻
		$this->assign('start', $start);
		$this->assign('pagelist', GetPageList(URL('news'), $totalpages, $page, 10)); //类别分页

		$newproducts = $this->db->getAll($new_pro_sql . " FROM " . TABLE_PREFIX . "product  WHERE is_show = 1 ORDER BY sort DESC LIMIT 10");
		$this->assign('newproducts', $newproducts); //分配最新产品

		$pagenav = '<a href="' . URL() . '">' . $this->langs['home'] . '</a>' . $this->langs['nav'] . '<a href="' . URL('news') . '">' . $this->langs['news'] . '</a>';
		$this->assign('pagenav', $pagenav); //分配导航栏

		$this->display('news.tpl');
	} 

}

?>