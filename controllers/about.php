<?php if(!defined('ROOT')) die('Access denied.');

class c_about extends SWeb{

	public function index(){
		$id = ForceIntFrom('id', 1); //当前常态内容调用ID
		$this->assign('menu', Iif($id==2, 'contact', 'about')); //菜单样式

		if(IS_CHINESE){
			$product_sql = "SELECT pro_id, cat_id, path, filename, price, title, clicks, created "; //随机产品
		}else{
			$product_sql = "SELECT pro_id, cat_id, path, filename, price_en AS price, title_en AS title, clicks, created ";
		}

		$products = $this->db->getAll($product_sql . " FROM " . TABLE_PREFIX . "product  WHERE is_show = 1 ORDER BY rand() LIMIT 10");
		$this->assign('products', $products); //分配随机产品

		$content = GetContent($id);

		if(!$content){
			$this->assign('errorinfo', $this->langs['er_info']); //错误信息
		}else{
			$this->assign('description',  $content['keywords'] . ','. $this->description);
			$this->assign('keywords',  $content['keywords'] . ','. $this->keywords);
			$this->assign('title', $content['title'] . ' - ' . $this->title); //标题
			$this->assign('content', $content); //常态内容

			$pagenav_more = $this->langs['nav'] . '<a href="' . URL(Iif($id==1, 'about', 'about?id=' . $id)) . '">' . $content['title'] . '</a>';
		}

		$pagenav = '<a href="' . URL() . '">' . $this->langs['home'] . '</a>' . $pagenav_more;

		$this->assign('id', $id);
		$this->assign('pagenav', $pagenav); //分配导航栏

		$this->display('about.tpl');
	}

}

?>