<?php if(!defined('ROOT')) die('Access denied.');

class c_settings extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

		SubMenu('网站设置', array(
			array('基本设置', 'settings', Iif($path[1] == 'index',1,0)),
			array('邮件设置', 'settings/mail', Iif($path[1] == 'mail',1,0))
		));
	}

    public function save(){
		$action = ForceStringFrom('action');
		$filename = ROOT . "config/settings.php";

		if(!is_writeable($filename)) {
			$errors = '请将系统配置文件config/settings.php设置为可写, 即属性设置为: 777';
		}

		if(isset($errors)){
			Error($errors, '系统设置错误');
		}else{
			$settings    = $_POST['settings'];
			$fp = @fopen($filename, 'rb');
			$contents = @fread($fp, filesize($filename));
			@fclose($fp);
			$contents =  trim($contents);
			$oldcontents = $contents;

			foreach($settings as $key => $value){
				if($this->config[$key] != $settings[$key]){
					$value = ForceString($value);
					
					if($key == 'siteBaseUrl' AND substr($value, -1) != '/') $value .= '/';

					switch($key){
						case 'siteSmall':
							$value = ForceInt($value);
							if($value < 40) $value = 40;
							break;
						case 'siteMiddle':
							$value = ForceInt($value);
							if($value < 160) $value = 160;
							break;
						case 'siteLarge':
							$value = ForceInt($value);
							if($value < 760) $value = 760;
							break;
					}

					$code = ForceString($key);
					$contents = preg_replace("/[$]_CFG\['$code'\]\s*\=\s*[\"'].*?[\"'];/is", "\$_CFG['$code'] = \"$value\";", $contents);
				}
			}

			if($contents != $oldcontents){
				$fp = @fopen($filename, 'w');
				@fwrite($fp, $contents);
				@fclose($fp);
			}

			Success('settings'. Iif($action, '/'.$action));
		}
	}

    public function index(){

		echo '<form method="post" action="'.BURL('settings/save').'">';

		TableHeader('基本设置');

		TableRow(array('<B>网站URL</B><BR>网站完整的URL, 用于正确显示编辑器中上传的图片、邮件发送等. 请以 <span class=note>/</span> 结束.', '<input type="text" style="width:292px;" name="settings[siteBaseUrl]" value="' . $this->config['siteBaseUrl'] . '">'));

		$Radio = new SRadio;
		$Radio->Name = 'settings[siteRewrite]';
		$Radio->SelectedID = $this->config['siteRewrite'];
		$Radio->AddOption(1, '开启', '&nbsp;&nbsp;&nbsp;&nbsp;');
		$Radio->AddOption(0, '关闭', '&nbsp;&nbsp;');
		TableRow(array('<B>URL友好访问模式(伪静态)</B><BR>如果服务器是Apache环境, 且Rewrite重写模式有效, 可设置为 <span class=note>开启</span>, 有利于搜索引擎收录您的网页. <BR>如果网站前台链接无效或访问不正常, 说明服务器不支持此功能, 需要重新设置为 <span class=note>关闭</span>.', $Radio->Get()));

		$Select = new SSelect;
		$Select->Name = 'settings[siteTimezone]';
		$Select->SelectedValue = $this->config['siteTimezone'];
		$Select->AddOption('-12', '(GMT -12) Eniwetok,Kwajalein');
		$Select->AddOption('-11', '(GMT -11) Midway Island,Samoa');
		$Select->AddOption('-10', '(GMT -10) Hawaii');
		$Select->AddOption('-9', '(GMT -9) Alaska');
		$Select->AddOption('-8', '(GMT -8) Pacific Time(US & Canada)');
		$Select->AddOption('-7', '(GMT -7) Mountain Time(US & Canada)');
		$Select->AddOption('-6', '(GMT -6) Mexico City');
		$Select->AddOption('-5', '(GMT -5) Bogota,Lima');
		$Select->AddOption('-4', '(GMT -4) Caracas,La Paz');
		$Select->AddOption('-3', '(GMT -3) Brazil,Buenos Aires,Georgetown');
		$Select->AddOption('-2', '(GMT -2) Mid-Atlantic');
		$Select->AddOption('-1', '(GMT -1) Azores,CapeVerde Islands');
		$Select->AddOption('', '(GMT) London,Lisbon,Casablanca');
		$Select->AddOption('+1', '(GMT +1) Paris,Brussels,Copenhagen');
		$Select->AddOption('+2', '(GMT +2) Kaliningrad,South Africa');
		$Select->AddOption('+3', '(GMT +3) Moscow,Baghdad,Petersburg');
		$Select->AddOption('+4', '(GMT +4) Abu Dhabi,Muscat,Baku,Tbilisi');
		$Select->AddOption('+5', '(GMT +5) Karachi,Islamabad,Tashkent');
		$Select->AddOption('+6', '(GMT +6) Almaty,Dhaka,Colombo');
		$Select->AddOption('+7', '(GMT +7) Bangkok,Hanoi,Jakarta');
		$Select->AddOption('+8', '(GMT +8) 北京, 香港, 新加坡');
		$Select->AddOption('+9', '(GMT +9) Tokyo,Osaka,Yakutsk');
		$Select->AddOption('+10', '(GMT +10) Australia,Guam,Vladivostok');
		$Select->AddOption('+11', '(GMT +11) Magadan,Solomon Islands');
		$Select->AddOption('+12', '(GMT +12) Auckland,Wellington,Fiji');
		TableRow(array('<B>网站默认时区</B><BR>'.APP_NAME.'中英文网站系统将按默认时区显示日期和时间.', $Select->Get()));

		$Select->Clear();
		$Select->Name = 'settings[siteDateFormat]';
		$Select->SelectedValue = $this->config['siteDateFormat'];
		$Select->AddOption('Y-m-d', "2010-08-12");
		$Select->AddOption('Y-n-j', "2010-8-12");
		$Select->AddOption('Y/m/d', "2010/08/12");
		$Select->AddOption('Y/n/j', "2010/8/12");
		$Select->AddOption('Y年n月j日', "2010年8月12日");
		$Select->AddOption('m-d-Y', "08-12-2010");
		$Select->AddOption('m/d/Y', "08/12/2010");
		$Select->AddOption('M j, Y', "Aug 12, 2010");
		TableRow(array('<B>日期格式</B><BR>系统显示日期的格式.', $Select->Get()));

		TableRow(array('<B>小图尺寸(像素)</B><BR>上传的产品图片生成三种规格的缩略图(小、中、大), 其中小图的长宽为:', '<input type="text" style="width:80px;" name="settings[siteSmall]" value="' . $this->config['siteSmall'] . '">'));
		TableRow(array('<B>中图尺寸(像素)</B><BR>上传的产品图片生成三种规格的缩略图(小、中、大), 其中中图的长宽为:', '<input type="text" style="width:80px;" name="settings[siteMiddle]" value="' . $this->config['siteMiddle'] . '">'));
		TableRow(array('<B>大图尺寸(像素)</B><BR>上传的产品图片生成三种规格的缩略图(小、中、大), 其中大图的长宽为:', '<input type="text" style="width:80px;" name="settings[siteLarge]" value="' . $this->config['siteLarge'] . '">'));

		$Radio ->Clear();
		$Radio->Name = 'settings[siteActived]';
		$Radio->SelectedID = $this->config['siteActived'];
		$Radio->AddOption(1, '开启', '&nbsp;&nbsp;&nbsp;&nbsp;');
		$Radio->AddOption(0, '关闭', '&nbsp;&nbsp;');
		TableRow(array('<B>开启或关闭网站</B><BR>当系统进行升级, 数据库备份或恢复等维护操作时, 推荐先关闭网站.', $Radio->Get()));

		TableRow(array('<B>关闭时显示(<span class=blue>中文</span>)</B><BR>网站关闭后显示的中文提示信息(允许HTML).', '<textarea name="settings[siteOffTitle]" rows="4" style="width:292px;">' . $this->config['siteOffTitle'] . '</textarea>'));

		TableRow(array('<B>关闭时显示(<span class=red>English</span>)</B><BR>网站关闭后显示的英文提示信息(允许HTML).', '<textarea name="settings[siteOffTitleEn]" rows="4" style="width:292px;">' . $this->config['siteOffTitleEn'] . '</textarea>'));

		TableRow(array('<B>网站名称(<span class=blue>中文</span>)</B><BR>在网站页面底部等处的版权信息, 邮件中显示的中文网站名称.', '<input type="text" style="width:292px;" name="settings[siteCopyright]" value="' . $this->config['siteCopyright'] . '">'));

		TableRow(array('<B>网站名称(<span class=red>English</span>)</B><BR>在网站页面底部等处的版权信息, 邮件中显示的英文网站名称.', '<input type="text" style="width:292px;" name="settings[siteCopyrightEn]" value="' . $this->config['siteCopyrightEn'] . '">'));

		TableRow(array('<B>网站标题(<span class=blue>中文</span>)</B><BR>显示在浏览器上方的中文网站Title标题.', '<input type="text" style="width:292px;" name="settings[siteTitle]" value="' . $this->config['siteTitle'] . '">'));

		TableRow(array('<B>网站标题(<span class=red>English</span>)</B><BR>显示在浏览器上方的中文网站Title标题.', '<input type="text" style="width:292px;" name="settings[siteTitleEn]" value="' . $this->config['siteTitleEn'] . '">'));

		TableRow(array('<B>Meta关键字(<span class=blue>中文</span>)</B><BR>便于搜索引擎收录和搜索您的网站, 多个Meta关键字需用英文逗号隔开.', '<input type="text" style="width:292px;" name="settings[siteKeywords]" value="' . $this->config['siteKeywords'] . '">'));

		TableRow(array('<B>Meta关键字(<span class=red>English</span>)</B><BR>便于搜索引擎收录和搜索您的网站, 多个Meta关键字需用英文逗号隔开.', '<input type="text" style="width:292px;" name="settings[siteKeywordsEn]" value="' . $this->config['siteKeywordsEn'] . '">'));

		TableRow(array('<B>网站备案信息</B><BR>在网站页面底部添加备案信息链接, <span class=note>不显示可留空</span>.', '<input type="text" style="width:292px;" name="settings[siteBeian]" value="' . $this->config['siteBeian'] . '">'));

		TableFooter();

		PrintSubmit('保存设置', '取消');
	} 

    public function mail(){

		echo '<form method="post" action="'.BURL('settings/save').'">
		<input type="hidden" name="action" value="mail">';

		TableHeader('邮件设置');

		TableRow(array('<B>网站Email地址</B><BR>接收用户邮件, 及发送邮件时显示在邮件的回复地址中.', '<input type="text" style="width:292px;" name="settings[siteEmail]" value="' . $this->config['siteEmail'] . '">'));

		TableRow(array('<B>邮件发送方式</B><BR>如果网站服务器是Windows系统, 则必须选择SMTP方式才能发送邮件(<span class=note>要求服务器php环境支持Sockets</span>).<BR>UNIX或linux服务器则推荐使用PHP Mail函数发送邮件.', '<input type="radio" id="m1" name="settings[siteUseSmtp]" value="0" '.Iif(!$this->config['siteUseSmtp'], ' checked="checked"').'><label for="m1">PHP Mail</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="m2" name="settings[siteUseSmtp]" value="1" '.Iif($this->config['siteUseSmtp'], ' checked="checked"').'><label for="m2">SMTP</label>'));

		TableRow(array('<B>-- SMTP服务器地址</B><BR>如: mailer.weentech.com 或SMTP邮件服务器IP地址.', '<input type="text" style="width:292px;" name="settings[siteSmtpHost]" value="' . $this->config['siteSmtpHost'] . '">'));
		TableRow(array('<B>-- SMTP服务器端口</B><BR>SMTP邮件服务器的端口号, 一般为25.', '<input type="text" style="width:292px;" name="settings[siteSmtpPort]" value="' . $this->config['siteSmtpPort'] . '">'));
		TableRow(array('<B>-- SMTP服务器邮箱</B><BR>使用当前SMTP邮件服务器时您的Email地址, 此Email地址仅用于发送邮件, 不用于接收Email.', '<input type="text" style="width:292px;" name="settings[siteSmtpEmail]" value="' . $this->config['siteSmtpEmail'] . '">'));
		TableRow(array('<B>-- SMTP服务器邮箱用户名</B><BR>登录SMTP服务器邮箱的用户名. 注: 有的SMTP服务器需求填写为用户名对应的邮箱地址.', '<input type="text" style="width:292px;" name="settings[siteSmtpUser]" value="' . $this->config['siteSmtpUser'] . '">'));
		TableRow(array('<B>-- SMTP服务器用户密码</B><BR>登录SMTP服务器邮箱的用户密码.', '<input type="password" style="width:292px;" name="settings[siteSmtpPassword]" value="' . $this->config['siteSmtpPassword'] . '">'));

		TableFooter();

		PrintSubmit('保存设置');
	} 

} 

?>