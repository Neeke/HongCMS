<?php if(!defined('ROOT')) die('Access denied.');

//editor_upload/ajax模拟ajax访问此控制器
class c_editor_upload extends SAdmin{

	private function alert($msg) {
		header('Content-type: text/html; charset=UTF-8');
		$json = new SJSON;
		echo $json->encode(array('error' => 1, 'message' => $msg));
		exit();
	}

	private function makedir($path) {
		if (!file_exists($path)) {
			mkdir($path, 0777);
			@chmod($path, 0777);
		}
	}

	public function ajax(){

		//文件保存目录路径
		$save_path = ROOT . 'uploads/';
		//文件保存目录URL
		$save_url = BASEURL . 'uploads/';
		//定义允许上传的文件扩展名
		$ext_arr = array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'flash' => array('swf', 'flv'),
			'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
			'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
		);

		//最大文件大小(字节)
		$max_size = 10485760; //10M

		//以上根据实际需要设置

		$post_max_size = @ini_get('post_max_size');
		$upload_max_filesize = @ini_get('upload_max_filesize');

		$p_unit = strtoupper(substr($post_max_size, -1));
		$u_unit = strtoupper(substr($upload_max_filesize, -1));

		$p_multiplier = ($p_unit == 'M' ? 1048576 : ($p_unit == 'K' ? 1024 : ($p_unit == 'G' ? 1073741824 : 1)));
		$u_multiplier = ($u_unit == 'M' ? 1048576 : ($u_unit == 'K' ? 1024 : ($u_unit == 'G' ? 1073741824 : 1)));

		$post_max_size = $p_multiplier*intval($post_max_size);
		$upload_max_filesize = $u_multiplier*intval($upload_max_filesize);

		if($upload_max_filesize < $post_max_size) $post_max_size = $upload_max_filesize;

		//当PHP限制上传文件的大小<$max_size时, 以PHP文件大小限制为准
		if($post_max_size AND $post_max_size < $max_size) $max_size = $post_max_size;

		$save_path = realpath($save_path) . '/';

		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//检查文件名
			if (!$file_name) {
				$this->alert("请选择文件。");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				$this->alert("上传目录不存在。");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				$this->alert("上传目录没有写权限。");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert("临时文件可能不是上传文件。");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				$this->alert("上传文件大小超过限制。");
			}
			//检查目录名
			$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
			if (empty($ext_arr[$dir_name])) {
				$this->alert("目录名不正确。");
			}
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
			}
			//创建文件夹
			if ($dir_name !== '') {
				$save_path .= $dir_name . "/";
				$save_url .= $dir_name . "/";
				$this->makedir($save_path);
			}

			$year = date("Y");
			$save_path .= $year . "/";
			$save_url .= $year . "/";
			$this->makedir($save_path); //创建年份目录

			$monthday = date("m") . date("d");
			$save_path .= $monthday . "/";
			$save_url .= $monthday . "/";
			$this->makedir($save_path); //创建月日目录

			//新文件名
			$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path = $save_path . $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->alert("上传文件失败。");
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
			
			header('Content-type: text/html; charset=UTF-8');
			$json = new SJSON;
			echo $json->encode(array('error' => 0, 'url' => $file_url));
			exit();
		}
	}

} 

?>