<?php if(!defined('ROOT')) die('Access denied.');

class c_database extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

		@set_time_limit(0); //防止操作大数据库表时超时

		$this->backupDir = ROOT. 'system/backup/';
		$this->backupUrl = BASEURL . 'system/backup/';

		if(!$this->ajax) SubMenu('数据库维护'); //根据父对象SAdmin的ajax成员变量, 判断是否为ajax动作
	}

	//下载数据库备份文件时, 伪装成ajax不输出页头等
	public function ajax(){
		$action = ForceStringFrom('action');
		if($action == 'download'){
			$filename = ForceStringFrom('file');

			if (file_exists($this->backupDir . $filename)){
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Type: application/force-download');
				header('Content-Type: application/octet-stream');
				header('Content-Type: application/download');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header('Content-Transfer-Encoding: binary');
				readfile($this->backupDir . $filename);
				exit();
			}else{
				header("Content-type: text/html; charset=utf-8");
				echo '<script type="text/javascript">alert("提定下载的文件 ' . $filename . ' 不存在!")</script>';
			}

		}elseif($action == 'delete'){
			$filename = ForceStringFrom('file');

			if(@unlink($this->backupDir . $filename)){
				//无动作
			}else{
				$this->ajax['s'] = 0; //ajax操作失败
				$this->ajax['i'] = '无法删除数据库备份文件! 文件夹不可写或文件不存在.';
			}

			die($this->json->encode($this->ajax));
		}
	}

	public function restore(){
		$filename = ForceStringFrom('file');
		$fp = openFileRead($this->backupDir . $filename);

		while (!eof($fp)){
			$query .= readFileData($fp, 10000);
		}

		closeFile($fp);

		$queries = ParseQueries($query, ';');

		for($i = 0; $i < count($queries); $i++){
			$sql = trim($queries[$i]);

			if(!empty($sql)){
				$this->db->query($sql);
			}
		}

		Success('database');
	}

	public function index(){
		$this->PrintInstructions();

		echo '<form method="post" action="'.BURL('database/operate').'" name="tables">
		<input type="hidden" name="dbaction" value="">';

		TableHeader('数据库列表');
		TableRow(array('选择', '表名称', '记录数', '数据大小', '索引大小', '空闲', '操作', '', '', '', ''), 'tr0');

		$recordsize = $datasize = $indexsize = $freesize = 0;

		$gettables = $this->db->query("SHOW TABLE STATUS LIKE '". substr(TABLE_PREFIX, 0, -1) ."\_%'");
		while($tableinfo = $this->db->fetch($gettables)){
			TableRow(array('<input type="checkbox" name="tablenames[]" value="' . $tableinfo['Name'] . '">', 
				$tableinfo['Name'], 
				$tableinfo['Rows'], 
				DisplayFilesize($tableinfo['Data_length']), 
				DisplayFilesize($tableinfo['Index_length']), 
				Iif($tableinfo['Data_free'] > 0, '<b>' . DisplayFilesize($tableinfo['Data_free']) . '</b>', 0), 
				'<a href="'.BURL('database/operate?dbaction=checktable&tablename=' . $tableinfo['Name']) . '">查错</a>', 
				'<a href="'.BURL('database/operate?dbaction=optimizetable&tablename=' . $tableinfo['Name']) . '">优化</a>', 
				'<a href="'.BURL('database/operate?dbaction=repairtable&tablename=' . $tableinfo['Name']) . '">修复</a>', 
				'<a href="'.BURL('database/operate?dbaction=backuptable&tablename=' . $tableinfo['Name']) . '">备份</a>', 
				Iif($tableinfo['Name'] == TABLE_PREFIX . 'sessions' OR $tableinfo['Name'] == TABLE_PREFIX . 'vvc', '<a href="#" onclick="'.Confirm('确定清空此数据库表 ' . $tableinfo['Name'] . ' 吗?', 'database/operate?dbaction=emptytable&tablename=' . $tableinfo['Name']).'">清空</a>')));

			$recordsize += $tableinfo['Rows'];
			$datasize += $tableinfo['Data_length'];
			$indexsize += $tableinfo['Index_length'];
			$freesize += $tableinfo['Data_free'];
		}

		TableRow(array('<input type="checkbox" id="checkAll" for="tablenames[]">&nbsp;<label for="checkAll"><B>全选</B></label>', '系统当前共有: '.$this->db->result_nums .' 个表', $recordsize, DisplayFilesize($datasize), DisplayFilesize($indexsize), DisplayFilesize($freesize), '<input type="submit" value="查错" onclick="document.forms[\'tables\'].dbaction.value = \'checkall\';" class="cancel">', '<input type="submit" value="优化" onclick="document.forms[\'tables\'].dbaction.value = \'optimizeall\';" class="cancel">', '<input type="submit" value="修复" onclick="document.forms[\'tables\'].dbaction.value = \'repairall\';" class="cancel">', '<input type="submit" value="备份" onclick="document.forms[\'tables\'].dbaction.value = \'backupall\';" class="cancel">', ''), 'tr2');
		TableFooter();

		BR(2);
		$this->DisplayBackups();

		echo '<script type="text/javascript">
			jQuery(document).ready(function() {
				$("#main a.ajax").click(function(e){
					var _me=$(this);
					$.dialog({title:"操作确认",lock:true,content:"确定删除数据库备份文件: " + _me.attr("file") + " 吗?",okValue:"  确定  ",
					ok:function(){
						ajax("' . BURL('database/ajax?action=delete') . '", {file: _me.attr("file")}, function(data){
							_me.parent().parent().hide();
						});
					},
					cancelValue:"取消",cancel:true});
					e.preventDefault();
				});
			});
		</script>';
	}

	private function PrintInstructions(){
		ShowTips('<B>i) 系统数据库维护工具提示:</B><ul>
		<li><u>查错</u> -- 检查数据库表是否存在错误.</li>
		<li><u>优化</u> -- 回收浪费的闲置空间, 优化数据库表.</li>
		<li><u>修复</u> -- 尝试修复数据库表中的错误.</li>
		<li><u>清空</u> -- '.TABLE_PREFIX.'sessions表记录和验证用户登陆, <span class="note">此表清空后所有已登录用户需重新登录</span>. '.TABLE_PREFIX.'vvc表记录<span class="note">验证码</span>.</li>
		</ul>
		<B>ii) 完全数据备份提示:<ul></B>
		<li>全选可以备份整个数据库, 也可单独备份某个表.</li>
		<li>备份文件保存在网站的 <span class="note">'.$this->backupUrl.'</span> 目录下, 建议下载到本地保存并删除网站中的文件.</li>
		<li>全面备份网站还需要将根目录下的 <span class="note">config/</span> 和 <span class="note">uploads/</span> 等目录下传到本地保存.</li>
		</ul>
		', '维护说明');
	}


	private function DisplayBackups(){
		TableHeader('数据库备份文件');
		TableRow(array('文件名 (/system/backup/)', '大小', '备份日期', '操作', '', ''), 'tr0');

		if (is_dir($this->backupDir)){
			$dir = opendir($this->backupDir);

			while (false !== ($file = readdir($dir))){
				if(strpos(strtolower($file),'.sql') > 0){
					$stats = stat($this->backupDir . $file);
					if($stats['size']>0){
						TableRow(array($file, DisplayFilesize($stats['size']), DisplayDate($stats['mtime']), '<a onclick="'.Confirm('确定恢复备份文件到数据库吗?<br><br>注: <span class=red>数据库中原有数据将被对应的备份数据替换.</span>', 'database/restore?file='.$file).'" class="link-btn">恢复</a>', '<a href="'.BURL('database/ajax?action=download&file='.$file).'" class="link-btn">下载</a>', '<a file="'.$file.'" class="link-btn ajax">删除</a>'));
					}
				}
			}
		}

		TableFooter();
	}

	public function operate(){
		$action = ForceStringFrom('dbaction');
		$tablename = ForceStringFrom('tablename');

		switch ($action){
			case 'checktable':
				$this->PrintResults('数据库表查错', $this->TableOperation($tablename, 'CHECK'));
				break;
			case 'checkall':
				$this->PrintResults('数据库表查错', $this->BatchTableOperation($_POST['tablenames'], 'CHECK'));
				break;
			case 'optimizetable':
				$this->PrintResults('数据库表优化', $this->TableOperation($tablename, 'OPTIMIZE'));
				break;
			case 'optimizeall':
				$this->PrintResults('数据库表优化',$this->BatchTableOperation($_POST['tablenames'], 'OPTIMIZE'));
				break;
			case 'repairtable':
				$this->PrintResults('数据库表修复',$this->TableOperation($tablename, 'REPAIR'));
				break;
			case 'repairall':
				$this->PrintResults('数据库表修复',$this->BatchTableOperation($_POST['tablenames'], 'REPAIR'));
				break;
			case 'backuptable':
				$this->PrintResults('数据库表备份',$this->BackupSingleTable($tablename));
				break;
			case 'backupall':
				$this->PrintResults('数据库表备份', $this->BatchBackupTable($_POST['tablenames']));
				break;
			case 'emptytable':
				$this->PrintResults('数据库表清空', $this->EmptyTable($tablename));
				break;
		}

		$this->index();
	}

	//一些私有数据库操作函数
	private function TableOperation($tablename, $OP){

		$result = $this->db->getOne("$OP TABLE `$tablename`");

		return " '" . $tablename . "' : <font class=greenb>" . $result['Msg_text'] . "</font><br/>";
	}

	private function BatchTableOperation($tablenames, $OP){
		$msg = '';

		for($i = 0; $i < count($tablenames); $i++){
			$msg .= $this->TableOperation($tablenames[$i], $OP);
		}

		return $msg;
	}

	private function BackupTable($tablename, $fp){
		if(isset($fp)){
			// Get the SQL to create the table
			$createTable = $this->db->getOne("SHOW CREATE TABLE `$tablename`");

			// Drop if it exists
			$tableDump = "DROP TABLE IF EXISTS `$tablename`;\n" . $createTable['Create Table'] . ";\n\n";

			writeFileData($fp, $tableDump);

			// get data
			$getRows = $this->db->query("SELECT * FROM `$tablename`");
			$rowCount = 0;

			while ($row = $this->db->fetch($getRows)){
				$tableDump = "INSERT INTO `$tablename` VALUES(";

				$firstfield = 1;

				// get each field's data
				foreach($row AS $value){
					if (!$firstfield){
						$tableDump .= ', ';
					}else{
						$firstfield = 0;
					}

					if (!isset($value)){
						$tableDump .= 'NULL';
					}else if ($value != ''){
						$tableDump .= '\'' . addslashes($value) . '\'';
					}else	{
						$tableDump .= '\'\'';
					}
				}

				$tableDump .= ");\n";

				writeFileData($fp, $tableDump);
				$rowCount++;
			}

			writeFileData($fp, "\n\n\n");

			$msg = "从表 '$tablename' 中备份了 $rowCount 行数据.<br/>";
		}else	{
			$msg = "备份数据库表 '$tablename' 失败!<br/>";
		}

		return $msg;
	}


	private function BackupSingleTable($tablename){
		$theverifycode = substr(md5(rand(0,9999)), 6, 12);
		$filename = $tablename . '_' . $theverifycode . '_' . date("ymd") . '.sql';
		$path = $this->backupDir . $filename;
		$fp = openFileWrite($path);

		if($fp){
			$msg = $this->BackupTable($tablename, $fp);
			closeFile($fp);
		}

		$msg .= '<span class=note>数据已备份到文件: ' . $this->backupUrl . $filename . Iif(function_exists('gzopen'), '.gz'). '</span><br/>';

		return $msg;
	}

	private function BatchBackupTable($tablenames){
		if(!empty($tablenames)) {
			$theverifycode = substr(md5(rand(0,9999)), 6, 12);
			$filename = TABLE_PREFIX . $theverifycode . '_' . date("ymd") . '.sql';
			$path = $this->backupDir . $filename;
			$fp = openFileWrite($path);

			if($fp){
				for($i = 0; $i < count($tablenames); $i++){
					$msg = $msg . $this->BackupTable($tablenames[$i], $fp);
				}
				closeFile($fp);
			}

			$msg .= '<span class=note>数据已备份到文件: ' . $this->backupUrl . $filename . Iif(function_exists('gzopen'), '.gz'). '</span>';
		}

		return $msg;
	}

	private function EmptyTable($tablename){
		$this->db->exe("DELETE FROM `$tablename`");
		$msg = '已完成清空数据库表: ' . $tablename . '<br/>';

		return $msg;
	}

	private function PrintResults($title, $message){
		if($message){
			ShowTips($message, '<font class="blueb">'.$title .'结果:</font>');	
		}else{
			Error('请选择数据库表, 再进行操作!', '维护数据库错误');
		}
	}
} 


// ####################### FILE READ/WRITE USING GZIP  ######################
function openFileWrite($filename){
	if(function_exists('gzopen')){
		$filename .= '.gz';
		$handle = gzopen($filename, "w9");
	}else{
		$handle = fopen($filename, "w");
	}
	return $handle;
}

function openFileRead($filename){
	if(function_exists('gzopen')){
		$handle = gzopen($filename, "r");
	}else{
		$handle = fopen($filename, "r");
	}
	return $handle;
}

function writeFileData($handle, $data){
	if(function_exists('gzwrite')){
		gzwrite($handle, $data);
	}else{
		fwrite($handle, $data);
	}
}

function readFileData($handle, $size){
	if(function_exists('gzread')){
		$data = gzread($handle, $size);
	}else{
		$data = fread($handle, $size);
	}
	return $data;
}

function eof($handle){
	if(function_exists('gzeof')){
		return gzeof($handle);
	}else{
		return feof($handle);
	}
}

function closeFile($handle){
	if(function_exists('gzclose')){
		gzclose($handle);
	}else{
		fclose($handle);
	}
}
// ####################### END FILE READ FUNCTIONS ######################

//处理文件中的SQL语句
function ParseQueries($sql, $delimiter){
    $matches = array();
    $output = array();

    $queries = explode($delimiter, $sql);
	$sql = "";

	$query_count = count($queries);
	for ($i = 0; $i < $query_count; $i++){
		if (($i != ($query_count - 1)) || (strlen($queries[$i] > 0)))	{
			$total_quotes = preg_match_all("/'/", $queries[$i], $matches);
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $queries[$i], $matches);
			$unescaped_quotes = $total_quotes - $escaped_quotes;

			if (($unescaped_quotes % 2) == 0){
				$output[] = $queries[$i];
				$queries[$i] = "";
			}else{
				$temp = $queries[$i] . $delimiter;
				$queries[$i] = "";

				$complete_stmt = false;

				for ($j = $i + 1; (!$complete_stmt && ($j < $query_count)); $j++){
					$total_quotes = preg_match_all("/'/", $queries[$j], $matches);
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $queries[$j], $matches);
					$unescaped_quotes = $total_quotes - $escaped_quotes;

					if (($unescaped_quotes % 2) == 1){
						$output[] = $temp . $queries[$j];

						$queries[$j] = "";
						$temp = "";

						$complete_stmt = true;
						$i = $j;
					}else{
						$temp .= $queries[$j] . $delimiter;
						$queries[$j] = "";
					}
				}
			}
		}
	}

	return $output;
}

?>