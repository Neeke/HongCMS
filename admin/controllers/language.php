<?php if(!defined('ROOT')) die('Access denied.');

class c_language extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

		$this->lang_path = ROOT.'public/languages/';

		if(!$this->ajax) SubMenu('语言管理'); //根据父对象SAdmin的ajax成员变量, 判断是否为ajax动作
	}

	//ajax动作集合, 能过action判断具体任务
    public function ajax(){
		
		$action = ForceStringFrom('action');
		if($action == 'setlang'){
			$this->select();
		}elseif($action == 'delete'){
			$file = ForceStringFrom('file');

			//不允许删除系统默认的语言文件
			if($file == 'English.php' OR $file == 'Chinese.php'){
				$this->ajax['s'] = 0; //ajax操作失败
				$this->ajax['i'] = '系统默认的语言文件无法删除!';
			}else{

				if(@unlink($this->lang_path.$file))	{
					//无动作
				}else{
					$this->ajax['s'] = 0; //ajax操作失败
					$this->ajax['i'] = '无法删除语言文件! 文件夹不可写或文件不存在.';
				}
			}

		}

		die($this->json->encode($this->ajax));
	}

	//选择并设置语言
    private function select(){
		$siteDefaultLang    = ForceStringFrom('siteDefaultLang');

		if($this->config['siteDefaultLang'] != $siteDefaultLang){
			$filename = ROOT . "config/settings.php";
			$fp = @fopen($filename, 'rb');
			$contents = @fread($fp, @filesize($filename));
			@fclose($fp);
			$contents =  trim($contents);

			$contents = preg_replace("/[$]_CFG\['siteDefaultLang'\]\s*\=\s*[\"'].*?[\"'];/is", "\$_CFG['siteDefaultLang'] = \"$siteDefaultLang\";", $contents);

			$fp = @fopen($filename, 'w');
			@fwrite($fp, $contents);
			@fclose($fp);
		}
	}

	//保存语言文件
    public function save(){
		$filename = ForceStringFrom('filename');
		$file = $this->lang_path . $filename;

		if (is_writable($file)) {
			$filecontent = trim($_POST['filecontent']);
			if (get_magic_quotes_gpc()) {
				$filecontent = stripslashes($filecontent);
			}

			$fd = fopen($file, 'wb');
			fputs($fd,$filecontent);

			Success('language');
		}else{
			$errors = '语言文件('.$filename.')不可写! 请将其属性设置为: 777';
			Error($errors, '编辑语言错误');
		}
	}

	//编辑语言文件
    public function edit(){
		$filename = ForceStringFrom('filename');
		$filepath = $this->lang_path . $filename;

		if(!is_file($filepath)) Error('正在打开的文件不存在!', '打开文件错误');

		$filecontent = htmlspecialchars(implode("",file($filepath)));

		echo '<form method="post" name="editform" action="'.BURL('language/save').'">
		<input type="hidden" name="filename" value="' . $filename . '">';

		TableHeader('编辑语言文件');

		TableRow('<b>注意:</b> <span class=note>语言文件为PHP程序文件, 请使用正确的标点符号!</span><BR><b>当前文件:</b> ' . BASEURL . 'public/languages/'.$filename);
		TableRow('<textarea rows="32" style="width:90%;" name="filecontent" >' . $filecontent . '</textarea>');

		TableFooter();

		PrintSubmit('保存更新', '取消', 1, "确定保存语言文件 $filename 吗?");
	}

    public function index(){
		$Langs = GetLangs();
		foreach($Langs as $val){
			$langoptions .='<option value="'.$val.'"' . Iif($this->config['siteDefaultLang'] == $val, ' SELECTED') . '>'.$val.'</option>';
		}

		TableHeader('设置前台语言');
		TableRow('<form>
			<b>前台语言:</b> <select name="siteDefaultLang"><option value="Auto"' . Iif($this->config['siteDefaultLang'] == 'Auto', ' SELECTED') . '>自动</option>'.$langoptions.'</select>&nbsp;&nbsp;
			<input type="submit" value="保存设置" class="cancel" id="setlang">
			</form>注: 当选择 <span class=note>自动</span> 时, 网站前台将根据用户的浏览器语言自动选择语言, 中文浏览器进入中文, 其它语言浏览器自动进入英文.');
		TableFooter();

		BR(2);

		TableHeader('语言文件列表');

		$files   = GetLangs(1);
		$columncount = 0;

		echo '<td class="td last"><table width="100%" border="0" cellpadding="5" cellspacing="0">';

		for($i = 0; $i < count($files); $i++) {
			$columncount++;

			if($columncount == 1)	{
				echo '<tr>';
			}

			echo '<td width="33%">';
			$this->DisplayFileDetails($files[$i]);
			echo '</td>';

			if($columncount == 3)	{
				echo '</tr>';
				$columncount = 0;
			}
		}
		@closedir($handle);

		if($columncount != 0 && $columncount != 3){
			while($columncount < 3){
				$columncount++;
				echo '<td>&nbsp;</td>';
			}
			echo '</tr>';
		}

		echo '</table></td>';

		TableFooter();

		echo '<script type="text/javascript">
				jQuery(document).ready(function() {
					$("#setlang").click(function(e){
						var data = $(this).parent().serialize();
						ajax("' . BURL('language/ajax?action=setlang') . '", data, function(data){
							$.dialog({title:"操作成功",lock:true,content:"<span class=blue>Ajax操作, 网站前台默认语言设置成功.</span>",okValue:"  确定  ",ok:true,time:1000});
						});

						e.preventDefault();
					});
				});

				$("#main a.ajax").click(function(e){
					var _me=$(this);
					$.dialog({title:"操作确认",lock:true,content:"确定删除语言文件: " + _me.attr("file") + " 吗?",okValue:"  确定  ",
					ok:function(){
						ajax("' . BURL('language/ajax?action=delete') . '", {file: _me.attr("file")}, function(data){
							_me.parent().parent().hide();
						});
					},
					cancelValue:"取消",cancel:true});
					e.preventDefault();
				});
				</script>';

	} 

	private function DisplayFileDetails($file){
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<td width="10" valign="top" style="padding-right: 15px;">
		<a href="'.BURL('language/edit?filename=' . $file).'"><img style="border:1px solid #e8e8e8; padding:3px;" src="'.SYSDIR .'public/admin/images/editablefile.gif" /></a>
		</td>
		<td valign="top">
		<b>' . $file . '</b> (' .DisplayFilesize(@filesize($this->lang_path . $file)). ')<br /><br />
		<a href="'.BURL('language/edit?filename=' . $file).'" class="link-btn">编辑文件</a>
		<a file="' . $file . '" class="link-btn ajax">删除文件</a>
		</td>
		</tr>
		</table>';
	}
} 

?>