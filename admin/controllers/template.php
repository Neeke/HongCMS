<?php if(!defined('ROOT')) die('Access denied.');

class c_template extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

		$this->temp_path = ROOT.'public/templates/';

		$this->current_dir = ForceStringFrom('dir');

		if(!$this->ajax) SubMenu('模板管理'); //根据父对象SAdmin的ajax成员变量, 判断是否为ajax动作
	}

	//ajax动作集合, 能过action判断具体任务
    public function ajax(){

		$action = ForceStringFrom('action');

		if($action == 'delete'){
			$file = ForceStringFrom('file');
			$filepath = $this->temp_path . $this->current_dir . $file;

			if(@unlink($filepath)){
				//无动作
			}else{
				$this->ajax['s'] = 0; //ajax操作失败
				$this->ajax['i'] = '无法删除模板文件! 文件夹不可写或文件不存在.';
			}

		}elseif($action == 'refreshcache'){

			$this->tpl_remove_cache(T_CACHEPATH); //更新缓存

		}elseif($action == 'settemplate'){

			$this->settemplate(); //设置模板编辑模式及当前模板

		}

		die($this->json->encode($this->ajax));
	}

	//保存模板文件
    public function save(){
		$file = ForceStringFrom('file');
		$filepath = $this->temp_path . $this->current_dir . $file;

		if (is_writable($filepath)) {
			$filecontent = trim($_POST['filecontent']);
			if (get_magic_quotes_gpc()) {
				$filecontent = stripslashes($filecontent);
			}

			$fd = fopen($filepath, 'wb');
			fputs($fd,$filecontent);

			Success('template'. Iif($this->current_dir, '?dir=' . $this->current_dir));
		}else{
			$errors = '模板文件('.$file.')不可写! 请将其属性设置为: 777';
			Error($errors, '编辑模板错误');
		}
	}

	//编辑模板文件
    public function edit(){
		$file = ForceStringFrom('file');
		$filepath = $this->temp_path . $this->current_dir . $file;

		if(!is_file($filepath)) Error('正在打开的文件不存在!', '打开文件错误');
		
		$filecontent = htmlspecialchars(implode("",file($filepath)));

		echo '<form method="post" name="editform" action="'.BURL('template/save').'">
		<input type="hidden" name="file" value="' . $file . '" />
		<input type="hidden" name="dir" value="' . $this->current_dir . '" />';

		TableHeader('编辑语言文件');

		TableRow('<b>当前文件:</b> ' . BASEURL . 'public/templates/' . $this->current_dir . $file);
		TableRow('<textarea rows="32" style="width:90%;" name="filecontent" >' . $filecontent . '</textarea>');

		TableFooter();

		PrintSubmit('保存更新', '取消', 1, "确定保存模板文件 $file 吗?");
	}


	//上传设置模板
    public function upload(){
		$file     = $_FILES['file'];
		$folderpath = $this->temp_path . $this->current_dir;

		$valid_image_extensions = array('gif', 'jpg', 'peg', 'bmp', 'tml', 'htm', 'php', 'css', 'txt', 'asp', 'swf', 'flv', 'jsp', 'js', 'xml', 'tpl', 'png', 'mp3');

		if($file['size'] == 0)	{
			$errors = '请选择要上传的文件!';
		}else if(!in_array(getFileExt($file['name']), $valid_image_extensions)){
			$errors = '不允许的文件类型!';
		} elseif (!is_uploaded_file($file['tmp_name']) || !($file['tmp_name'] != 'none' && $file['tmp_name'] && $file['name'])){
			$errors ='上传文件无效!';
		}elseif (file_exists($folderpath . $file['name'])){
			$errors = '目标文件夹内存在同名的文件, 请先删除原文件再上传!';
		}else{
			@chmod($folderpath, 0777);

			if((function_exists('move_uploaded_file') AND @move_uploaded_file($file['tmp_name'], $folderpath . $file['name'])) OR @copy($file['tmp_name'], $folderpath . $file['name'])){
				@chmod($folderpath . $file['name'], 0777);
				@unlink($file['tmp_name']);
			}else{
				$errors = '文件夹 "' . BASEURL . 'public/templates/' . $this->current_dir . '" 不可写!';
			}
		}

		if(isset($errors)){
			Error($errors, '上传模板文件错误');
		}else{
			Success('template?'. Iif($this->current_dir, 'dir=' . $this->current_dir . '&') . 'uploaded=' . $file['name']);
		}
	}

	//更新缓存函数
	private function tpl_remove_cache($dirPath) {
		if($handle = @opendir($dirPath)){
		   while(false !== ($item = @readdir($handle))){
			   if($item != "." && $item != ".."){
				   if(@is_dir("$dirPath/$item")){
					   tpl_remove_cache("$dirPath/$item");
				   }else{
					   @unlink("$dirPath/$item");
				   }
			   }
		   }

		   @closedir($handle);
		   @rmdir($dirPath);
		}
	}

	//选择并设置模板
    private function settemplate(){
		$siteDefaultTemplate = ForceStringFrom('siteDefaultTemplate');
		$siteTemplateCheck = ForceStringFrom('siteTemplateCheck');

		$filename = ROOT . "config/settings.php";
		$fp = @fopen($filename, 'rb');
		$contents = @fread($fp, filesize($filename));
		@fclose($fp);
		$contents =  trim($contents);
		$oldcontents = $contents;

		if($this->config['siteDefaultTemplate'] != $siteDefaultTemplate){
			$contents = preg_replace("/[$]_CFG\['siteDefaultTemplate'\]\s*\=\s*[\"'].*?[\"'];/is", "\$_CFG['siteDefaultTemplate'] = \"$siteDefaultTemplate\";", $contents);
		}

		if($this->config['siteTemplateCheck'] != $siteTemplateCheck){
			$contents = preg_replace("/[$]_CFG\['siteTemplateCheck'\]\s*\=\s*[\"'].*?[\"'];/is", "\$_CFG['siteTemplateCheck'] = \"$siteTemplateCheck\";", $contents);
		}

		if($contents != $oldcontents){
			$fp = @fopen($filename, 'w');
			@fwrite($fp, $contents);
			@fclose($fp);
		}
	}

    public function index(){
		$folderpath = $this->temp_path . $this->current_dir;

		$folderurl = '<b>当前文件夹:</b> ' . BASEURL . 'public/templates/'. $this->current_dir;

		$uploaded = ForceStringFrom('uploaded');

		$Templates = GetTemps();
		foreach($Templates as $val){
			$templateoptions .='<option value="'.$val.'"' . Iif($this->config['siteDefaultTemplate'] == $val, ' SELECTED') . '>'.$val.'</option>';
		}

		$this->print_javascript();

		TableHeader('更新缓存 | 切换模板');
		TableRow('当网站运行在模板编辑模式 <span class=note>关闭</span> 状态时, 修改或编辑前台模板文件(.tpl)后, 需要更新模板缓存才能显现效果:&nbsp;&nbsp;&nbsp<input type="submit" value="更新缓存" class="cancel" id="refreshcache">');

		TableRow('<form><b>模板编辑模式:</b> <input type="radio" id="m1" name="siteTemplateCheck" value="1" '.Iif($this->config['siteTemplateCheck'], ' checked="checked"').'><label for="m1">开启</label>&nbsp;&nbsp;&nbsp;<input type="radio" id="m2" name="siteTemplateCheck" value="0" '.Iif(!$this->config['siteTemplateCheck'], ' checked="checked"').'><label for="m2">关闭(<span class=note>推荐</span>, 有利于提高网站速度)</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>前台模板:</b> <select name="siteDefaultTemplate">'.$templateoptions.'</select>&nbsp;&nbsp;&nbsp;<input type="submit" value="保存设置" class="cancel" id="settemplate"></form>');
		TableFooter();


		TableHeader('上传模板文件');
		TableRow($folderurl);
		TableRow(Iif($uploaded, '<font class=blue>' . $uploaded . ' 文件上传成功!</font><br />').'
		<form enctype="multipart/form-data" method="post" action="'.BURL('template/upload').'" name="upload_form">
		<input type="hidden" name="dir" value="' . $this->current_dir . '" />
		<input name="file" type="file" size="38" class="file">&nbsp;&nbsp;&nbsp;<input type="submit" value="上传文件" class="cancel">
		</form>注: 上传文件到当前文件夹(<span class=note>仅允许上传jpg, png, gif, bmp, htm, html, php, css, txt, asp, jsp, js, tpl, xml, swf, flv, mp3文件</span>).');
		TableFooter();

		BR();

		TableHeader('模板文件列表');
		TableRow($folderurl);
	  
		$files   = array();
		$images  = array();
		$folders = array();
		$handle  = opendir($folderpath);

		while(false !== ($file = readdir($handle))){
			$fileExt = getFileExt($file);
			$extensions = array('js', 'jpeg', 'gif', 'jpg', 'bmp', 'png', 'html', 'css', 'htm', 'php', 'txt', 'asp', 'jsp', 'tpl', 'xml', 'swf', 'flv', 'mp3');

			if(in_array($fileExt, $extensions))	{
			  $images[]  = $file;
			}else if($file != '.' AND is_dir($folderpath . $file)){
			  $folders[] = $file;
			}
		}

		closedir($handle);

		// now sort both images and folders
		@sort($folders);
		@sort($images);

		$files = @array_merge($folders, $images);

		//var_dump($files);exit;

		$columncount = 0;

		echo '<td class="td last"><table width="100%" border="0" cellpadding="5" cellspacing="0">';

		for($i = 0; $i < count($files); $i++){
			$columncount++;

			if($columncount == 1){
			  echo '<tr>';
			}

			echo '<td width="33%">';

			$this->DisplayFileDetails($files[$i]);

			echo '</td>';

			if($columncount == 3){
			  echo '</tr>';
			  $columncount = 0;
			}
		}

		if($columncount != 0 && $columncount != 3){
			while($columncount < 3){
				$columncount++;
				echo '<td width="33%">&nbsp;</td>';
			}
			echo '</tr>';
		}

		echo '</table>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				$("#main a.ajax").click(function(e){
					var _me=$(this);
					$.dialog({title:"操作确认",lock:true,content:"确定删除模板中的文件: " + _me.attr("file") + " 吗?",okValue:"  确定  ",
					ok:function(){
						ajax("' . BURL('template/ajax?action=delete') . '", {dir: "' . $this->current_dir . '", file: _me.attr("file")}, function(data){
							_me.parent().parent().hide();
						});
					},
					cancelValue:"取消",cancel:true});
					e.preventDefault();
				});

				$("#refreshcache").click(function(e){
					ajax("' . BURL('template/ajax?action=refreshcache') . '", {}, function(data){
						$.dialog({title:"操作成功",lock:true,content:"<span class=blue>Ajax操作, 当前模板缓存已更新.</span>",okValue:"  确定  ",ok:true,time:1000});
					});

					e.preventDefault();
				});

				$("#settemplate").click(function(e){
					var data = $(this).parent().serialize();
					ajax("' . BURL('template/ajax?action=settemplate') . '", data, function(data){
						$.dialog({title:"操作成功",lock:true,content:"<span class=blue>Ajax操作, 模板编辑模式及当前模板设置成功.</span>",okValue:"  确定  ",ok:true,time:1000});
					});

					e.preventDefault();
				});

			});
		</script>
		</td>';

		TableFooter();
	} 


	private function DisplayFileDetails($file){
		$filepath = $this->temp_path . $this->current_dir . $file;

		$extensions1 = array('jpeg', 'gif', 'jpg', 'bmp', 'png');
		$extensions2 = array('js', 'html', 'css', 'htm', 'php', 'txt', 'asp', 'jsp', 'tpl', 'xml', 'swf', 'flv', 'mp3');
		$extensions3 = array('swf', 'flv', 'mp3');

		$fileExt = getFileExt($file);

		if(in_array($fileExt, $extensions1)) {
			$maxwidth  = 80;
			$maxheight = 80;

			if($imagesize = @getimagesize($filepath))	{
				list($width, $height, $type, $attr) = $imagesize;

				$scale = min($maxwidth/$width, $maxheight/$height);
				//$newwidth  = ($scale < 1) ? floor($scale * $width)  : $width;
				$newheight = ($scale < 1) ? floor($scale * $height) : $height;

				$imageurl = SYSDIR . 'public/templates/'.$this->current_dir . $file;

				echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
				<td width="80" height="80" align="center" style="padding:3px;border:1px solid #e8e8e8;"><a href="' . $imageurl . '" target="_blank"><img src="' . $imageurl . '" height="' . $newheight . '"  onMouseMove="yiru(this, '.$width.', '.$height.', event);" onMouseOut="yichu(this);"></a></td>
				<td valign="top" style="padding-left:15px;"><b>' . $file . '</b> (' .DisplayFilesize(filesize($filepath)) . ')<br />' . $width . 'px / ' . $height . 'px (宽/高)<br /><br /><a file="' . $file . '" class="link-btn ajax">删除图片</a>
				</td>
				</tr>
				</table>';
			}
		}else if(in_array($fileExt, $extensions2)){
			$uneditable = in_array($fileExt, $extensions3);

			echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
			<td width="10" valign="top" style="padding-right: 15px;">' . Iif($uneditable, '<img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/uneditable.gif">', '<a href="'.BURL('template/edit/?dir=' . $this->current_dir . '&file=' . $file).'"><img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/editablefile.gif"></a>') . '</td>
			<td valign="top">
			<b>' . $file . '</b> (' .DisplayFilesize(filesize($filepath)) . ')<br /><br />
			' . Iif(!$uneditable, '<a href="'.BURL('template/edit?dir=' . $this->current_dir . '&file=' . $file).'" class="link-btn">编辑文件</a>') . '
			<a file="' . $file . '" class="link-btn ajax">删除文件</a>
			</td>
			</tr>
			</table>';
	  
		}else{
			if($file == '..')	{
				if(!$this->current_dir)	{
					echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr><td width="10" valign="top" style="padding-right: 15px;"><img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/folderforbidden.gif" />
					</td>
					<td valign="top"><br />当前是模板根文件夹.</td>
					</tr>
					</table>';
				}else{
					$tmp_dirname = substr($this->current_dir, 0, -1);
					$tmp_array = explode('/', $tmp_dirname);
					$predirname = str_replace(end($tmp_array), '', $tmp_dirname);

					echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
					<td width="10" valign="top" style="padding-right: 15px;"><a href="'.BURL('template'. Iif($predirname, '?dir=' . $predirname)) . '"><img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/folderup.gif" /></a></td>
					<td valign="top"><b>上层文件夹</b><br /><br /><a href="'.BURL('template'. Iif($predirname, '?dir=' . $predirname)) . '" class="link-btn">返回上层文件夹</a></td>
					</tr>
					</table>';
				}
			}else if(is_dir($filepath)){
				$dirname = $this->current_dir . $file . '/';

				echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
				<td width="10" valign="top" style="padding-right: 15px;"><a href="'.BURL('template?dir='. $dirname) . '"><img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/folder.gif" /></a></td>
				<td valign="top"><b>' . $file . '</b><br /><br /><a href="'.BURL('template?dir='. $dirname) . '" class="link-btn">打开文件夹</a></td>
				</tr>
				</table>';
			}
		}
	}

	private function print_javascript(){
		echo '<script type="text/javascript">
		function yiru(t, tw, th, e){    
			var ei = $$("t_big_image");
			if(!ei){
				var thisstyle = "";
				var thisw = null;
				var thish = null;
				if (tw > 380){
					thisstyle = "width=380px";
					thisw = 380;
					thish = parseInt(380 * th / tw);
				}else if (th > 380){
					thisstyle = "height=380px";
					thisw = parseInt(380 * tw / th);
					thish = 380;
				}else if (th < 160 && tw <= th){
					thisstyle = "height=160px";
					thisw = parseInt(160 * tw / th);
					thish = 160;
				}else if (tw < 160 && tw >= th){
					thisstyle = "width=160px";
					thisw = 160;
					thish = parseInt(160 * th / tw);
				}else{
					thisstyle = "width=" + tw + " height=" + th;
					thisw = tw;
					thish = th;
				}
				var d = document.createElement("DIV");
				d.id = "t_big_image";
				d.style.cssText = "padding:3px;background:#dfdfdf;position:absolute;z-index:88888;border:1px solid #B2B2B2;";
				ei = document.body.appendChild(d);
				ei.innerHTML = "<img src=\"" + t.src + "\"  " + thisstyle + ">";
				ei.style.display = "";
			}
			var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);         
			var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);

			if(ei.offsetHeight > (e.clientY-10)){
				ei.style.top  = scrollTop + e.clientY + 10 + "px";
			}else{
				ei.style.top  = scrollTop + e.clientY - ei.offsetHeight - 10 + "px";
			}
			
			if(ei.offsetWidth > (e.clientX-10)){
				ei.style.left  = scrollLeft + e.clientX + 10 + "px";
			}else{
				ei.style.left = scrollLeft + e.clientX - ei.offsetWidth - 10 + "px";
			}
		}

		function yichu(){
			var ei = $$("t_big_image");
			if(ei){
				document.body.removeChild(ei);
			}
		}
		</script>';
	}

} 

?>