<?php if(!defined('ROOT')) die('Access denied.');

class admin{

	var $data = null; //保存用户信息

	public function __construct($ajax = 0){
		global $DB;

		$this->db = & $DB;  //引用全局数据库连接实例

		$this->auth($ajax); //admin类构造时就进行授权
	}


	/**
	 * private 授权函数 auth
	 */
	private function auth($ajax){
		$sessionid = ForceCookieFrom(COOKIE_ADMIN);
		$useragent = substr(ForceString($_SERVER['HTTP_USER_AGENT']), 0, 252);

		$check_agent = true; //是否验证用户浏览器

		if(IsPost('sessionid')) {//swfupload使用
			$sessionid = ForceStringFrom('sessionid');
			$check_agent = false; //swf上传时, $useragent的值为: Shockwave Flash, 此时不能验证用户浏览器
		}

		if($sessionid AND IsAlnum($sessionid)){//登录成功验证cookie授权
			$sql = "SELECT s.sessionid, u.userid, u.username, u.activated, u.nickname FROM " . TABLE_PREFIX . "sessions s
						LEFT JOIN " . TABLE_PREFIX . "admin u ON u.userid = s.userid
						WHERE s.sessionid    = '$sessionid'
						" . Iif($check_agent, " AND s.useragent = '$useragent' ") . "
						AND   s.admin = 1
						AND   u.activated = 1";

			$userinfo = $this->db->getOne($sql);

			if(!$userinfo OR !$userinfo['userid']){ //用户不合法, 清除cookie, 重新登录
				setcookie(COOKIE_ADMIN, '', 0, '/');

				if(!$ajax AND $check_agent) $this->login(); //ajax或swfupload上传时, 不输出登录窗口
			}else{
				$this->data = $userinfo; //授权成功, 执行后面的程序
			}
		}else{
			if(!$ajax AND $check_agent) $this->login(); //ajax或swfupload上传时, 不输出登录窗口
		}
	}

	/**
	 * private 输出用户登录窗口 login
	 */
	private function login(){
		$info = '';

		if(IsPost('submit')){
			$info = $this->check();
		}

		$info = Iif($info, "<font color='#ff3300'>$info</font>", '请输入用户名和密码.');

		$key = PassGen(8);
		$code = authcode(md5(WEBSITE_KEY), 'ENCODE', $key, 1800);

		echo '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>'.APP_NAME.' - 管理登录</title>
<link rel="stylesheet" type="text/css" href="'. SYSDIR .'public/admin/login.css">
</head>
<body>
<div id="logo">
	<img src="'. SYSDIR .'public/admin/images/logo-login.png" alt="'.APP_NAME.'"> 
</div>
<div id="login">
	<form id="loginform" action="' . BURL() . '" method="post">
	<input type="hidden" name="key" value="'.$key.'">
	<input type="hidden" name="code" value="'.$code.'">
	<p id="info">' . $info . '</p>
		<div class="control-group">
			<span class="icon-user"></span><input name="username" placeholder="Username" type="text">
		</div>

		<div class="control-group">
			<span class="icon-lock"></span><input name="password" placeholder="Password" type="password">
		</div>

		<div class="remember-me">
				<input name="remember" value="1" type="checkbox" id="rm"><label for="rm">记住我</label>
				<a href="" id="forget-password">忘记密码?</a>
		</div>

		<div class="login-btn">
			<input id="login-btn" value="登 录" type="submit" name="submit">
		</div>
	</form>

	<form id="forgotform" class="hide" action="./" method="post">
		<p id="info2">请输入Email地址重设密码.</p>
		<div class="control-group">
			<span class="icon-mail"></span><input name="email" placeholder="Email" type="text">
		</div>

		<div class="login-btn forget-btn">
			<input id="forget-btn" value="提 交" type="submit">
		</div>
	</form>

</div>

<div id="login-copyright">
	'.date("Y").' &copy; HongCMS <a href="http://www.weentech.com" target="_blank">weentech.com</a>
</div>

<script src="'. SYSDIR .'public/js/jquery-1.8.3.min.js" type="text/javascript"></script>   
<script>
$(document).ready(function() {     
	$("#logo").css("margin-top", ($(window).height()-460)/2+"px");
	$("input[name=\'username\']").focus();

	$("#forget-password").click(function (e) {
		$("#loginform").hide();
		$("#forgotform").show(200);
		e.preventDefault();
	});

	$("#forget-btn").click(function (e) {

		$("#loginform").slideDown(200);
		$("#forgotform").slideUp(200);
		e.preventDefault();
	});

});
</script>

</body>
</html>';

		exit(); //终止程序继续运行  important !!!!!
	}


 	/**
	 * 登录验证
	 */
   private function check(){
		$username = ForceStringFrom('username');
		$password = ForceStringFrom('password');
		$remember = ForceIntFrom('remember');
		$key = ForceStringFrom('key');
		$code = ForceStringFrom('code');
		$decode = authcode($code, 'DECODE', $key);

		if(!strlen($username) OR !strlen($password)){
			$error = '请输入用户名和密码!';
		}elseif(!isName($username)){
			$error = '用户名存在非法字符!';
		}elseif($decode != md5(WEBSITE_KEY)){
			$error = '验证码过期, 请重新登录!';
		}else{
			$password = md5($password);

			$user = $this->db->getOne("SELECT u.userid FROM " . TABLE_PREFIX . "admin u WHERE u.username = '$username' AND u.password = '$password' AND u.activated = 1");

			if(!$user['userid']){
				$error = '用户不存在或密码错误!';
			}else{//授权成功, 执行相关操作
				$userip = GetIP();
				$timenow = time();
				$sessionid = md5(uniqid($user['userid'] . COOKIE_KEY));
				$useragent = substr(ForceString($_SERVER['HTTP_USER_AGENT']), 0, 252);

				$this->db->exe("INSERT INTO " . TABLE_PREFIX . "sessions (sessionid, userid, ipaddress, useragent, created, admin)
						  VALUES ('$sessionid', '$user[userid]', '$userip', '$useragent', '$timenow', 1) ");
				$this->db->exe("UPDATE " . TABLE_PREFIX . "admin SET lastdate = '$timenow', lastip = '$userip', loginnum = (loginnum + 1)  WHERE userid = '$user[userid]' ");

				$time = Iif($remember, time()+3600*24*30, 0);

				setcookie(COOKIE_ADMIN, $sessionid, $time, '/');

				Redirect(); //登录验证成功后跳转到首页
			}
		}

		return $error; //提交数据有错误或验证用户失败, 返回错误信息在登录中显示
	}


	/**
	 * public 退出登录函数logout
	 */
    public function logout(){
		$sessionid = ForceCookieFrom(COOKIE_ADMIN);

		if($sessionid AND IsAlnum($sessionid)){
			$this->db->exe("DELETE FROM " . TABLE_PREFIX . "sessions WHERE sessionid = '$sessionid'"); //后台用户退出时删除当前的session
		}

		$this->db->exe("DELETE FROM " . TABLE_PREFIX . "vvc WHERE created < " . (time() - 3600*8)); //删除8小时前的验证码
		$this->db->exe("DELETE FROM " . TABLE_PREFIX . "sessions WHERE created < " . (time() - 3600*24*30)); //删除30天前的session

		setcookie(COOKIE_ADMIN, '', 0, '/'); //清除cookie
		Redirect(); //退出后跳转到后台首页
	} 

	/**
	 * public 操作权限验证函数 CheckAccess 无输出
	 */
	public function CheckAccess($action = '') {

		/*
		if($this->admin['actions'] == 'all') return true; //全权限系统管理员

		if ($action AND strstr($this->admin['actions'], '*' . $action . '*')) {
			return true;
		}else{
			return false;
		}
		*/
	}

	/**
	 * public 操作授权验证输出并输出错误信息 CheckAction
	 */
	public function CheckAction($action = '') {

		/*
		if(!$this->CheckAccess($action)){
			Error('您没有进行本次操作的权限!', $errortitle = '权限错误');
		}
		*/
	}
}


?>