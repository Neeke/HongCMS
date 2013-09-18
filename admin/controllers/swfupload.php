<?php if(!defined('ROOT')) die('Access denied.');

class c_swfupload extends SAdmin{

	//错误输出函数
	private function header_err($code){
		header("HTTP/1.1 $code File Upload Error");
		exit();
	}

	public function ajax(){
		@set_time_limit(0);  //解除时间限制

		if (!function_exists('imagecreatetruecolor')){  //不支持GD2
			$this->header_err(535);
		}

		$targetPath = ROOT . 'uploads/';    //改变目录可以测试错误显示

		if (!is_dir($targetPath)){  //上传文件夹不存在
			$this->header_err(536);
		}else if (!is_writable($targetPath)){  //上传文件夹不可写
			@chmod($targetPath, 0777);
			if(!is_writable($targetPath)) {
				$this->header_err(537);
			}
		}

		if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0  || !($_FILES["Filedata"]['tmp_name'] != 'none' && $_FILES["Filedata"]['tmp_name'] && $_FILES["Filedata"]['name'])) { //判断是否为合法的上传文件
			$this->header_err(531);
		}

		$valid_image_types = array('jpeg', 'jpg', 'tif');
		$fileName  = $_FILES["Filedata"]['name'];

		$fileArr = explode('.', basename($fileName));
		$fileExt = strtolower($fileArr[count($fileArr)-1]);

		if(!in_array($fileExt, $valid_image_types)){//通过后缀判断文件类型是否正确, $_FILES["Filedata"]["type"]不可用
			$this->header_err(532);
		}

		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {//判断文件是否太大
			$this->header_err(533);
		}

		$lastName = 'image_'.$this->admin->data['userid']. '_' . md5(uniqid($fileName)) . '.' . $fileExt;
		$fileTemp   = $_FILES["Filedata"]["tmp_name"];

		if(!move_uploaded_file($fileTemp, $targetPath . $lastName)){//判断上传文件是否保存成功
			$this->header_err(534);
		}

	}

} 

?>