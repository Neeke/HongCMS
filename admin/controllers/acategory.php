<?php if(!defined('ROOT')) die('Access denied.');

class c_acategory extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

	}

	//分类下拉菜单函数
	private function GetCategorySelect($currentid, $selectedid =0, $showzerovalue = 1, $selectname = 'p_id'){
		$sReturn = '<select name="' . $selectname . '">';

		if($showzerovalue){
			$sReturn .= '<option value="0">无</option>';
		}

		$categories = $this->db->getAll("SELECT cat_id, p_id, name, name_en, counts  FROM " . TABLE_PREFIX . "acat ORDER BY sort");

		$sReturn .= $this->GetOptions($categories, $currentid, $selectedid);
		$sReturn .= '</select>';

		return $sReturn;
	}

	//分类选项列表函数
	private function GetOptions($categories, $currentid = 0, $selectedid = 0, $parentid = 0, $sublevelmarker = ''){
		if($parentid) $sublevelmarker .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		foreach($categories as $value){
			if($parentid == $value['p_id'] AND $value['cat_id'] != $currentid){
				$sReturn .= '<option '. Iif(!$parentid, 'style="color:#cc4911;font-weight:bold;"') . ' value="' . $value['cat_id'] . '" ' . Iif($selectedid == $value['cat_id'], 'SELECTED', '') . '>' . $sublevelmarker . $value['name'] . '-' . $value['name_en'] . ' (' . $value['counts'] . ')</option>';

				$sReturn .= $this->GetOptions($categories, $currentid, $selectedid, $value['cat_id'], $sublevelmarker);
			}
		}

		return $sReturn;
	}

	//删除分类
	public function delete(){
		$cat_id = ForceIntFrom('cat_id');
		$p_id = ForceIntFrom('p_id');
		$cats = ForceIntFrom('cats');

		if(IsPost('confirmdelete'))	{
			if($p_id){
				$getcounts = $this->db->getOne("SELECT counts FROM " . TABLE_PREFIX . "acat WHERE cat_id = '$cat_id'");
				$this->db->exe("DELETE FROM " . TABLE_PREFIX . "acat WHERE cat_id = '$cat_id'");
				$this->db->exe("UPDATE " . TABLE_PREFIX . "article SET cat_id = '$p_id' WHERE cat_id = '$cat_id' ");
				$this->db->exe("UPDATE " . TABLE_PREFIX . "acat SET counts = (counts +$getcounts[counts]) WHERE cat_id = '$p_id'");
			}else{
				$this->db->exe("DELETE FROM " . TABLE_PREFIX . "acat WHERE cat_id = '$cat_id'");
				$this->db->exe("DELETE FROM " . TABLE_PREFIX . "article WHERE cat_id = '$cat_id'");
			}

			Success('acategory');

		}else{
			$parent_cat = $this->db->getOne("SELECT cat_id  FROM " . TABLE_PREFIX . "acat WHERE p_id = '$cat_id'");
			if($parent_cat) {
				Error('当前分类有下级分类, 请先删除其下级分类!', '删除分类错误');
			}

			SubMenu('文章分类管理', array(array('添加分类', 'acategory/add'),array('删除分类', 'acategory/delete?cat_id='.$cat_id . '&cats=' . $cats, 1),array('全部分类', 'acategory'), array('文章列表', 'articles')));

			$category = $this->db->getOne("SELECT cat_id, p_id, name  FROM " . TABLE_PREFIX . "acat WHERE cat_id = '$cat_id'");


			echo '<form method="post" action="'.BURL('acategory/delete').'">
			<input type="hidden" name="cat_id" value="' . $cat_id . '" />';

			TableHeader('删除分类: <span class=note>' . $category['name'] . '</span>');
			TableRow(array('<BR><b>确定删除分类目录: "<font class=redb>' . $category['name']  . '</font>" 吗?</b><BR><BR>' . 
			Iif($cats > 1, '<span class=note>此分类下所有的文章将自动转入</span>: ' . $this->GetCategorySelect($cat_id, $category['p_id'], 0), '<span class=note>注: 此分类下所有的文章将被删除!</span>') . '<BR><BR>', '<input type="submit" name="confirmdelete" value="确定删除" class="save">
			<input type="submit" value="取 消" class="cancel" onclick="history.back();return false;">'));

			TableFooter();
			echo '</form>';
		}
	}

	//保存分类
	public function save(){
		$cat_id = ForceIntFrom('cat_id');
		$p_id = ForceIntFrom('p_id');
		$sort = ForceIntFrom('sort');
		$is_show = ForceIntFrom('is_show');
		$show_sub = ForceIntFrom('show_sub');

		$name = ForceStringFrom('name');
		$name_en = ForceStringFrom('name_en');
		$keywords = ForceStringFrom('keywords');
		$keywords_en = ForceStringFrom('keywords_en');
		$desc_cn = ForceStringFrom('desc_cn');
		$desc_en = ForceStringFrom('desc_en');

		if(!$name OR !$name_en){
			$errors = "中英文分类名称均不能为空!";
		}

		if(isset($errors)){
			Error($errors, Iif($cat_id, '编辑分类错误', '添加分类错误'));
		}else{
			if($cat_id){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "acat SET 
				p_id= '$p_id',
				sort= '$sort',
				is_show= '$is_show',
				show_sub= '$show_sub',
				name     = '$name',
				name_en     = '$name_en',
				keywords     = '$keywords',
				keywords_en     = '$keywords_en',
				desc_cn     = '$desc_cn',
				desc_en     = '$desc_en'
				WHERE cat_id = '$cat_id' ");
			}else{
				$this->db->exe("INSERT INTO " . TABLE_PREFIX . "acat (p_id, is_show, show_sub, name, name_en, keywords, keywords_en, desc_cn, desc_en) VALUES ('$p_id', '$is_show', '$show_sub', '$name', '$name_en', '$keywords', '$keywords_en', '$desc_cn', '$desc_en') ");

				$cat_id = $this->db->insert_id;
				$this->db->exe("UPDATE " . TABLE_PREFIX . "acat SET sort = '$cat_id' WHERE cat_id = '$cat_id'");
			}

			Success('acategory');
		}
	}

	//批量更新分类
	public function updatecategories(){
		$cat_ids   = $_POST['cat_ids'];
		$sorts   = $_POST['sorts'];
		$names   = $_POST['names'];
		$name_ens   = $_POST['name_ens'];

		$is_shows   = $_POST['is_shows'];
		$show_subs   = $_POST['show_subs'];

		for($i = 0; $i < count($cat_ids); $i++){
			$this->db->exe("UPDATE " . TABLE_PREFIX . "acat SET sort = '". ForceInt($sorts[$i])."',
			is_show = '".ForceInt($is_shows[$i])."',
			show_sub = '".ForceInt($show_subs[$i])."',
			name = '". ForceString($names[$i])."',
			name_en = '". ForceString($name_ens[$i])."'
			WHERE cat_id = '".ForceInt($cat_ids[$i])."'");
		}

		Success('acategory');
	}

	//编辑分类调用add
	public function edit(){
		$this->add();
	}

	//添加分类的表单页面
	public function add(){
		$cat_id = ForceIntFrom('cat_id');
		if($cat_id){
			SubMenu('文章分类管理', array(array('添加分类', 'acategory/add'),array('编辑分类', 'acategory/add?cat_id='.$cat_id, 1),array('全部分类', 'acategory'), array('文章列表', 'articles')));

			$category = $this->db->getOne("SELECT * FROM " . TABLE_PREFIX . "acat WHERE cat_id = '$cat_id'");
		}else{
			SubMenu('文章分类管理', array(array('添加分类', 'acategory/add', 1),array('全部分类','acategory'),array('文章列表', 'articles')));

			$category = array('p_id' => 0, 'is_show' => 1);
		}

		echo '<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/kindeditor.js"></script>
		<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/lang/zh_CN.js"></script>
		<script>
			KindEditor.ready(function(K) {
				var editor88 = K.create(\'textarea[name="desc_cn"]\', {
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
				var editor66 = K.create(\'textarea[name="desc_en"]\', {
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
		</script>
		<form id="editorform88" name="editorform88" method="post" action="'.BURL('acategory/save').'">
		<input type="hidden" name="cat_id" value="' . $cat_id . '" />';

		if($cat_id){
			TableHeader('编辑分类: <span class=note>' . Iif($category['name'], $category['name'], '未命名') . '</span>');
		}else{
			TableHeader('添加分类');
		}

		TableRow(array('<B>分类名称(<span class=blue>中文</span>):</B>', '<input type="text" name="name" value="' . $category['name'] . '"  size="30" /> <font class=red>* 必填项</font>'));

		TableRow(array('<B>分类名称(<span class=red>英文</span>):</B>', '<input type="text" name="name_en" value="' . $category['name_en'] . '"  size="30" /> <font class=red>* 必填项</font>'));

		TableRow(array('<B>父分类:</B>', $this->GetCategorySelect($cat_id, $category['p_id']) . '&nbsp;&nbsp;<span class=light>注: 选择当前分类的上级分类.</span>' ));

		if($cat_id){
			TableRow(array('<B>排序编号:</B>', '<input type="text" name="sort" value="' . $category['sort'] . '"  size="10" /> <span class=light>注: 分类将按此编号排序.</span>'));
		}

		TableRow(array('<B>是否发布?</B>', '<input type="checkbox" name="is_show" value="1" '.Iif($category['is_show'] == 1, 'CHECKED').'> <b>是:</b> <span class=light>当不发布时, 此分类及其下属分类, 以及这些分类下的所有文章均不显示.</span>'));

		TableRow(array('<B>是否显示下级?</B>', '<input type="checkbox" name="show_sub" value="1" '.Iif($category['show_sub'], 'CHECKED').'> <b>是:</b> <span class=light>打开此分类时, 是否同时显示所有下级分类的文章?</span>'));

		TableRow(array('<B>Meta关键字(<span class=blue>中文</span>):</B>', '<input type="text" name="keywords" value="' . $category['keywords'] . '" size="30" /> <span class=light>注: 分类的Meta关键字, <span class=note>便于搜索引擎收录, 请用英文逗号隔开</span>.</span>'));

		TableRow(array('<B>Meta关键字(<span class=red>英文</span>):</B>', '<input type="text" name="keywords_en" value="' . $category['keywords_en'] . '" size="30" /> <span class=light>注: 同上</span>'));

		TableRow(array('<B>分类描述:</B><BR><span class=light>详细描述性文字.</span>', '
			<div class="ok_tab">
				<div class="ok_tabheader">
					<ul id="tabContent-li-ok_tabOn-">
						<li class="ok_tabOn"><a href="javascript:void(0)" title="中文描述" rel="1" hidefocus="true">中文描述</a></li>
						<li><a href="javascript:void(0)" title="英文描述" rel="2" hidefocus="true">英文描述</a></li>
					</ul>
				</div>
				<div id="tabContent_1" class="tabContent">
				 <textarea name="desc_cn" style="width:100%;height:300px;visibility:hidden;" id="desc_cn">'.$category['desc_cn'].'</textarea>
				</div>

				<div id="tabContent_2" class="tabContent" style="display: none;">
				<textarea name="desc_en" style="width:100%;height:300px;visibility:hidden;" id="desc_en">'.$category['desc_en'].'</textarea>
				</div>

				<div class="ok_tabbottom">
					<span class="tabbottomL"></span>
					<span class="tabbottomR"></span>
				</div>
			</div>
			<script type="text/javascript">new tab(\'tabContent-li-ok_tabOn-\', \'-\');</script>
		'));

		TableFooter();

		PrintSubmit(Iif($cat_id, '保存更新', '创建分类'));
	}

	public function index(){
		SubMenu('文章分类管理', array(array('添加分类', 'acategory/add'), array('文章列表', 'articles')));

		$getcategories = $this->db->query("SELECT cat_id, p_id, sort, is_show, show_sub, name, name_en, counts FROM " . TABLE_PREFIX . "acat ORDER BY sort");
		$this->cats = $this->db->result_nums;

		echo '<form method="post" action="'.BURL('acategory/updatecategories').'">';

		TableHeader('文章分类列表('.$this->cats.'个)');
		TableRow(array('排序编号 与 分类名称', '文章数', '状态', '显示下级分类', '编辑', '删除'), 'tr0');

		if($this->cats < 1){
			TableRow('<center><BR><font class=redb>暂无任何文章分类!</font><BR><BR></center>');
		}else{

			$this->categories = array();
			$this->parentids = array();

			while($category = $this->db->fetch($getcategories)){
				$this->categories[$category['cat_id']] = $category;
				$this->parentids[$category['cat_id']] = $category['p_id'];
			}

			$this->ShowCategories();
		}

		TableFooter();

		PrintSubmit('保存更新');
	}


	private function ShowCategories ($parentid = 0, $sublevelmarker = '') {
		if($parentid) $sublevelmarker .= '<img src="' . SYSDIR . 'public/admin/images/sub.gif" align="absmiddle">';

		$allcategories = $this->parentids;

		foreach($allcategories as $key => $value){
			if($parentid == $value){
				TableRow(array($sublevelmarker .'<input type="hidden" name="cat_ids[]" value="' . $key . '" /><input type="text" name="sorts[]" value="' . $this->categories[$key]['sort'] . '" size="4">&nbsp;&nbsp;<input type="text" name="names[]" value="' . $this->categories[$key]['name'] . '" size="22" />&nbsp;&nbsp;<input type="text" name="name_ens[]" value="' . $this->categories[$key]['name_en'] . '" size="22" />',
				$this->categories[$key]['counts'],
				'<select name="is_shows[]"><option value="1">发布</option><option style="color:red;" value="0" ' . Iif(!$this->categories[$key]['is_show'], 'SELECTED', '') . '>隐藏</option></select>',
				'<select name="show_subs[]"><option value="1">是</option><option style="color:red;" value="0" ' . Iif(!$this->categories[$key]['show_sub'], 'SELECTED', '') . '>否</option></select>',
				'<a href="' . BURL('acategory/edit?cat_id=' . $key) . '"><img src="' . SYSDIR . 'public/admin/images/edit.png" /></a>',
				'<a href="' . BURL('acategory/delete?cat_id=' . $key . '&cats=' . $this->cats) . '"><img src="' . SYSDIR . 'public/admin/images/trash.png"></a>'));

				$this->ShowCategories($key, $sublevelmarker);
			}
		}
	}
} 

?>