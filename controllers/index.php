<?php if(!defined('ROOT')) die('Access denied.');

class c_index extends SWeb{

    public function index(){

		$this->assign('menu', 'home'); //菜单样式
		$this->assign('title', $this->langs['home'] . ' - ' . $this->title); //首页标题

		if(IS_CHINESE){
			$news_sql = "SELECT n_id, title, linkurl, clicks, created "; //最新公司新闻
			$new_and_re_sql = "SELECT pro_id, cat_id, path, filename, price, title, clicks, created "; //最新和推荐产品
		}else{
			$news_sql = "SELECT n_id, title_en AS title, linkurl_en AS linkurl, clicks, created ";
			$new_and_re_sql = "SELECT pro_id, cat_id, path, filename, price_en AS price, title_en AS title, clicks, created ";
		}

		$getnews = $this->db->getAll($news_sql . " FROM " . TABLE_PREFIX . "news  WHERE is_show = 1 ORDER BY sort DESC LIMIT 9");
		$this->assign('news', $getnews); //分配公司新闻

		$newproducts = $this->db->getAll($new_and_re_sql . " FROM " . TABLE_PREFIX . "product  WHERE is_show = 1 ORDER BY sort DESC LIMIT 8");
		$this->assign('newproducts', $newproducts); //分配最新产品

		$recommends = $this->db->getAll($new_and_re_sql . " FROM " . TABLE_PREFIX . "product  WHERE is_show = 1 AND is_best = 1 ORDER BY sort DESC LIMIT 5");
		$this->assign('recommends', $recommends); //分配推荐产品

		$this->display('index.tpl');
	} 


}

?>