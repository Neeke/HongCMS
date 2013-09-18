<?php if(!defined('ROOT')) die('Access denied.');

class c_products extends SWeb{
	public function __construct(){
		parent::__construct();

		$this->id = ForceIntFrom('id'); //当前产品ID
		$this->cat = ForceIntFrom('cat'); //当前分类ID
	}

    public function index(){
		//如果有产品ID则显示产品, 其它情况显示分类
		if($this->id){
			$this->show_product();
		}else{
			$this->show_category();
		}
	}

	//按分类ID获得多级导航分类链接
	private function GetCategorylinks($cat){
		$sReturn = $this->langs['nav'].'<a href="'.URL('products?cat=' . $cat).'">'.ShortTitle($this->pcategories[$cat]['name'], 36).'</a>';

		if($this->pcat_ids[$cat]){//如果有父分类
			$sReturn = $this->GetCategorylinks($this->pcat_ids[$cat]) . $sReturn;
		}

		return $sReturn;
	}

	//按分类ID获取当前所有分类的下级分类
	private function GetSubCats($cat){
		$cats = $this->pcat_ids;
		$sReturn = '';

		foreach($cats as $id => $pid){
			if($cat == $pid){
				$sReturn .= ",".$id . $this->GetSubCats($id);
			}
		}
		
		return $sReturn;
	}

	//显示产品
    private function show_product(){
		$id = $this->id; //当前产品ID
		$this->assign('menu', 'products'); //菜单样式

		if(IS_CHINESE){
			$product_sql = "SELECT pro_id, cat_id, is_best, username, path, filename, price, title, keywords, content, clicks, created ";
			$prev_next_sql = " title ";
		}else{
			$product_sql = "SELECT pro_id, cat_id, is_best, username, path, filename, price_en AS price, title_en AS title, keywords_en AS keywords, content_en AS content, clicks, created ";
			$prev_next_sql = " title_en AS title ";
		}

		$product = $this->db->getOne($product_sql . " FROM " . TABLE_PREFIX . "product WHERE is_show = 1 AND pro_id='$id'");

		if(!$product OR !array_key_exists($product['cat_id'], $this->pcats_ok)){
			$this->assign('errorinfo', $this->langs['er_noproduct']); //产品不存在或产品所属分类未发布, 输出错误信息
		}else{
			$cat = $product['cat_id'];//当前产品的分类ID 

			//获取上一个和下一个产品
			//$prev_product = $this->db->getOne("SELECT pro_id, path, filename," . $prev_next_sql . " FROM " . TABLE_PREFIX . "product WHERE is_show = 1 AND cat_id = '$cat' AND pro_id > '$id' ORDER BY pro_id ASC");
			//$next_product = $this->db->getOne("SELECT pro_id, path, filename," . $prev_next_sql . " FROM " . TABLE_PREFIX . "product WHERE is_show = 1 AND cat_id = '$cat' AND pro_id < '$id' ORDER BY pro_id DESC");

			//获得组图片, 使用图片延迟加载技术, src改成original或hide
			$getgimages = $this->db->query("SELECT * FROM " . TABLE_PREFIX . "gimage  WHERE is_show = 1 AND pro_id = '$id' ORDER BY g_id ASC");

			$step = 0;
			$counts = 2; //前台加一个主图片, 从2开始, 每次显示8个图片
			$gimages = '';

			while($gimage = $this->db->fetch($getgimages)){
				if($counts == 1) $gimages .= '<ul class="lev_brandUL"><table><tr>';

				$gimages .= '<td><img ' . Iif($step, 'hide', 'original') . '="' . GetImageURL($gimage['path'], $gimage['filename']) . '" width="80" step="' . $step . '" ></td>';

				if($counts == 8){
					$gimages .= '</tr></table></ul>';
					$step += 1;
					$counts = 1;
				}else{
					$counts += 1;
				}
			}

			if($gimages){
				$gimages = '<ul class="lev_brandUL"><table><tr><td><img class="now" width="80" now="1" original="' . GetImageURL($product['path'], $product['filename']) . '" step="0" ></td>' . $gimages;

				if(substr($gimages, -4) != '</ul>') $gimages .= '</tr></table></ul>'; //如果没有</ul>封闭加上
			}

			//获取当前产品分类的导航栏链接
			$pagenav_more = $this->GetCategorylinks($cat);
			$pagenav_more .= $this->langs['nav'] . '<span class=title>' . $product['title'] . '</span>';

			$this->assign('description',  $product['keywords'] . ','. $this->description);
			$this->assign('keywords',  $product['keywords'] . ','. $this->keywords);
			$this->assign('title', $product['title'] . ' - ' . $this->pcategories[$cat]['name'] . ' - ' . $this->langs['products'] . ' - ' . $this->title); //标题

			$this->assign('product', $product); //分配产品
			//$this->assign('prev_product', $prev_product); //上一个产品
			//$this->assign('next_product', $next_product); //下一个产品
			$this->assign('gimages', $gimages); //分配组图片

			add_clicks($id); //增加点击次数
		}

		$pagenav = '<a href="' . URL() . '">' . $this->langs['home'] . '</a>' . $this->langs['nav'] . '<a href="' . URL('products') . '">' . $this->langs['products'] . '</a>' . $pagenav_more;

		$this->assign('pagenav', $pagenav); //分配导航栏

		$this->display('product.tpl');
	}

	//显示产品分类
    private function show_category(){
		$cat = $this->cat; //当前分类ID
		$keyword = ForceStringFrom('s'); //获得搜索关键词
		$page = ForceIntFrom('p', 1); //当前页
		$NumPerPage = 20;   //每页显示的产品数量
		$start = $NumPerPage * ($page-1);  //分页的每页起始位置

		$this->assign('menu', 'products'); //菜单样式

		$sTitle = $this->langs['products']; //默认页面标题

		//根据搜索或产品分类生成附加的SQL
		$special_sql = "";

		if($keyword){
			$keyword = SafeSearchSql(Iif(IsGet('s'), urldecode($keyword), $keyword));

			$search_sql_ch = '';
			$search_sql_en = '';

			$keywords = explode (' ', str_replace(array('+', ',' , ';'), ' ', $keyword) );
			foreach ($keywords as $value) {
				if($value){
					$search_sql_ch .= " AND (title LIKE '%".$value."%' OR keywords LIKE '%".$value."%' OR content LIKE '%".$value."%') ";
					$search_sql_en .= " AND (title_en LIKE '%".$value."%' OR keywords_en LIKE '%".$value."%' OR content_en LIKE '%".$value."%') ";
				}
			}

			$special_sql .= Iif(IS_CHINESE, $search_sql_ch, $search_sql_en); //搜索的sql
			$sTitle = $this->langs['search'] . ' - ' . $sTitle; //分类名是页面标题
			$this->assign('keyword', $keyword); //分配搜索关键词给header.tpl

		}else if($cat AND array_key_exists($cat, $this->pcats_ok)){

			$sub_categorysql = '';

			if($this->pcategories[$cat]['show_sub'] AND in_array($cat, $this->pcat_ids)){ //当分类设置成显示下级分类产品且有下级分类时
				$sub_categorysql = $this->GetSubCats($cat); //获取所以下级分类的SQL
			}

			if($sub_categorysql){
				$special_sql .= " AND cat_id IN (". $cat . $sub_categorysql. ") ";
			}else{
				$special_sql .= " AND cat_id = $cat ";
			}

			$sTitle = $this->pcategories[$cat]['name'] . ' - ' . $sTitle; //分类名是页面标题

			//获取当前分类的导航栏链接
			$pagenav_more = $this->GetCategorylinks($cat);

			//重新分配页面描述和关键字
			$this->assign('description',  $this->pcategories[$cat]['keywords'] . ','. $this->description);
			$this->assign('keywords',  $this->pcategories[$cat]['keywords'] . ','. $this->keywords);

		}else{//未指定产品分类时, 显示所有未隐藏分类的产品

			$sub_categorysql = $this->GetSubCats(0); //获取所有未隐藏分类及下级分类的SQL

			if($sub_categorysql){
				$special_sql = " AND cat_id IN (". trim($sub_categorysql, ',') . ") ";
			}else{
				$special_sql = "";
			}
		}

		$this->assign('title', $sTitle . ' - ' . $this->title); //分类标题

		if(IS_CHINESE){
			$products_sql = "SELECT pro_id, cat_id, is_best, path, filename, price, title, clicks, created "; //分类产品
		}else{
			$products_sql = "SELECT pro_id, cat_id, is_best, path, filename, price_en AS price, title_en AS title, clicks, created ";
		}

		$products = $this->db->getAll($products_sql . " FROM " . TABLE_PREFIX . "product WHERE is_show = 1 " . $special_sql . " ORDER BY sort DESC LIMIT $start, $NumPerPage");
		$maxrows = $this->db->getOne("SELECT COUNT(pro_id) AS value FROM " . TABLE_PREFIX . "product WHERE is_show = 1 " . $special_sql);
		$totalpages = ceil($maxrows['value'] / $NumPerPage);

		if(!$products){
			$this->assign('errorinfo', Iif($keyword, $this->langs['er_nosearchs'], $this->langs['er_noproducts'])); //错误信息
		}

		$this->assign('products', $products); //分配分类产品

		if($keyword){
			$pagenav_more = $this->langs['nav'] . '<span class=title>' . $this->langs['search'].'</span>: <span class=red>'. $keyword. '</span>&nbsp;&nbsp;(' . $maxrows['value'] . $this->langs['results'] . ')'; //搜索附加的导航栏信息

			$this->assign('pagelist', GetPageList(URL('products'), $totalpages, $page, 10, 's', urlencode($keyword))); //搜索分页
		}else{
			$this->assign('pagelist', GetPageList(URL('products'), $totalpages, $page, 10, 'cat', $cat)); //类别分页
		}

		$pagenav = '<a href="' . URL() . '">' . $this->langs['home'] . '</a>' . $this->langs['nav'] . '<a href="' . URL('products') . '">' . $this->langs['products'] . '</a>' . $pagenav_more;
		$this->assign('pagenav', $pagenav); //分配导航栏

		$this->display('products.tpl');
	} 

}

?>