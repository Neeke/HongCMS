<?php if(!defined('ROOT')) die('Access denied.');

class c_news extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

	}

	public function index(){
		$NumPerPage = 20;   //每页显示的新闻列表的数量
		$page = ForceIntFrom('p', 1);   //页码
		$search = ForceStringFrom('s');   //搜索的内容
		$type = ForceStringFrom('type');   //搜索的内容
		$time = ForceStringFrom('t');
		if(IsGet('s')){
			$search = urldecode($search);
		}

		$start = $NumPerPage * ($page-1);  //分页的每页起始位置

		if($search OR $time){
			SubMenu('新闻列表', array(array('添加新闻', 'news/add'), array('全部新闻', 'news')));
		}else{
			SubMenu('新闻列表', array(array('添加新闻', 'news/add')));
		}

		echo '<script type="text/javascript" src="'.SYSDIR.'public/js/DatePicker/WdatePicker.js"></script>
		<form method="post" action="'.BURL('news').'" name="searchform">';

		TableHeader('搜索新闻');
		TableRow('<center><label>搜索:</label>&nbsp;<input type="text" name="s" size="22" value="' . $search . '">&nbsp;&nbsp;&nbsp;<label>发布时间:</label>&nbsp;<select name="type"><option value="gr" ' . Iif($type == 'gr', 'SELECTED') . '>大于(>)</option><option value="eq" ' . Iif($type == 'eq', 'SELECTED') . '>等于(=)</option><option value="le" ' . Iif($type == 'le', 'SELECTED') . '>小于(<)</option></select> <input type="text" name="t" onClick="WdatePicker()" value="' . $time . '">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="searchnews" value="搜索新闻" class="cancel"></center>');
		TableFooter();
		echo '</form>';

		$Where = $this->GetSearchSql($search, $type, $time);
		$title = Iif($Where, '搜索到的新闻列表', '全部新闻列表');

		$getnews = $this->db->query("SELECT n_id, sort, is_show, title, title_en, clicks, created FROM " . TABLE_PREFIX . "news " . $Where . " ORDER BY is_show ASC, sort DESC LIMIT $start, $NumPerPage");
		$maxrows = $this->db->getOne("SELECT COUNT(n_id) AS value FROM " . TABLE_PREFIX . "news " . $Where);

		echo '<form method="post" action="'.BURL('news/updatenews').'" name="newsform">';
		TableHeader($title.'('.$maxrows['value'].'个)');
		TableRow(array('排序', '新闻标题(编辑)', '状态', '点击数', '日期', '浏览', '<input type="checkbox" id="checkAll" for="deleten_ids[]"> <label for="checkAll">删除</label>'), 'tr0');

		if($maxrows['value'] < 1){
			TableRow('<center><BR><font class=redb>未搜索到任何新闻!</font><BR><BR></center>');
		}else{
			while($news = $this->db->fetch($getnews)){

				$title = Iif($news['is_show'],  ShortTitle($news['title'], 28), '<font class=red><s>' . ShortTitle($news['title'], 28) . '</s></font>');

				TableRow(array('<input type="hidden" name="n_ids[]" value="'.$news['n_id'].'" /><input type="text" name="sorts[]" value="' . $news['sort'] . '" size="4" />',
					'<a href="'.BURL('news/edit?n_id='.$news['n_id']).'" title="英文: '.$news['title_en'].'">' . $title . '</a>',
					'<select name="is_shows[]"><option value="1">发布</option><option style="color:red;" value="0" ' . Iif(!$news['is_show'], 'SELECTED', '') . '>隐藏</option></select>',
					$news['clicks'],
					DisplayDate($news['created']),
					Iif($news['is_show'], '<a href="'.URL('news?id=' . $news['n_id']).'" target="_blank"><img src="' . SYSDIR . 'public/admin/images/view.gif"></a>', '<img src="' . SYSDIR . 'public/admin/images/disview.gif">'),
					'<input type="checkbox" name="deleten_ids[]" value="' . $news['n_id'] . '"'));
			}

			$totalpages = ceil($maxrows['value'] / $NumPerPage);
			if($totalpages > 1){
				TableRow(GetPageList(BURL('news'), $totalpages, $page, 10, 's', urlencode($search), 't', $time, 'type', $type));
			}
		}

		TableFooter();

		echo '<div class="submit"><input type="submit" name="updatenews" value="保存更新" class="save"><input type="submit" name="deletenews" onclick="'.Confirm('确定删除所选新闻吗?<br><br><span class=red>注: 所选新闻将被永久删除!</span>', 'form').'" value="删除新闻" class="cancel"></div></form>';
	}

	public function updatenews(){
		if(IsPost('updatenews')){
			$n_ids = $_POST['n_ids'];
			$sorts   = $_POST['sorts'];
			$is_shows   = $_POST['is_shows'];
			for($i = 0; $i < count($n_ids); $i++){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "news SET sort = '". ForceInt($sorts[$i])."',
				is_show = '". ForceInt($is_shows[$i])."'
				WHERE n_id = '". ForceInt($n_ids[$i])."'");
			}
		}else{
			$n_ids = $_POST['deleten_ids'];
			for($i=0; $i<count($n_ids); $i++){
				$this->db->exe("DELETE FROM " . TABLE_PREFIX . "news WHERE n_id=".ForceInt($n_ids[$i]));
			}
		}
		Success('news');
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
		$n_id = ForceIntFrom('n_id');

		if($n_id){  //编辑时的
			SubMenu('新闻管理', array(array('添加新闻', 'news/add'),array('编辑新闻', 'news/edit?n_id='.$n_id, 1),array('新闻列表', 'news')));
			$news = $this->db->getOne("SELECT * FROM " . TABLE_PREFIX . "news WHERE n_id = '$n_id'");
		}else{
			SubMenu('新闻管理', array(array('添加新闻', 'news/add', 1),array('新闻列表', 'news')));

			$news = array('is_show' => 1);
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
		<form id="editorform88" name="editorform88" method="post" action="'.BURL('news/save').'">
		<input type="hidden" name="n_id" value="' . $n_id . '" />';

		if($n_id){
			TableHeader('编辑新闻: <span class=note>' . Iif($news['title'], $news['title'], '未命名') . '</span>');

			TableRow(array('<B>是否删除?</B>', '<input type="checkbox" name="deletethisnews" value="1"> <b>是:</b> <font class=redb>慎选!</font> <span class=light>如果选择删除, 此新闻相关的所有信息将被删除.</span>'));
		}else{
			TableHeader('添加新闻');
		}

		TableRow(array('<B>新闻标题(<span class=blue>中文</span>):</B>', '<input type="text" name="title" value="' . $news['title'] . '"  size="50" /> <font class=red>* 必填项</font>'));
		TableRow(array('<B>新闻标题(<span class=red>英文</span>):</B>', '<input type="text" name="title_en" value="' . $news['title_en'] . '"  size="50" /> <font class=red>* 必填项</font>'));

		if($n_id){
			TableRow(array('<B>排序编号:</B>', '<input type="text" name="sort" value="' . $news['sort'] . '"  size="10" /> <span class=light>注: 新闻将按此编号排序.</span>'));
		}

		TableRow(array('<B>是否发布?</B>', '<input type="checkbox" name="is_show" value="1" '.Iif($news['is_show'] == 1, 'CHECKED').'> <b>是:</b> <span class=light>当不发布时, 此新闻不在前台显示.</span>'));

		TableRow(array('<B>Meta关键字(<span class=blue>中文</span>):</B>', '<input type="text" name="keywords" value="' . $news['keywords'] . '" size="50" /> <span class=light>注: 新闻的Meta关键字, <span class=note>便于搜索引擎收录, 请用英文逗号隔开</span>.</span>'));
		TableRow(array('<B>Meta关键字(<span class=red>英文</span>):</B>', '<input type="text" name="keywords_en" value="' . $news['keywords_en'] . '" size="50" /> <span class=light>注: 同上</span>'));

		TableRow(array('<B>新闻正文:</B><BR><span class=light>新闻的详细内容.</span>', '
			<div class="ok_tab">
				<div class="ok_tabheader">
					<ul id="tabContent-li-ok_tabOn-">
						<li class="ok_tabOn"><a href="javascript:void(0)" title="中文内容" rel="1" hidefocus="true">中文内容</a></li>
						<li><a href="javascript:void(0)" title="英文内容" rel="2" hidefocus="true">英文内容</a></li>
					</ul>
				</div>
				<div id="tabContent_1" class="tabContent">
				 <textarea name="content" style="width:100%;height:400px;visibility:hidden;" id="content">'.$news['content'].'</textarea>
				</div>

				<div id="tabContent_2" class="tabContent" style="display: none;">
				<textarea name="content_en" style="width:100%;height:400px;visibility:hidden;" id="content_en">'.$news['content_en'].'</textarea>
				</div>

				<div class="ok_tabbottom">
					<span class="tabbottomL"></span>
					<span class="tabbottomR"></span>
				</div>
			</div>
			<script type="text/javascript">new tab(\'tabContent-li-ok_tabOn-\', \'-\');</script>
		'));

		TableRow(array('<B>新闻链接(<span class=blue>中文</span>):</B>', '<input type="text" name="linkurl" value="' . $news['linkurl'] . '"  size="50" /> <span class=light>注: 如果填写链接地址, 点击新闻标题时将打开此链接而不是显示以上详细内容.</span>'));
		TableRow(array('<B>新闻链接(<span class=red>英文</span>):</B>', '<input type="text" name="linkurl_en" value="' . $news['linkurl_en'] . '"  size="50" /> <span class=light>注: 同上.</span>'));

		TableFooter();

		PrintSubmit(Iif($n_id, '保存更新', '添加新闻'));
	}

	public function save(){
		$n_id = ForceIntFrom('n_id');

		$is_show = ForceIntFrom('is_show');
		$sort = ForceIntFrom('sort');

		$title = ForceStringFrom('title');
		$title_en = ForceStringFrom('title_en');
		$linkurl = ForceStringFrom('linkurl');
		$linkurl_en = ForceStringFrom('linkurl_en');
		$keywords = ForceStringFrom('keywords');
		$keywords_en = ForceStringFrom('keywords_en');
		$content = ForceStringFrom('content');
		$content_en = ForceStringFrom('content_en');
		
		$deletethisnews     = ForceIntFrom('deletethisnews');

		if($deletethisnews AND $n_id){//删除新闻
			$this->db->exe("DELETE FROM " . TABLE_PREFIX ."news where n_id='$n_id'");

			Success('news');
		}

		$time = time();

		if(!$title){
			$errors[] = '新闻标题不能为空！';
		}

		if(!$title_en){
			$errors[] = '新闻英文标题不能为空！';
		}

		if(isset($errors)) Error($errors, Iif($n_id, '编辑新闻错误', '添加新闻错误'));

		if($n_id){
			$this->db->exe("UPDATE " . TABLE_PREFIX . "news SET 
			sort= '$sort',
			is_show= '$is_show',
			title = '$title',
			title_en = '$title_en',
			linkurl = '$linkurl',
			linkurl_en = '$linkurl_en',
			keywords = '$keywords',
			keywords_en = '$keywords_en',
			content = '$content',
			content_en = '$content_en'
			WHERE n_id = ".$n_id);

			Success('news/edit?n_id=' . $n_id);
		}else{
			$this->db->exe("INSERT INTO " . TABLE_PREFIX . "news (is_show, title, title_en, linkurl, linkurl_en, keywords, keywords_en, content, content_en, clicks, created) VALUES ('$is_show', '$title', '$title_en', '$linkurl', '$linkurl_en', '$keywords', '$keywords_en', '$content', '$content_en', '0', '$time') ");

			$lastid = $this->db->insert_id;
			$this->db->exe("UPDATE " . TABLE_PREFIX . "news SET sort = '$lastid' WHERE n_id = '$lastid'");

			Success('news/edit?n_id=' . $lastid);
		}
	}

}

?>