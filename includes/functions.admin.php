<?php if(!defined('ROOT')) die('Access denied.');

include(ROOT . 'includes/functions.global.php');

//处理后台伪静态BURL
function BURL($url = ''){
	global $_CFG;

	return SYSDIR . ADMINDIR . '/'. Iif($url, Iif(!$_CFG['siteRewrite'], 'index.php/') . $url);
}

//立即跳转函数 redirect
function Redirect($url = ''){
	echo '<script type="text/javascript">window.location="' . BURL($url) . '";</script>';
	exit();
}

// ############################### BR ################################

function BR($n=1) {
	for($i = 0; $i < $n; $i++)
		echo '<BR>';
}

// ############################### PRINT HEADER ################################

function SubMenu($title, $menus = array()) {
	if(empty($menus)) {
		$s = '<div class="itemtitle"><h3>'.$title.'</h3></div>';
	} else {
		$s = '<div class="itemtitle"><h3>'.$title.'</h3><ul>';
		foreach($menus as $k => $menu) {
			$s .= '<a class="link-btn' . Iif($menu[2], ' link-live') . '" href="' . BURL($menu[1]) . '">' . $menu[0] . '</a>';
		}
		$s .= '</ul></div>';
	}
	echo $s;
}

// ############################### ShowTips ################################

function ShowTips($tips, $tiptitle = '技巧提示') {
	TableHeader($tiptitle);
	TableRow('<div class=tips>' . $tips . '</div>');
	TableFooter();
}

// ############################### Table ################################

function TableHeader($title = '') {
	echo '<table class="tb">';
	if($title) {
		echo '<tr><td colspan="38" class="tbheader">'.$title.'</td></tr>';
	}
}

function TableRow($tdtext = '', $trstyle = '') {
	$cells = '<tr' . Iif($trstyle, ' class='.$trstyle) . '>';
	if(is_array($tdtext)) {
		$last = count($tdtext) - 1;
		foreach($tdtext as $key => $v) {
				$cells .= '<td class="td' . Iif($last == $key, ' last') . '">' . $v . '</td>';
		}
	} else {
		$cells .= '<td colspan="38" valign="middle" class="td">'.$tdtext.'</td>';
	}
	$cells .= '</tr>';
	echo $cells;
}


function TableFooter() {
	echo '</table>';
}

// ############################### PRINT SUBMIT ################################

function PrintSubmit($submit, $cancel = '', $confirm = 0, $confirminfo ='') {
	echo '<div class="submit"><input class="save" type="submit" name="save" value="' . $submit . '"' . Iif($confirm, 'onclick="' . Confirm(Iif($confirminfo, $confirminfo, '确定保存更新吗?'), 'form'). '"') . '>'.Iif($cancel, '<input class="cancel" type="submit" name="cancel" value="' . $cancel . '" onclick="history.back();return false;">').'</div></form>';
}

// ############################### ERROR DIALOG #################################

function Error($errors, $errortitle = ''){
	if(is_array($errors)){
		for($i = 0; $i < count($errors); $i++)
			$errorinfo .= ($i + 1) . ') <span class=red>' . $errors[$i] . '</span><br />';
	}else {
		$errorinfo = '<span class=red>'. $errors . '</span><br />';
	}

	echo "<script>\$.dialog({lock:true,title:'$errortitle',content:'$errorinfo',okValue:'  确定  ',ok:true,beforeunload:function(){history.back();}});</script>";

	exit();//输出错误信息后终止程序执行
}

// ############################### SUCCESS DIALOG #################################

function Success($url, $time = 1){
	echo '<script>$.dialog({lock:true,title:"操作成功",content:"<span class=blue>操作成功, 页面跳转中 ...</span>",okValue:"  确定  ",ok:true,beforeunload:function(){window.location="' . BURL($url) . '";}, time:' . $time*1000 . '});</script>';
	
	exit();
}

// ############################### ShowInfo DIALOG #################################
//$time 默认5秒后自动关闭, 0不关闭
function ShowInfo($info = '呵呵, 想干啥?', $time = 10){
	return "\$.dialog({lock:true,title:'提示信息',content:'$info',ok:true,time:" . $time*1000 . "});return false;";
}

// ############################### Confirm DIALOG #################################

function Confirm($info = '', $url = ''){
	if($url === 'form'){
		return "var _me=\$(this);\$.dialog({title:'操作确认',lock:true,content:'$info',okValue:'  确定  ',ok:function(){_me.closest('form').submit();},cancelValue:'取消',cancel:true});return false;";
	}else{
		return "\$.dialog({title:'操作确认',lock:true,content:'$info',okValue:'  确定  ',ok:function(){window.location='" . BURL($url) . "';},cancelValue:'取消',cancel:true});return false;";
	}

}

// ########################### ADMIN PAGELIST ##############################

function GetPageList($FileName, $PageCount, $CurrentPage = 1, $PagesToDisplay = 10, $PN01 = '', $PNV01 = '', $PN02 = '', $PNV02 = '', $PN03 = '', $PNV03 = '', $PN04 = '', $PNV04 = '', $PN05 = '', $PNV05 = '') {

	$PreviousText =  '&nbsp;&#60;&#60;&nbsp;';
	$NextText = '&nbsp;&#62;&#62;&nbsp;';

	$Params = '';
	$Params .= Iif($PN01 AND $PNV01, '&'.$PN01.'='.$PNV01);
	$Params .= Iif($PN02 AND $PNV02, '&'.$PN02.'='.$PNV02);
	$Params .= Iif($PN03 AND $PNV03, '&'.$PN03.'='.$PNV03);
	$Params .= Iif($PN04 AND $PNV04, '&'.$PN04.'='.$PNV04);
	$Params .= Iif($PN05 AND $PNV05, '&'.$PN05.'='.$PNV05);

	$iPagesToDisplay = $PagesToDisplay - 2;      
	if ($iPagesToDisplay <= 8) $iPagesToDisplay = 8;

	$MidPoint = ($iPagesToDisplay / 2);

	$FirstPage = $CurrentPage - $MidPoint;
	if ($FirstPage < 1) $FirstPage = 1;

	$LastPage = $FirstPage + ($iPagesToDisplay - 1);

	if ($LastPage > $PageCount) {
		$LastPage = $PageCount;
		$FirstPage = $PageCount - $iPagesToDisplay;
		if ($FirstPage < 1) $FirstPage = 1;
	}

	$sReturn = '<div class="PageListDiv"><ol class="PageList">';
	$Loop = 0;
	$iTmpPage = 0;

	if ($PageCount > 1) {
		if ($CurrentPage > 1) {
			$iTmpPage = $CurrentPage - 1;
			$sReturn .= '<li><a href="' . $FileName . '?p=' . $iTmpPage . $Params . '" class="PagePrev"  onfocus="this.blur()">'.$PreviousText.'</a></li>';
		} else {
			$sReturn .= '<li><span class="NoPagePrev">'.$PreviousText.'</span></li>';
		}

		if ($FirstPage > 2) {
			$sReturn .= '&nbsp;<li><a href="' . $FileName . '?p=1' . $Params . '" onfocus="this.blur()">1</a></li>&nbsp;<li>...</li>';
		} elseif ($FirstPage == 2) {
			$sReturn .= '&nbsp;<li><a href="' . $FileName . '?p=1' . $Params . '" onfocus="this.blur()">1</a></li>';
		}

		$Loop = 0;

		for ($Loop = 1; $Loop <= $PageCount; $Loop++) {
			if (($Loop >= $FirstPage) && ($Loop <= $LastPage)) {
				if ($Loop == $CurrentPage) {
					$sReturn .= '&nbsp;<li><span class="CurrentPage">'.$Loop.'</span></li>';
				} else {
					$sReturn .= '&nbsp;<li><a href="' . $FileName . '?p=' . $Loop . $Params . '" onfocus="this.blur()">'.$Loop.'</a></li>';
				}
			}
		}

		if ($CurrentPage < ($PageCount - $MidPoint) && $PageCount > $PagesToDisplay - 1) {
			$sReturn .= '&nbsp;<li>...</li>&nbsp;<li><a href="' . $FileName . '?p=' . $PageCount . $Params . '" onfocus="this.blur()">'.$PageCount.'</a></li>';
		} else if ($CurrentPage == ($PageCount - $MidPoint) && ($PageCount > $PagesToDisplay)) {
			$sReturn .= '&nbsp;<li><a href="' . $FileName . '?p=' . $PageCount . $Params . '" onfocus="this.blur()">'.$PageCount.'</a></li>';
		}

		if ($CurrentPage != $PageCount) {
			$iTmpPage = $CurrentPage + 1;
			$sReturn .= '&nbsp;<li><a href="' . $FileName . '?p=' . $iTmpPage . $Params . '" class="PageNext" onfocus="this.blur()">'.$NextText.'</a></li>';
		} else {
			$sReturn .= '&nbsp;<li><span class="NoPageNext">'.$NextText.'</span></li>';
		}
	} else {
		$sReturn .= '<li>&nbsp;</li>';
	}

	$sReturn .= '</ol></div>';

	return  $sReturn;
}

// ##############################################################

function GetTemps() {
	$Templates = array();
	$TempPath = ROOT . 'public/templates/';
	$FolderHandle = @opendir($TempPath);
	while (false !== ($Item = readdir($FolderHandle))) {
		if (is_dir($TempPath.$Item) && $Item != '.' 	&& $Item != '..') {
			if (substr($Item, 0, 1) != ".") $Templates[] = $Item;
		}
	}
	@closedir($LangPath);
	return $Templates;
}

// ##############################################################

function GetLangs($filename = 0) {
	$Languages = array();
	$LangPath = ROOT . 'public/languages/';
	$FolderHandle = @opendir($LangPath);
	while (false !== ($Item = readdir($FolderHandle))) {
		if (filesize($LangPath.$Item) && $Item != '.' 	&& $Item != '..' && substr($Item, -4) == '.php') {
			if (substr($Item, 0, 1) != ".") {
				$Languages[] = Iif($filename, $Item, substr($Item, 0, -4));
			}
		}
	}
	@closedir($LangPath);
	return $Languages;
}

// ###############################################################

function MakeDir($path) {
	if (!file_exists($path)) {
		mkdir($path, 0777);
		@chmod($path, 0777);
	}
}

// ###############################################################

function IsUploadedFile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

// ###############################################################

function UploadFile($sourcefile, $destination_path, $filename, $filesizelimit, $valid_types, $valid_exts = array()) {
	$sourcefile_size = $sourcefile['size'];

	//获得文件后缀
	$temp_arr = explode(".", $sourcefile['name']);
	$file_ext = strtolower(trim(array_pop($temp_arr)));

	if($sourcefile_size == 0 OR !CheckUploadSize($sourcefile_size, $filesizelimit))	{
		$errors = '上传的文件超过系统设置的大小: '.DisplayFilesize($filesizelimit).' 或超过了服务器PHP环境允许上传的文件大小: '.ini_get('upload_max_filesize');
	}elseif(!in_array($sourcefile['type'], $valid_types) OR (!empty($valid_exts) AND !in_array($file_ext, $valid_exts))){
		$errors = '上传的文件类型不允许!';
	}elseif (!is_uploaded_file($sourcefile['tmp_name'])){
		$errors = '上传文件无效!';
	}else{
		MakeDir($destination_path); //仅当有文件上传时新建项目文件夹

		$destination_file = $destination_path . $filename . '.' . $file_ext;  //场景目标文件名及路径

		if (move_uploaded_file($sourcefile['tmp_name'], $destination_file) === false) {
			$errors = '上传文件失败!';
		}else{
			return true; //上传成功返回true
		}
	}

	return $errors; //有错误时返回错误信息
}

// ###############################################################

function DeleteDir($dirName, $del_self = true) {
	if($handle = @opendir($dirName)){
	   while(false !== ($item = @readdir($handle))){
		   if($item != "." && $item != ".."){
			   if(@is_dir("$dirName/$item")){
				   DeleteDir("$dirName/$item");
			   }else{
				   @unlink("$dirName/$item");
			   }
		   }
	   }

	   @closedir($handle);

	   if($del_self) @rmdir($dirName);
	}
}

// 获得文件后缀函数
function getFileExt($filename) {
	$temp_arr = explode(".", $filename);
	$file_ext = strtolower(trim(array_pop($temp_arr)));

	if($filename == $file_ext) return ''; //没有后缀返回空字符串

	return $file_ext;
}

// ###############################################################

function CheckUploadSize($filesize, $limit = 0) { //$limit默认不限
	if($limit && $filesize > $limit) return false;

	$post_max_size = @ini_get('post_max_size');
	$upload_max_filesize = @ini_get('upload_max_filesize');

	$p_unit = strtoupper(substr($post_max_size, -1));
	$u_unit = strtoupper(substr($upload_max_filesize, -1));

	$p_multiplier = ($p_unit == 'M' ? 1048576 : ($p_unit == 'K' ? 1024 : ($p_unit == 'G' ? 1073741824 : 1)));
	$u_multiplier = ($u_unit == 'M' ? 1048576 : ($u_unit == 'K' ? 1024 : ($u_unit == 'G' ? 1073741824 : 1)));

	$post_max_size = $p_multiplier*intval($post_max_size);
	$upload_max_filesize = $u_multiplier*intval($upload_max_filesize);

	if($upload_max_filesize < $post_max_size) $post_max_size = $upload_max_filesize;

	if($filesize > $post_max_size) {
		return false;
	}else{
		return true;
	}
}

// ###############################################################

function CreateImageFile($src_path, $des_path, $new_dims) {
	$source = @imagecreatefromjpeg($src_path);

	if ($source) {
		$imageX = @imagesx($source);
		$imageY = @imagesy($source);
		
		if ($imageX >= $imageY) {
			$thumbX = $new_dims;
			$thumbY = (int)(($thumbX*$imageY)/$imageX);
		} else {
			$thumbY = $new_dims;
			$thumbX = (int)(($thumbY*$imageX)/$imageY);
		}

		$dest_thum  = @imagecreatetruecolor($new_dims, $new_dims);
		$bg = @imagecolorallocate($dest_thum, 255, 255, 255);
		@imagefill($dest_thum, 0, 0, $bg);
		@imagecopyresampled ($dest_thum, $source, ($new_dims - $thumbX)/2, ($new_dims - $thumbY)/2, 0, 0, $thumbX, $thumbY, $imageX, $imageY);
		@imageinterlace($dest_thum);
		@imagejpeg($dest_thum,$des_path,88);
		@ImageDestroy($dest_thum);
		@ImageDestroy($source);
	}
}


?>