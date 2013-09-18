<?php if(!defined('ROOT')) die('Access denied.');

class c_products extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

		$this->upload_path = ROOT . 'uploads/';

		@set_time_limit(0);  //解除时间限制
	}

	//ajax动作集合, 能过action判断具体任务
    public function ajax(){

		$action = ForceStringFrom('action');

		if($action == 'deletelast'){
			$files = $this->get_upload_files();

			foreach($files AS $Item){
				@unlink($this->upload_path . $Item);
			}
		}elseif($action == 'deleteone'){
			$file = ForceStringFrom('file');
			@unlink($this->upload_path . $file);
		}

		die($this->json->encode($this->ajax));
	}

	private function GetSelect($selectedid =0, $selectname = 'cat_id'){
		$sReturn = '<select name="' . $selectname . '"><option value="0">-- 请选择 --</option>';
		$categories = $this->db->getAll("SELECT cat_id, p_id, name, name_en, counts  FROM " . TABLE_PREFIX . "pcat ORDER BY sort");
		$sReturn .= $this->GetOptions($categories, $selectedid);
		$sReturn .= '</select>';

		return $sReturn;
	}

	private function GetOptions($categories, $selectedid = 0, $p_id = 0, $sublevelmarker = ''){
		if($p_id) $sublevelmarker .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		foreach($categories as $value){
			if($p_id == $value['p_id']){
				$sReturn .= '<option '. Iif(!$p_id, 'style="color:#cc4911;font-weight:bold;"') . ' value="' . $value['cat_id'] . '" ' . Iif($selectedid == $value['cat_id'], 'SELECTED', '') . '>' . $sublevelmarker . $value['name'] . '-' . $value['name_en'] . ' (' . $value['counts'] . ')</option>';

				$sReturn .= $this->GetOptions($categories, $selectedid, $value['cat_id'], $sublevelmarker);
			}
		}

		return $sReturn;
	}

	private function UploadImage($imagefile, $file_path_str, $imagename) {
		$uploaddir = $this->upload_path;
		$file_path = '';

		$temp = explode('/', $file_path_str);

		foreach($temp AS $path){
			$file_path .= $path . '/';
			MakeDir($uploaddir . $file_path);
		}

		if((function_exists('move_uploaded_file') AND @move_uploaded_file($imagefile['tmp_name'], $uploaddir.$file_path.$imagename)) OR @rename($imagefile['tmp_name'], $uploaddir.$file_path.$imagename))	{

			$image_size = @getimagesize($uploaddir.$file_path.$imagename);

			CreateImageFile($uploaddir.$file_path.$imagename, $uploaddir.$file_path. $imagename . '_l.jpg', $this->config['siteLarge']);

			if ($image_size[0] > $this->config['siteLarge'] || $image_size[1] > $this->config['siteLarge']) {
				CreateImageFile($uploaddir.$file_path.$imagename . '_l.jpg', $uploaddir.$file_path. $imagename . '_m.jpg', $this->config['siteMiddle']);
				CreateImageFile($uploaddir.$file_path.$imagename . '_m.jpg', $uploaddir.$file_path. $imagename . '_s.jpg', $this->config['siteSmall']);
			}else{
				CreateImageFile($uploaddir.$file_path.$imagename, $uploaddir.$file_path. $imagename . '_m.jpg', $this->config['siteMiddle']);
				CreateImageFile($uploaddir.$file_path.$imagename, $uploaddir.$file_path. $imagename . '_s.jpg', $this->config['siteSmall']);
			}

			@unlink($uploaddir.$file_path.$imagename);

			return true;
		
		}else{
			return false;
		}
	}

	//处理并保存已上传的组图片
	private function SaveGroupImage($imagefile, $file_path_str, $imagename) {
		$uploaddir = $this->upload_path;
		$file_path = '';

		$temp = explode('/', $file_path_str);

		foreach($temp AS $path){
			$file_path .= $path . '/';
			MakeDir($uploaddir . $file_path);
		}

		$image_size = @getimagesize($uploaddir.$imagefile);

		CreateImageFile($uploaddir.$imagefile, $uploaddir.$file_path. $imagename . '_l.jpg', $this->config['siteLarge']);

		if ($image_size[0] > $this->config['siteLarge'] || $image_size[1] > $this->config['siteLarge']) {
			CreateImageFile($uploaddir.$file_path.$imagename . '_l.jpg', $uploaddir.$file_path. $imagename . '_m.jpg', $this->config['siteMiddle']);
			CreateImageFile($uploaddir.$file_path.$imagename . '_m.jpg', $uploaddir.$file_path. $imagename . '_s.jpg', $this->config['siteSmall']);
		}else{
			CreateImageFile($uploaddir.$imagefile, $uploaddir.$file_path. $imagename . '_m.jpg', $this->config['siteMiddle']);
			CreateImageFile($uploaddir.$imagefile, $uploaddir.$file_path. $imagename . '_s.jpg', $this->config['siteSmall']);
		}
		@unlink($uploaddir.$imagefile);

		return true;
	}

	//删除图片文件
	private function UnlinkImage($path, $filename) {
		@unlink($this->upload_path . $path . '/' . $filename . '_s.jpg');
		@unlink($this->upload_path . $path . '/' . $filename . '_m.jpg');
		@unlink($this->upload_path . $path . '/' . $filename . '_l.jpg');
	}

	//按pro_id删除产品
	private function DeleteProductById($pro_id) {
		$product = $this->db->getOne("SELECT pro_id, cat_id, path, filename FROM " . TABLE_PREFIX . "product WHERE pro_id = '$pro_id'");

		if(empty($product)) return;

		$this->db->exe("DELETE FROM " . TABLE_PREFIX . "product WHERE pro_id = '$pro_id'");
		$this->db->exe("UPDATE " . TABLE_PREFIX . "pcat SET counts = (counts-1) WHERE cat_id = '$product[cat_id]'");

		$this->UnlinkImage($product['path'], $product['filename']); //删除主图片

		$this->DeleteGroupImage($pro_id); //删除组图片
	}

	//按产品pro_id 或组图片g_id 删除其图片
	private function DeleteGroupImage($pro_id, $g_id = 0) {
		$more = '';
		
		if($g_id) $more = " AND g_id ='$g_id'";

		$getimages = $this->db->query("SELECT g_id, path, filename FROM " . TABLE_PREFIX . "gimage WHERE pro_id = '$pro_id' " . $more);

		while($image = $this->db->fetch($getimages)){
			$this->UnlinkImage($image['path'], $image['filename']);
		}
		
		$this->db->exe("DELETE FROM " . TABLE_PREFIX . "gimage WHERE pro_id = '$pro_id' " . $more);
	}

	//更新保存多个产品
	public function updateproducts(){
		if(IsPost('updateproducts')){
			$pro_ids = $_POST['pro_ids'];
			$sorts   = $_POST['sorts'];
			$is_shows   = $_POST['is_shows'];
			$is_bests   = $_POST['is_bests'];
			for($i = 0; $i < count($pro_ids); $i++){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "product SET sort = '". ForceInt($sorts[$i])."',
				is_show = '". ForceInt($is_shows[$i])."',
				is_best = '". ForceInt($is_bests[$i])."'
				WHERE pro_id = '". ForceInt($pro_ids[$i])."'");
			}
		}else{
			$pro_ids = $_POST['deletepro_ids'];
			for($i=0; $i<count($pro_ids); $i++){
				$this->DeleteProductById(ForceInt($pro_ids[$i]));
			}
		}
		Success('products');
	}

	//$search 查询的关键字 $type时间条件 大于小于什么的  $time时间 $cat_id产品分类的id
	public function GetSearchSql($search, $type, $time, $cat_id){
		$Where = "";

		if($cat_id > 0){
			$Where .= ' cat_id=' . $cat_id;
		}elseif($cat_id == -1){
			$Where .= " is_show=0 ";
		}

		if($search){
			$Where .= Iif($Where, " AND ") . " (title like '%$search%' OR title_en like '%$search%' OR content like '%$search%' OR content_en like '%$search%' OR keywords like '%$search%' OR keywords_en like '%$search%') ";
		}

		if($time){
			$timearr=explode('-',$time);
			$year=$timearr[0];
			$month=$timearr[1];
			$day=$timearr[2];
			$bigtime=mktime(0,0,0,$month,$day+1,$year);
			$littletime=mktime(0,0,0,$month,$day,$year);
			if($type=='eq'){
				$twhere = " created > $littletime AND created < $bigtime ";
			}elseif($type=='gr'){
				$twhere = " created > $bigtime ";
			}elseif($type=='le'){
				$twhere = " created > $littletime ";
			}

			$Where .= Iif($Where, " AND ") . $twhere;
		}

		$Where = Iif($Where, " WHERE " . $Where, " ");

		return $Where;
	}


	//获取已上传的组图片文件
	private function get_upload_files($more = 0){
		$files = array();
		$FolderHandle = @opendir($this->upload_path);

		while (false !== ($Item = readdir($FolderHandle))) {
			if ($Item != '.' AND $Item != '..' AND preg_match("/^image_".$this->admin->data['userid']."_/i", $Item) AND $imagesize = @getimagesize($this->upload_path . $Item) AND $imagesize[2] == '2') {
				if($more){
					$files[] = '<a class="open">' . $Item . '</a>&nbsp;&nbsp;&nbsp;&nbsp;' . $imagesize[0] . ' * ' . $imagesize[1];

				}else{
					$files[] = $Item;
				}
			}
		}

		@closedir($this->upload_path);
		return $files;
	}

	public function save(){
		$pro_id = ForceIntFrom('pro_id');

		$is_show = ForceIntFrom('is_show');
		$is_best = ForceIntFrom('is_best');
		$sort = ForceIntFrom('sort');
		$cat_id = ForceIntFrom('cat_id');
		$oldcat_id = ForceIntFrom('oldcat_id');

		$price = ForceStringFrom('price');
		$price_en = ForceStringFrom('price_en');
		$title = ForceStringFrom('title');
		$title_en = ForceStringFrom('title_en');
		$keywords = ForceStringFrom('keywords');
		$keywords_en = ForceStringFrom('keywords_en');
		$content = ForceStringFrom('content');
		$content_en = ForceStringFrom('content_en');

		$pro_path = ForceStringFrom('pro_path');
		$pro_filename = ForceStringFrom('pro_filename');
		
		$deletethisproduct     = ForceIntFrom('deletethisproduct');

		if($deletethisproduct AND $pro_id){//删除产品
			$this->db->exe("DELETE FROM " . TABLE_PREFIX ."product where pro_id='$pro_id'");
			$this->db->exe("UPDATE " . TABLE_PREFIX . "pcat SET counts = (counts-1) WHERE cat_id = '$oldcat_id'");

			//删除产品主图片
			$this->UnlinkImage($pro_path, $pro_filename); //删除多个图片

			//删除组图片
			$this->DeleteGroupImage($pro_id);

			Success('products');
		}

		$imagefile         = $_FILES['imagefile'];
		$valid_image_types = array('image/pjpeg',	'image/jpeg', 'image/jpg');

		$time = time();
		$username = Iif($this->admin->data['nickname'], $this->admin->data['nickname'], $this->admin->data['username']);
		$userid = $this->admin->data['userid'];

		if(!$title){
			$errors[] = '产品标题不能为空！';
		}

		if(!$title_en){
			$errors[] = '产品英文标题不能为空！';
		}

		if(!$cat_id){
			$errors[] = '您没有选择产品分类！';
		}

		if (!function_exists('imagecreatetruecolor')){
			$errors[] ='服务器PHP环境不支持GD2库, 无法上传图片文件!';
		}

		if (!is_dir($this->upload_path)){
			$errors[] ='保存图片的文件夹: uploads/ 不存在!';
		}else if (!is_writable($this->upload_path)){
			@chmod($this->upload_path, 0777);
			if(!is_writable($this->upload_path)) {
				$errors[] = '保存图片的文件夹: uploads/ 不可写! - 文件夹属性需改为: 0777';
			}
		}

		if(isset($errors))	Error($errors, Iif($pro_id, '编辑产品错误', '添加产品错误'));

		if($pro_id){//编辑产品
			$filesize = $imagefile['size'];

			if($filesize > 0){//有主图片文件上传时
				if(!in_array($imagefile['type'], $valid_image_types)){
					$errors[] = '无效的图片文件类型!';
				}elseif (!IsUploadedFile($imagefile['tmp_name']) || !($imagefile['tmp_name'] != 'none' && $imagefile['tmp_name'] && $imagefile['name'])){
					$errors[] ='上传的文件无效!';
				}

				if(isset($errors)) Error($errors, '编辑产品错误');

				$file_path = DisplayDate(time(), 'Y/md');
				$imagename = md5(uniqid(COOKIE_KEY.time()));

				if(!$this->UploadImage($imagefile, $file_path, $imagename)){
					Error('处理产品图片发生错误!', '编辑产品错误');
				}
			}

			$this->db->exe("UPDATE " . TABLE_PREFIX . "product SET 
			sort= '$sort',
			cat_id= '$cat_id',
			is_show= '$is_show',
			is_best= '$is_best',
			" . Iif($filesize AND $file_path AND $imagename, "path = '$file_path', filename = '$imagename',") . "
			price = '$price',
			price_en = '$price_en',
			title = '$title',
			title_en = '$title_en',
			content = '$content',
			content_en = '$content_en',
			keywords = '$keywords',
			keywords_en = '$keywords_en'
			WHERE pro_id = ".$pro_id);

			if($oldcat_id != $cat_id){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "pcat SET counts = (counts+1) WHERE cat_id = '$cat_id'");
				$this->db->exe("UPDATE " . TABLE_PREFIX . "pcat SET counts = (counts-1) WHERE cat_id = '$oldcat_id'");
			}

			//重新上传了主图片时删除原有图片文件
			if($filesize AND $file_path AND $imagename){
				$this->UnlinkImage($pro_path, $pro_filename); //删除多个图片
			}

			//设置或删除已有组图片
			$gis_shows   = $_POST['gis_shows'];
			$deletegimages   = $_POST['deletegimages'];

			for($i = 0; $i < count($deletegimages); $i++){
				$this->DeleteGroupImage($pro_id, ForceInt($deletegimages[$i]));
			}

			$this->db->exe("UPDATE " . TABLE_PREFIX . "gimage SET is_show = 0 WHERE pro_id   = '$pro_id'");

			for($i = 0; $i < count($gis_shows); $i++){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "gimage SET 
				is_show     = 1
				WHERE pro_id = '$pro_id' AND g_id   = ". ForceInt($gis_shows[$i]));
			}

			//处理并保存上传组图片
			$uploaded_images = $this->get_upload_files();
			$file_path = DisplayDate(time(), 'Y/md');

			foreach($uploaded_images AS $file){
				$imagename = md5(uniqid(COOKIE_KEY.microtime()));

				if($this->SaveGroupImage($file, $file_path, $imagename)){
					$this->db->exe("INSERT INTO " . TABLE_PREFIX . "gimage (pro_id, path, filename) VALUES ('$pro_id', '$file_path', '$imagename') ");
				}
			}

			Success('products/edit?pro_id=' . $pro_id);
		}else{//添加产品
			$filesize = $imagefile['size'];
			if($filesize == 0)	{
				$errors[] = '未选择图片文件, 或文件大小超过了服务器PHP环境允许上传的文件大小: '.ini_get('upload_max_filesize');
			}elseif(!in_array($imagefile['type'], $valid_image_types)){
				$errors[] = '无效的图片文件类型!';
			}elseif (!IsUploadedFile($imagefile['tmp_name']) || !($imagefile['tmp_name'] != 'none' && $imagefile['tmp_name'] && $imagefile['name'])){
				$errors[] ='上传的文件无效!';
			}

			if(isset($errors)) Error($errors, '添加产品错误');

			$file_path = DisplayDate(time(), 'Y/md');
			$imagename = md5(uniqid(COOKIE_KEY.time()));

			if($this->UploadImage($imagefile, $file_path, $imagename)){
			
				$this->db->exe("INSERT INTO " . TABLE_PREFIX . "product (cat_id, is_show, is_best, userid, username, path, filename, price, price_en, title, title_en, content, content_en, keywords, keywords_en, clicks, created) VALUES ('$cat_id', '$is_show', '$is_best', '$userid', '$username', '$file_path', '$imagename', '$price', '$price_en', '$title', '$title_en', '$content', '$content_en', '$keywords', '$keywords_en', '0', '$time') ");

				$lastid = $this->db->insert_id;
				$this->db->exe("UPDATE " . TABLE_PREFIX . "product SET sort = '$lastid' WHERE pro_id = '$lastid'");
				$this->db->exe("UPDATE " . TABLE_PREFIX . "pcat SET counts = (counts+1) WHERE cat_id = '$cat_id'");

				//处理并保存组图片
				$uploaded_images = $this->get_upload_files();

				foreach($uploaded_images AS $file){
					$imagename = md5(uniqid(COOKIE_KEY.microtime()));

					if($this->SaveGroupImage($file, $file_path, $imagename)){
						$this->db->exe("INSERT INTO " . TABLE_PREFIX . "gimage (pro_id, path, filename) VALUES ('$lastid', '$file_path', '$imagename') ");
					}
				}

			}else{
				Error('处理产品图片发生错误!', '添加产品错误');
			}

			Success('products/edit?pro_id=' . $lastid);
		}
	}

	public function index(){
		$NumPerPage = 10;   //每页显示的产品列表的数量
		$page = ForceIntFrom('p', 1);   //页码
		$search = ForceStringFrom('s');   //搜索的内容
		$type = ForceStringFrom('type');   //搜索的内容
		$time = ForceStringFrom('t');
		$cat_id = ForceIntFrom('c');   //按分类搜索时所选的分类的id
		if(IsGet('s')){
			$search = urldecode($search);
		}

		$start = $NumPerPage * ($page-1);  //分页的每页起始位置

		if($search OR $time OR $cat_id){
			SubMenu('产品列表', array(array('添加产品', 'products/add'), array('全部产品', 'products')));
		}else{
			SubMenu('产品列表', array(array('添加产品', 'products/add')));
		}

		$newcategories = array();
		$getcategories = $this->db->query("SELECT cat_id, p_id, name, name_en, counts FROM " . TABLE_PREFIX . "pcat ORDER BY sort");
		while($category = $this->db->fetch($getcategories)){
			$newcategories[$category['cat_id']] = $category;
		}

		echo '<script type="text/javascript" src="'.SYSDIR.'public/js/DatePicker/WdatePicker.js"></script>
		<form method="post" action="'.BURL('products').'" name="searchform">';

		TableHeader('搜索产品');
		TableRow('<center><label>搜索:</label>&nbsp;<input type="text" name="s" size="12" value="' . $search . '">&nbsp;&nbsp;&nbsp;<label>发布时间:</label>&nbsp;<select name="type"><option value="gr" ' . Iif($type == 'gr', 'SELECTED') . '>大于(>)</option><option value="eq" ' . Iif($type == 'eq', 'SELECTED') . '>等于(=)</option><option value="le" ' . Iif($type == 'le', 'SELECTED') . '>小于(<)</option></select> <input type="text" name="t" onClick="WdatePicker()" value="' . $time . '" size="12">&nbsp;&nbsp;&nbsp;<label>分类:</label>&nbsp;<select name="c"><option value="0">全部分类</option><option style="color:red;" value="-1" ' . Iif($cat_id == -1, 'SELECTED') . '>未发布的产品</option>' . $this->GetOptions($newcategories,$cat_id) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="searchproduct" value="搜索产品" class="cancel"></center>');
		TableFooter();
		echo '</form>';

		$Where = $this->GetSearchSql($search, $type, $time, $cat_id);
		$title = Iif($Where, '搜索到的产品列表', '全部产品列表');

		$getproducts = $this->db->query("SELECT pro_id, sort, cat_id, is_show, is_best, userid, username, path, filename, title, title_en, clicks, created FROM " . TABLE_PREFIX . "product " . $Where . " ORDER BY is_show ASC, sort DESC LIMIT $start, $NumPerPage");
		$maxrows = $this->db->getOne("SELECT COUNT(pro_id) AS value FROM " . TABLE_PREFIX . "product " . $Where);

		echo '<form method="post" action="'.BURL('products/updateproducts').'" name="productsform">';
		TableHeader($title.'('.$maxrows['value'].'个)');
		TableRow(array('排序', '缩图', '产品标题(编辑)', '所属分类', '作者', '状态', '推荐', '点击数', '日期', '浏览', '<input type="checkbox" id="checkAll" for="deletepro_ids[]"> <label for="checkAll">删除</label>'), 'tr0');

		if($maxrows['value'] < 1){
			TableRow('<center><BR><font class=redb>未搜索到任何产品!</font><BR><BR></center>');
		}else{
			while($product = $this->db->fetch($getproducts)){

				$title = Iif($product['is_show'],  ShortTitle($product['title'], 28), '<font class=red><s>' . ShortTitle($product['title'], 28) . '</s></font>');

				TableRow(array('<input type="hidden" name="pro_ids[]" value="'.$product['pro_id'].'" /><input type="text" name="sorts[]" value="' . $product['sort'] . '" size="4" />',
					'<a href="'.BURL('products/edit?pro_id='.$product['pro_id']).'"><img src="'. GetImageURL($product['path'], $product['filename']).'" width="40" onMouseMove="ShowBigImage();"></a>',
					'<a href="'.BURL('products/edit?pro_id='.$product['pro_id']).'" title="英文: '.$product['title_en'].'">' . $title . '</a>',
					$newcategories[$product['cat_id']]['name'],
					$product['username'],
					'<select name="is_shows[]"><option value="1">发布</option><option style="color:red;" value="0" ' . Iif(!$product['is_show'], 'SELECTED', '') . '>隐藏</option></select>',
					'<select name="is_bests[]"><option value="0">否</option><option style="color:orange;" value="1" ' . Iif($product['is_best'], 'SELECTED', '') . '>是</option></select>',
					$product['clicks'],
					DisplayDate($product['created']),
					Iif($product['is_show'], '<a href="'.URL('products?id=' . $product['pro_id']).'" target="_blank"><img src="' . SYSDIR . 'public/admin/images/view.gif"></a>', '<img src="' . SYSDIR . 'public/admin/images/disview.gif">'),
					'<input type="checkbox" name="deletepro_ids[]" value="' . $product['pro_id'] . '" checkme="group" />'));
			}

			$totalpages = ceil($maxrows['value'] / $NumPerPage);
			if($totalpages > 1){
				TableRow(GetPageList(BURL('products'), $totalpages, $page, 10, 's', urlencode($search), 'c', $cat_id, 't', $time, 'type', $type));
			}

		}

		TableFooter();

		echo '<div class="submit"><input type="submit" name="updateproducts" value="保存更新" class="save"><input type="submit" name="deleteproducts" onclick="'.Confirm('确定删除所选产品吗?<br><br><span class=red>注: 所选产品的全部信息将被永久删除!</span>', 'form').'" value="删除产品" class="cancel"></div></form>';
	}

	//编辑调用add
	public function edit(){
		$this->add();
	}

	public function add(){
		$pro_id = ForceIntFrom('pro_id');

		if($pro_id){  //编辑时的
			SubMenu('产品管理', array(array('添加产品', 'products/add'),array('编辑产品', 'products/edit?pro_id='.$pro_id, 1),array('产品列表', 'products')));
			$product = $this->db->getOne("SELECT * FROM " . TABLE_PREFIX . "product WHERE pro_id = '$pro_id'");

			//组图片
			$getgimages = $this->db->query("SELECT * FROM " . TABLE_PREFIX . "gimage WHERE pro_id = '$pro_id' ORDER BY g_id ASC");

			$gimagetable = '';
			if($this->db->result_nums > 0){
				while($gimage = $this->db->fetch($getgimages)){
					$gimagetable .= '<div class="thumb-group">
					<table>
					<thead>
					<tr>
					<th><img src="'.GetImageURL($gimage['path'], $gimage['filename']).'" onMouseMove="ShowBigImage();"></th>
					</tr>
					</thead>
					<tr>
					<td>
					<div><input type="checkbox" name="gis_shows[]" value="'.$gimage['g_id'].'" '.Iif($gimage['is_show'] == 1, 'CHECKED').'>&nbsp;发布</div>
					<div><input type="checkbox" name="deletegimages[]" value="'.$gimage['g_id'].'">&nbsp;删除</div>
					</td>
					</tr>
					</table>
					</div>';
				}
			}
		}else{
			SubMenu('产品管理', array(array('添加产品', 'products/add', 1),array('产品列表', 'products')));

			$product = array('is_best' => 0, 'is_show' => 1);
		}

		$uploaded_images = $this->get_upload_files(1);
		$uploaded_counts = count($uploaded_images);

		echo '<link href="'. SYSDIR .'public/js/swfupload/css/swfupload.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="'. SYSDIR .'public/js/swfupload/swfupload.js"></script>
		<script type="text/javascript" src="'. SYSDIR .'public/js/swfupload/swfupload.queue.js"></script>
		<script type="text/javascript" src="'. SYSDIR .'public/js/swfupload/swfupload.fileprogress.js"></script>
		<script type="text/javascript" src="'. SYSDIR .'public/js/swfupload/swfupload.handlers.js"></script>
		<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/kindeditor.js"></script>
		<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/lang/zh_CN.js"></script>
		<script type="text/javascript">
			KindEditor.ready(function(K) {
				var editor88 = K.create(\'textarea[name="content"]\', {
					uploadJson : \''.BURL('editor_upload/ajax').'\',
					fileManagerJson : \''.BURL('editor_file_manager/ajax').'\',
					allowFileManager : true,
					afterCreate : function() {
						var self = this;
						K.ctrl(document, 13, function() {
							self.sync();
							K(\'form[name=editorform88]\')[0].submit();
						});
						K.ctrl(self.edit.doc, 13, function() {
							self.sync();
							K(\'form[name=editorform88]\')[0].submit();
						});
					}
				});

				var editor66 = K.create(\'textarea[name="content_en"]\', {
					uploadJson : \''.BURL('editor_upload/ajax').'\',
					fileManagerJson : \''.BURL('editor_file_manager/ajax').'\',
					allowFileManager : true,
					afterCreate : function() {
						var self = this;
						K.ctrl(document, 13, function() {
							self.sync();
							K(\'form[name=editorform88]\')[0].submit();
						});
						K.ctrl(self.edit.doc, 13, function() {
							self.sync();
							K(\'form[name=editorform88]\')[0].submit();
						});
					}
				});

			});

			var swfu;
			window.onload = function() {
				var settings = {
					flash_url : "'. SYSDIR .'public/js/swfupload/swfupload.swf",
					upload_url: "'.BURL('swfupload/ajax').'",
					post_params: {"sessionid": "'.$this->admin->data['sessionid'].'"},
					file_size_limit : "10 MB",
					file_types : "*.jpg;*.jpeg",
					file_types_description : "Image Files",
					file_upload_limit : 60,
					file_queue_limit : 60,
					custom_settings : {
						progressTarget : "fsUploadProgress",
						cancelButtonId : "btnCancel",
						uploadButtonId : "btnUpload",
						filesStatusId: "filesStatus"
					},

					button_image_url: "'. SYSDIR .'public/js/swfupload/images/swfupload_btn_flash.png",
					button_width: "78",
					button_height: "28",
					button_cursor: SWFUpload.CURSOR.HAND,
					button_placeholder_id: "spanButtonPlaceHolder",
					
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
					upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,
					upload_completeinfo_handler : uploadCompleteInfo
				};

				swfu = new SWFUpload(settings);
			 };
		</script>
		<form id="editorform88" name="editorform88" method="post" enctype="multipart/form-data" action="'.BURL('products/save').'">
		<input type="hidden" name="pro_id" value="' . $pro_id . '" />';

		if($pro_id){
			TableHeader('编辑产品: <span class=note>' . Iif($product['title'], $product['title'], '未命名') . '</span>');
		}else{
			TableHeader('添加产品');
		}

		if($uploaded_counts > 0){
			$uploaded_file_str = '';

			foreach($uploaded_images as $value){
				$uploaded_file_str .= '<div>' . (++$key) . ') ' .$value . '</div>';
			}

			TableRow(array('<B>未处理的组图片:</B>', '<font class=redb>重要提示:</font> 您共有<font class=redb> ' .$uploaded_counts .'</font> 个已经上传, 但未正常处理的组图片文件, 如果您不删除它们, 本次保存将自动添加这些组图片. <BR>' . $uploaded_file_str . '<BR><a class="link-btn ajax">全部删除</a>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					$("#main a.open").click(function(e){
						var _me=$(this);
						var filename=_me.html();
						$.dialog({title: "查看及确认删除", lock:true, content:"<img width=\'400\' height=\'400\' src=\''. SYSDIR .'uploads/" + filename + "\'>",okValue:"  删除  ", ok:function(){
								ajax("' . BURL('products/ajax?action=deleteone') . '", {file: filename}, function(data){
									_me.parent().hide();
								});
						},cancelValue:"取消",cancel:true});

						e.preventDefault();
					});

					$("#main a.ajax").click(function(e){
						var _me=$(this);
						$.dialog({title:"操作确认",lock:true,content:"确定删除已上传的图片文件吗?",okValue:"  确定  ",
						ok:function(){
							ajax("' . BURL('products/ajax?action=deletelast') . '", {}, function(data){
								_me.parent().parent().hide();
							});
						},
						cancelValue:"取消",cancel:true});
						e.preventDefault();
					});

				});
			</script>'));
		}

		if($pro_id){
			TableRow(array('<b>' . Iif($product['is_show'], '浏览产品', '<font class=red>待审产品</font>') . ':</b>', Iif($product['is_show'], '<a href="'.URL('products?id=' . $pro_id).'" target="_blank">') . '<input type="hidden" name="pro_path" value="' . $product['path'] . '" /><input type="hidden" name="pro_filename" value="' . $product['filename'] . '" /><img src="'. GetImageURL($product['path'], $product['filename']).'" align="top" style="padding-right:26px;float:left;" onMouseMove="ShowBigImage();">'. Iif($product['is_show'], '</a>')));
		}

		TableRow(array('<B>'. Iif($pro_id, '重新上传主图片', '上传主图片') . ':</B>', '<input name="imagefile" type="file" size="40" />&nbsp;&nbsp;<span class=light>注: <span class=note>仅允许上传JPG类型的图片文件</span>.</span>'));

		if($pro_id){
			TableRow(array('<B>是否删除?</B>', '<input type="checkbox" name="deletethisproduct" value="1"> <b>是:</b> <font class=redb>慎选!</font> <span class=light>如果选择删除, 此产品相关的所有信息将被删除.</span>'));
		}

		TableRow(array('<B>产品名称(<span class=blue>中文</span>):</B>', '<input type="text" name="title" value="' . $product['title'] . '"  size="50" /> <font class=red>* 必填项</font>'));
		TableRow(array('<B>产品名称(<span class=red>英文</span>):</B>', '<input type="text" name="title_en" value="' . $product['title_en'] . '"  size="50" /> <font class=red>* 必填项</font>'));

		TableRow(array('<B>产品分类:</B>', '<input type="hidden" name="oldcat_id" value="' . $product['cat_id'] . '">'.$this->GetSelect($product['cat_id']) . ' <font class=red>* 必填项</font>'));

		if($pro_id){
			TableRow(array('<B>排序编号:</B>', '<input type="text" name="sort" value="' . $product['sort'] . '"  size="10" /> <span class=light>注: 当前分类中产品将按此编号排序.</span>'));
		}

		TableRow(array('<B>是否发布?</B>', '<input type="checkbox" name="is_show" value="1" '.Iif($product['is_show'] == 1, 'CHECKED').'> <b>是:</b> <span class=light>当不发布时, 此产品不在前台显示.</span>'));
		TableRow(array('<B>是否推荐?</B>', '<input type="checkbox" name="is_best" value="1" '.Iif($product['is_best'] == 1, 'CHECKED').'> <b>是:</b> <span class=light>推荐的产品显示在重要的位置.</span>'));

		TableRow(array('<B>产品价格(<span class=blue>中文</span>):</B>', '<input type="text" name="price" value="' . $product['price'] . '" size="30" /> <span class=light>注: 可填写价格及单位.</span>'));
		TableRow(array('<B>产品价格(<span class=red>英文</span>):</B>', '<input type="text" name="price_en" value="' . $product['price_en'] . '" size="30" /> <span class=light>注: 同上</span>'));

		TableRow(array('<B>Meta关键字(<span class=blue>中文</span>):</B>', '<input type="text" name="keywords" value="' . $product['keywords'] . '" size="50" /> <span class=light>注: 产品的Meta关键字, <span class=note>便于搜索引擎收录, 请用英文逗号隔开</span>.</span>'));
		TableRow(array('<B>Meta关键字(<span class=red>英文</span>):</B>', '<input type="text" name="keywords_en" value="' . $product['keywords_en'] . '" size="50" /> <span class=light>注: 同上</span>'));

		TableRow(array('<B>产品正文:</B><BR><span class=light>产品的详细内容.</span>', '
			<div class="ok_tab">
				<div class="ok_tabheader">
					<ul id="tabContent-li-ok_tabOn-">
						<li class="ok_tabOn"><a href="javascript:void(0)" title="中文内容" rel="1" hidefocus="true">中文内容</a></li>
						<li><a href="javascript:void(0)" title="英文内容" rel="2" hidefocus="true">英文内容</a></li>
					</ul>
				</div>
				<div id="tabContent_1" class="tabContent">
				 <textarea name="content" style="width:100%;height:400px;visibility:hidden;" id="content">'.$product['content'].'</textarea>
				</div>

				<div id="tabContent_2" class="tabContent" style="display: none;">
				<textarea name="content_en" style="width:100%;height:400px;visibility:hidden;" id="content_en">'.$product['content_en'].'</textarea>
				</div>

				<div class="ok_tabbottom">
					<span class="tabbottomL"></span>
					<span class="tabbottomR"></span>
				</div>
			</div>
			<script type="text/javascript">new tab(\'tabContent-li-ok_tabOn-\', \'-\');</script>
		'));

		if($pro_id AND $gimagetable){
			TableRow(array('<B>组图片列表:</B>', '<div style="width:800px;">' . $gimagetable . '</div>'));
		}

		TableRow(array('<B>上传组图片:</B><BR><span class=light>组图片是辅助展示图片.</span>', '<div id="swfupload">
			<div class="fieldset">
				<span class="legend">上传文件列表</span>
				<table id="file_table" cellpadding="0" cellspacing="0" class="file_table">
					<thead>
						<tr>
							<th width="30">#</th>
							<th width="180">文件</th>
							<th width="60">大小</th>
							<th width="180">状态</th>
							<th width="40">操作</th>
						</tr>
					</thead>
					<tbody id="fsUploadProgress"></tbody>
				</table>
				<div class="filesStatus" id="filesStatus">请选择(可多选)需要上传的文件! <span class=light>注: 仅允许上传 <span class=note>JPG</span> 类型的图片文件</span></div>
			</div>
			<div class="buttons">
				<span id="spanButtonPlaceHolder"></span>
				<input id="btnUpload" type="button" onfocus="this.blur();" onclick="swfu.startUpload();" disabled="disabled" class="btnUpload_disabled" />
				<input id="btnCancel" type="button" onfocus="this.blur();" onclick="swfu.cancelQueue();" disabled="disabled" class="btnCancel_disabled" />
			</div>
		</div>'));

		TableFooter();

		PrintSubmit(Iif($pro_id, '保存更新', '添加产品'));
	}

}

?>