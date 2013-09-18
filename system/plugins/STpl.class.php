<?php if(!defined('ROOT')) die('Access denied.');

//STpl模板类
class STpl{
	var $_tpl_vars             = array();
	var $tpl_left_delimiter  =  '{';
	var $tpl_right_delimiter =  '}';
	var $tpl_template_dir    =  'templates/';
	var $tpl_compile_dir     =  'cache/';
	var $tpl_safe_mode = false;
	var $tpl_check = true;

	public function assign($tpl_var, $value = null){
		if (is_array($tpl_var)){
			foreach ($tpl_var as $key => $val) {
				if ($key != '') $this->_tpl_vars[$key] = $val;
			}
		} else {
			if ($tpl_var != '')	$this->_tpl_vars[$tpl_var] = $value;
		}
	}

	public function display($tpl){
		$tplTemp = explode('/',  $tpl); //支持下级目录, 如: $tpl->display('dir1/xxxx.tpl');

		if(count($tplTemp) > 1){
			$tplCacheDir = $this->tpl_compile_dir . $tplTemp[0] . '/';
			$tpl_real  = $this->tpl_template_dir . $tplTemp[0] . '/' . $tplTemp[1]; //模板文件路径
			$compiled_file = $tplCacheDir . base64_encode($tplTemp[1]) . ".%%.tpl"; //缓存文件路径
		}else{
			$tplCacheDir = $this->tpl_compile_dir;
			$tpl_real  = $this->tpl_template_dir . $tpl;
			$compiled_file = $tplCacheDir . base64_encode($tpl) . ".%%.tpl";
		}

		//未编译或模板文件已修改时, 编译生成模板缓存文件
		if(!is_file($compiled_file) || ($this->tpl_check && filemtime($tpl_real)>filemtime($compiled_file))){
			if(!is_dir($tplCacheDir)){
				mkdir($tplCacheDir, 0777, true);
			}

			$compiled_contents = $this->_compile(file_get_contents($tpl_real));
			file_put_contents($compiled_file, $compiled_contents, LOCK_EX);
		}

		include($compiled_file);
	}

	private function _match($matches){
		$content = $matches[1];

		//include或require包含文件
		if(preg_match('/^(include|require)[\s|\(]+["|\']?([\w\.\-\/]+)["|\']?[\s|\)]*$/msi', $content, $matches)){
			$content = "\$this->display('{$matches[2]}')";
		}else{
			//替换 if,elseif,/if; foreach,/foreach; for,/for
			$pattern = "/^(if|foreach|for)(([\s|\(]+)(.+))/msi";
			$content = preg_replace_callback($pattern,create_function('$m','$t = trim($m[3]);$v = trim($m[2]);if(empty($t)){return "{$m[1]}($v){";}else{return "{$m[1]}$v{";}'),$content);
			$patterns = array("/^(elseif)([\s*|\\(].*)/msi","/^(else)/msUi","/^\/(if|foreach|for)/msi");
			$replacements=array('}\\1(\\2){','}\\1{','}');
			$content = preg_replace($patterns,$replacements,$content);

			//替换变量或输出变量(包括对象成员变量或函数)
			$content = preg_replace_callback('/\$(\w+)([\s]*\.[\s]*(\w+))*/ms', create_function('$m', '$arr=explode(".",$m[0]);array_shift($arr);$r="\$this->_tpl_vars[\'".$m[1]."\']";foreach($arr AS $a){$r.="[\'".trim($a)."\']";}return $r;'), $content);
			$content = preg_replace('/^(\$this->_tpl_vars((\[["|\']\w+["|\']\])+)(->.+)*)$/ms', "echo \\1", $content);
		}

		$content="<?php $content; ?>";
		return $content;
	}

	//编译
	private function _compile($content){
		$left_delimiter_quote = preg_quote($this->tpl_left_delimiter);
		$right_delimiter_quota= preg_quote($this->tpl_right_delimiter);

		//安全模式, 替换php可执行代码
		if($this->tpl_safe_mode){
			$pattern="/\\<\\?.*\\?>/msUi";
			$content = preg_replace($pattern, '<!-- PHP CODE REPLACED ON SAFE MODE -->', $content);
		}

		//替换注释: {*xxx*}
		$pattern="/{$left_delimiter_quote}\*(.*)\*{$right_delimiter_quota}/msU";
		$content = preg_replace($pattern, "<?php /*\\1*/?>", $content);

		//调用_match函数编译
		$pattern="/{$left_delimiter_quote}([\S].*){$right_delimiter_quota}/msU";
		return preg_replace_callback($pattern, array(&$this, '_match'), $content);
	}

	//清空当前模板缓存
	public function clear_compiled_tpl(){
		tpl_remove_cache($this->tpl_compile_dir);
	}
}

//清空模板缓存函数, 不写在模板类文件中, 方便独立使用和减少类成员函数
//需要独立使用时需将此函数移到系统函数库文件中
function tpl_remove_cache($dirPath) {
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

?>