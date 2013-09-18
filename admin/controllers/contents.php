<?php if(!defined('ROOT')) die('Access denied.');

class c_contents extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

	}

	public function index(){
		$NumPerPage = 20;   //每页显示的常态内容列表的数量
		$page = ForceIntFrom('p', 1);   //页码
		$search = ForceStringFrom('s');   //搜索的内容
		$type = ForceStringFrom('type');   //搜索的内容
		$time = ForceStringFrom('t');
		if(IsGet('s')){
			$search = urldecode($search);
		}

		$start = $NumPerPage * ($page-1);  //分页的每页起始位置

		if($search OR $time){
			SubMenu('常态内容列表', array(array('添加常态内容', 'contents/add'), array('全部常态内容', 'contents')));
		}else{
			SubMenu('常态内容列表', array(array('添加常态内容', 'contents/add')));
		}

		ShowTips('<ul>
		<li><b>说 明</b>: 调用常态内容都使用GetContent($id)函数, $id参数指常态内容的调用ID. 调用ID可以编辑和修改;</li>
		<li><b>方法1</b>: 参阅about.php和about.tpl两个文件调用常态内容;</li>
		<li><b>方法2</u></b>: 在前台模板文件中直接调用常态内容的模板代码如下:
		<BR>首先分配变量(<span class=note>id指常态内容的调用ID</span>):&nbsp;&nbsp;&nbsp;&nbsp;{$mycontent = GetContent(id)} 
		<BR>显示常态内容的名称:&nbsp;&nbsp;&nbsp;&nbsp;{$mycontent.title}
		<BR>显示常态内容的正文:&nbsp;&nbsp;&nbsp;&nbsp;{$mycontent.content}
		<BR>显示常态内容的日期:&nbsp;&nbsp;&nbsp;&nbsp;{$mycontent.created}
		</li></ul>', '如何调用常态内容');

		echo '<script type="text/javascript" src="'.SYSDIR.'public/js/DatePicker/WdatePicker.js"></script>
		<form method="post" action="'.BURL('contents').'" name="searchform">';

		TableHeader('搜索常态内容');
		TableRow('<center><label>搜索:</label>&nbsp;<input type="text" name="s" size="22" value="' . $search . '">&nbsp;&nbsp;&nbsp;<label>发布时间:</label>&nbsp;<select name="type"><option value="gr" ' . Iif($type == 'gr', 'SELECTED') . '>大于(>)</option><option value="eq" ' . Iif($type == 'eq', 'SELECTED') . '>等于(=)</option><option value="le" ' . Iif($type == 'le', 'SELECTED') . '>小于(<)</option></select> <input type="text" name="t" onClick="WdatePicker()" value="' . $time . '">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="searchcontents" value="搜索常态内容" class="cancel"></center>');
		TableFooter();
		echo '</form>';

		$Where = $this->GetSearchSql($search, $type, $time);
		$title = Iif($Where, '搜索到的常态内容列表', '全部常态内容列表');

		$getcontents = $this->db->query("SELECT c_id, title, title_en, created, r_id FROM " . TABLE_PREFIX . "content " . $Where . " ORDER BY c_id DESC LIMIT $start, $NumPerPage");
		$maxrows = $this->db->getOne("SELECT COUNT(c_id) AS value FROM " . TABLE_PREFIX . "content " . $Where);

		echo '<form method="post" action="'.BURL('contents/updatecontents').'" name="contentsform">';
		TableHeader($title.'('.$maxrows['value'].'个)');
		TableRow(array('调用ID', '中文名称', '英文名称', '日期', '<input type="checkbox" id="checkAll" for="deletec_ids[]"> <label for="checkAll">删除</label>'), 'tr0');

		if($maxrows['value'] < 1){
			TableRow('<center><BR><font class=redb>未搜索到任何常态内容!</font><BR><BR></center>');
		}else{
			while($contents = $this->db->fetch($getcontents)){

				TableRow(array('<input type="hidden" name="c_ids[]" value="'.$contents['c_id'].'" />'.$contents['r_id'],
					'<a href="'.BURL('contents/edit?c_id='.$contents['c_id']).'" title="英文: '.$contents['title_en'].'">' . ShortTitle($contents['title'], 28) . '</a>',
					'<a href="'.BURL('contents/edit?c_id='.$contents['c_id']).'" title="中文: '.$contents['title'].'">' . ShortTitle($contents['title_en'], 28) . '</a>',
					DisplayDate($contents['created']),
					'<input type="checkbox" name="deletec_ids[]" value="' . $contents['c_id'] . '">'));
			}

			$totalpages = ceil($maxrows['value'] / $NumPerPage);
			if($totalpages > 1){
				TableRow(GetPageList(BURL('contents'), $totalpages, $page, 10, 's', urlencode($search), 't', $time, 'type', $type));
			}
		}

		TableFooter();

		echo '<div class="submit"><input type="submit" name="deletecontents" onclick="'.Confirm('确定删除所选常态内容吗?<br><br><span class=red>注: 所选常态内容将被永久删除!</span>', 'form').'" value="删除常态内容" class="save"></div></form>';
	}

	public function updatecontents(){
		$c_ids = $_POST['deletec_ids'];
		for($i=0; $i<count($c_ids); $i++){
			$c_id = ForceInt($c_ids[$i]);
			if($c_id != 1 AND $c_id != 2) $this->db->exe("DELETE FROM " . TABLE_PREFIX . "content WHERE c_id='$c_id'");
		}

		Success('contents');
	}

	//$search 查询的关键字 $type时间条件 大于小于什么的  $time时间
	public function GetSearchSql($search, $type, $time){
		$Where = "";

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

	//编辑调用add
	public function edit(){
		$this->add();
	}

	public function add(){
		$c_id = ForceIntFrom('c_id');

		if($c_id){  //编辑时的
			SubMenu('常态内容管理', array(array('添加常态内容', 'contents/add'),array('编辑常态内容', 'contents/edit?c_id='.$c_id, 1),array('常态内容列表', 'contents')));
			$contents = $this->db->getOne("SELECT * FROM " . TABLE_PREFIX . "content WHERE c_id = '$c_id'");
		}else{
			SubMenu('常态内容管理', array(array('添加常态内容', 'contents/add', 1),array('常态内容列表', 'contents')));

			$contents = array();
		}

		echo '<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/kindeditor.js"></script>
		<script charset="utf-8" src="'. SYSDIR .'public/js/kindeditor/lang/zh_CN.js"></script>
		<script>
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
		</script>
		<form id="editorform88" name="editorform88" method="post" action="'.BURL('contents/save').'">
		<input type="hidden" name="c_id" value="' . $c_id . '" />';

		if($c_id){
			TableHeader('编辑常态内容: <span class=note>' . Iif($contents['title'], $contents['title'], '未命名') . '</span>');

			TableRow(array('<B>是否删除?</B>', '<input type="checkbox" name="deletethiscontents" value="1"' . Iif($c_id == 1 OR $c_id==2, ' disabled') .'> <b>是:</b> <font class=redb>慎选!</font> <span class=light>如果选择删除, 此常态内容相关的所有信息将被删除.</span>'));

			TableRow(array('<B>调用ID:</B>', '<input type="text" name="r_id" value="' . $contents['r_id'] . '" size="10" /> <font class=red>* 必填项</font>'));
		}else{
			TableHeader('添加常态内容');
		}

		TableRow(array('<B>常态内容名称(<span class=blue>中文</span>):</B>', '<input type="text" name="title" value="' . $contents['title'] . '"  size="50" /> <font class=red>* 必填项</font>'));
		TableRow(array('<B>常态内容名称(<span class=red>英文</span>):</B>', '<input type="text" name="title_en" value="' . $contents['title_en'] . '"  size="50" /> <font class=red>* 必填项</font>'));

		TableRow(array('<B>Meta关键字(<span class=blue>中文</span>):</B>', '<input type="text" name="keywords" value="' . $contents['keywords'] . '" size="50" /> <span class=light>注: 常态内容的Meta关键字, <span class=note>便于搜索引擎收录, 请用英文逗号隔开</span>.</span>'));
		TableRow(array('<B>Meta关键字(<span class=red>英文</span>):</B>', '<input type="text" name="keywords_en" value="' . $contents['keywords_en'] . '" size="50" /> <span class=light>注: 同上</span>'));

		TableRow(array('<B>常态内容正文:</B><BR><span class=light>常态内容的详细内容.</span>', '
			<div class="ok_tab">
				<div class="ok_tabheader">
					<ul id="tabContent-li-ok_tabOn-">
						<li class="ok_tabOn"><a href="javascript:void(0)" title="中文内容" rel="1" hidefocus="true">中文内容</a></li>
						<li><a href="javascript:void(0)" title="英文内容" rel="2" hidefocus="true">英文内容</a></li>
					</ul>
				</div>
				<div id="tabContent_1" class="tabContent">
				 <textarea name="content" style="width:100%;height:400px;visibility:hidden;" id="content">'.$contents['content'].'</textarea>
				</div>

				<div id="tabContent_2" class="tabContent" style="display: none;">
				<textarea name="content_en" style="width:100%;height:400px;visibility:hidden;" id="content_en">'.$contents['content_en'].'</textarea>
				</div>

				<div class="ok_tabbottom">
					<span class="tabbottomL"></span>
					<span class="tabbottomR"></span>
				</div>
			</div>
			<script type="text/javascript">new tab(\'tabContent-li-ok_tabOn-\', \'-\');</script>
		'));

		TableFooter();

		PrintSubmit(Iif($c_id, '保存更新', '添加常态内容'));
	}

	public function save(){
		$c_id = ForceIntFrom('c_id');
		$r_id = ForceIntFrom('r_id'); //常态内容调用ID

		$title = ForceStringFrom('title');
		$title_en = ForceStringFrom('title_en');
		$keywords = ForceStringFrom('keywords');
		$keywords_en = ForceStringFrom('keywords_en');
		$content = ForceStringFrom('content');
		$content_en = ForceStringFrom('content_en');
		
		$deletethiscontents     = ForceIntFrom('deletethiscontents');

		if($deletethiscontents AND $c_id){//删除常态内容
			if($c_id != 1 OR $c_id != 2) Error('抱歉, 系统默认的常态内容无法删除!', '删除常态内容错误'); //默认的两个常态内容不能删除

			$this->db->exe("DELETE FROM " . TABLE_PREFIX . "content where c_id='$c_id'");

			Success('contents');
		}

		if(!$title){
			$errors[] = '常态内容名称不能为空！';
		}

		if(!$title_en){
			$errors[] = '常态内容英文名称不能为空！';
		}

		if($c_id){//检查调用ID是否重复
			if($this->db->getOne("SELECT c_id FROM " . TABLE_PREFIX . "content WHERE r_id='$r_id' AND c_id != '$c_id'")){
				$errors[] = '调用ID不能重复！';
			}
		}

		if(isset($errors)) Error($errors, Iif($c_id, '编辑常态内容错误', '添加常态内容错误'));

		if($c_id){
			$this->db->exe("UPDATE " . TABLE_PREFIX . "content SET 
			title = '$title',
			title_en = '$title_en',
			keywords = '$keywords',
			keywords_en = '$keywords_en',
			content = '$content',
			content_en = '$content_en',
			r_id = '$r_id'
			WHERE c_id = ".$c_id);

			Success('contents/edit?c_id=' . $c_id);
		}else{
			$time = time();

			$this->db->exe("INSERT INTO " . TABLE_PREFIX . "content (title, title_en, keywords, keywords_en, content, content_en, created) VALUES ('$title', '$title_en', '$keywords', '$keywords_en', '$content', '$content_en', '$time') ");

			$lastid = $this->db->insert_id;
			$this->db->exe("UPDATE " . TABLE_PREFIX . "content SET r_id = '$lastid' WHERE c_id = '$lastid'");

			Success('contents/edit?c_id=' . $lastid);
		}
	}
}

?>