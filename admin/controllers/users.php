<?php if(!defined('ROOT')) die('Access denied.');

class c_users extends SAdmin{

	public function __construct($path){
		parent::__construct($path);

	}

	//保存
	public function save(){
		$userid          = ForceIntFrom('userid');
		$username        = ForceStringFrom('username');
		$password        = ForceStringFrom('password');
		$passwordconfirm = ForceStringFrom('passwordconfirm');
		$activated       = ForceIntFrom('activated');
		$nickname        = ForceStringFrom('nickname');
		if(!$nickname) $nickname = $username;

		$deleteuser       = ForceIntFrom('deleteuser');
		$deleteuserpublish       = ForceIntFrom('deleteuserpublish');

		if($deleteuserpublish){

			//管理员用户不需要此功能
		}

		if($deleteuser){//删除用户
			if($userid != $this->admin->data['userid']){
				$this->db->exe("DELETE FROM " . TABLE_PREFIX . "admin WHERE userid = '$userid' ");

				//CMS需要时, 删除好友、头像等
			}

			Success('users');
		}

		if(strlen($username) == 0){
			$errors[] = '请输入用户名!';
		}elseif(!IsName($username)){
			$errors[] = '用户名存在非法字符!';
		}elseif($this->db->getOne("SELECT userid FROM " . TABLE_PREFIX . "admin WHERE username = '$username' AND userid != '$userid'")){
			$errors[] = '用户名已存在!';
		}

		if($userid){
			if(strlen($password) OR strlen($passwordconfirm)){
				if(strcmp($password, $passwordconfirm)){
					$errors[] = '两次输入的密码不相同!';
				}
			}
		}else{
			if(strlen($password) == 0){
				$errors[] = '请输入密码!';
			}elseif($password != $passwordconfirm){
				$errors[] = '两次输入的密码不相同!';
			}
		}

		if(isset($errors)){
			Error($errors, Iif($userid, '编辑用户错误', '添加用户错误'));
		}else{
			if($userid){
				$this->db->exe("UPDATE " . TABLE_PREFIX . "admin SET username    = '$username',
				".Iif($userid != $this->admin->data['userid'], "activated = '$activated',")."
				".Iif($password, "password = '" . md5($password) . "',")."
				nickname       = '$nickname'
				WHERE userid      = '$userid'");

			}else{
				$this->db->exe("INSERT INTO " . TABLE_PREFIX . "admin (activated, username, password, joindate, joinip, nickname) VALUES (1, '$username', '".md5($password)."',  '".time()."', '".GetIP()."', '$nickname')");

			}

			Success('users');
		}
	}

	//批量更新用户
	public function updateusers(){
		if(IsPost('updateusers')){
			$userids   = $_POST['updateuserids'];
			$activateds   = $_POST['activateds'];

			for($i = 0; $i < count($userids); $i++){
				if($userids[$i] != $this->admin->data['userid']){
					$this->db->exe("UPDATE " . TABLE_PREFIX . "admin SET activated = '".ForceInt($activateds[$i])."' WHERE userid = '".ForceInt($userids[$i])."'");
				}
			}

		}else{
			$deleteuserids = $_POST['deleteuserids'];

			for($i = 0; $i < count($deleteuserids); $i++){
				$userid = ForceInt($deleteuserids[$i]);
				if($userid != $this->admin->data['userid']){
					$this->db->exe("DELETE FROM " . TABLE_PREFIX . "admin WHERE userid = '$userid'");
				}
			}
		}

		Success('users');
	}

	//编辑调用add
	public function edit(){
		$this->add();
	}

	//添加页面
	public function add(){
		$userid = ForceIntFrom('userid');

		if($userid){
			SubMenu('管理用户', array(array('添加用户', 'users/add'),array('编辑用户', 'users/edit?userid='.$userid, 1),array('用户列表', 'users')));
			
			$user = $this->db->getOne("SELECT * FROM " . TABLE_PREFIX . "admin WHERE userid = '$userid'");
		}else{
			SubMenu('添加用户', array(array('添加用户', 'users/add', 1),array('用户列表', 'users')));

			$user = array('userid' => 0, 'groupid' => 2, 'activated' => 1);
		}

		$need_info = '&nbsp;&nbsp;<font class=red>* 必填项</font>';
		$pass_info = Iif($userid, '&nbsp;&nbsp;<font class=grey>不修改请留空</font>', $need_info);

		echo '<form method="post" action="'.BURL('users/save').'">
		<input type="hidden" name="userid" value="' . $user['userid'] . '" />';

		if($userid){
			TableHeader('编辑用户信息: <span class=note>' . $user['username'] . '</span>');
		}else{
			TableHeader('填写用户信息');
		}

		TableRow(array('<b>用户名:</b>', '<input type="text" name="username" value="'.$user['username'].'" size="20" />' .$need_info));

		TableRow(array('<b>密码:</b>', '<input type="text" name="password" size="20" />'.$pass_info));
		TableRow(array('<b>确认密码:</b>', '<input type="text" name="passwordconfirm" size="20" />'.$pass_info));

		TableRow(array('<b>昵称:</b>', '<input type="text" name="nickname" value="'.$user['nickname'].'" size="20" />'));

		if($userid){
			TableRow(array('<b>是否激活?</b>', '<input type="checkbox" ' . Iif($userid == $this->admin->data['userid'], 'disabled') .' name="activated" value="1" ' . Iif($user['activated'] == 1, ' checked="checked"') .' />'));

			TableRow(array('<b>删除此用户?</b>', '<input type="checkbox" ' . Iif($userid == $this->admin->data['userid'], 'disabled') .' name="deleteuser" value="1" />&nbsp;<font class=redb>慎选!</font> <span class=light>删除此用户.</span>'));

			TableRow(array('<b>删除此用户发表的信息?</b>', '<input type="checkbox" name="deleteuserpublish" value="1" />&nbsp;<font class=redb>慎选!</font> <span class=light>删除此用户发表的所有信息.</span>'));
		}

		TableFooter();

		PrintSubmit(Iif($userid, '保存更新', '添加用户'));
	}

	public function index(){
		$NumPerPage = 20;
		$page = ForceIntFrom('p', 1);
		$letter = ForceStringFrom('key');
		$search = ForceStringFrom('s');

		if(IsGet('s')){
			$search = urldecode($search);
		}

		$start = $NumPerPage * ($page-1);

		if($search OR $letter){
			SubMenu('用户列表', array(array('添加用户', 'users/add'), array('全部用户', 'users')));
		}else{
			SubMenu('用户列表', array(array('添加用户', 'users/add')));
		}

		TableHeader('快速查找用户');
		for($alphabet = 'a'; $alphabet != 'aa'; $alphabet++){
			$alphabetlinks .= '<a href="'.BURL('users?key=' . $alphabet) . '" title="' . strtoupper($alphabet) . '开头的用户">' . strtoupper($alphabet) . '</a> &nbsp;';
		}

		TableRow('<center><b><a href="'.BURL('users').'">[全部用户]</a>&nbsp;&nbsp;&nbsp;' . $alphabetlinks . '&nbsp;<a href="'.BURL('users?key=Validating').'">[未激活]</a>&nbsp;&nbsp;<a href="'.BURL('users?key=Neverlogin').'">[未登陆]</a>&nbsp;&nbsp;<a href="'.BURL('users?key=Other').'">[中文名]</a></b></center>');
		TableFooter();

		echo '<form method="post" action="'.BURL('users').'" name="searchusers">';

		TableHeader('搜索用户');
		TableRow('<center><label>ID, 用户名或昵称:</label>&nbsp;<input type="text" name="s" size="18">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="搜索用户" class="cancel"></center>');
		TableFooter();

		echo '</form>';

		if($letter){
			if($letter == 'Other'){
				$searchsql = " WHERE username NOT REGEXP(\"^[a-zA-Z]\") ";
				$title = '<span class=note>中文用户名</span> 的用户列表';
			}else if($letter == 'Validating'){
				$searchsql = " WHERE activated = 0 ";
				$title = '<span class=note>未激活</span> 的用户列表';
			}else if($letter == 'Neverlogin')	{
				$searchsql = " WHERE lastdate = 0 ";
				$title = '<span class=note>未登陆</span> 的用户列表';
			}else{
				$searchsql = " WHERE username LIKE '$letter%' ";
				$title = '<span class=note>'.strtoupper($letter) . '</span> 字母开头的用户列表';
			}
		}else if($search){
			if(preg_match("/^[1-9][0-9]*$/", $search)){
				$searchsql = " WHERE userid = '".ForceInt($search)."' "; //按ID搜索
				$title = "搜索ID号为: <span class=note>$search</span> 的用户";
			}else{
				$searchsql = " WHERE (username LIKE '%$search%' OR nickname LIKE '%$search%') "; //按ID搜索
				$title = "搜索: <span class=note>$search</span> 的用户列表";
			}
		}else{
			$searchsql = '';
			$title = '全部用户列表';
		}

		$getusers = $this->db->query("SELECT * FROM " . TABLE_PREFIX . "admin ".$searchsql." ORDER BY activated ASC, userid DESC LIMIT $start,$NumPerPage");

		$maxrows = $this->db->getOne("SELECT COUNT(userid) AS value FROM " . TABLE_PREFIX . "admin ".$searchsql);

		echo '<form method="post" action="'.BURL('users/updateusers').'" name="usersform">';

		TableHeader($title.'('.$maxrows['value'].'个)');
		TableRow(array('ID', '用户名',	'昵称', '登录次数', '状态', '注册日期', '注册IP', '最后登陆', '最后IP', '<input type="checkbox" id="checkAll" for="deleteuserids[]"> <label for="checkAll">删除</label>'), 'tr0');

		if($maxrows['value'] < 1){
			TableRow('<center><BR><font class=redb>未搜索到任何用户!</font><BR><BR></center>');
		}else{
			while($user = $this->db->fetch($getusers)){
				TableRow(array($user['userid'], '<input type="hidden" name="updateuserids[]" value="'.$user['userid'].'" /><a href="'.BURL('users/edit?userid='.$user['userid']).'">'.Iif($user['activated'], $user['username'],'<font class=red><s>'.$user['username'].'</s></font>').'</a>',
				'<a href="'.BURL('users/edit?userid='.$user['userid']).'">'.Iif($user['activated'], $user['nickname'],'<font class=red><s>'.$user['nickname'].'</s></font>').'</a>',
				$user['loginnum'],
				'<select name="activateds[]"><option value="1">已激活</option><option style="color:red;" value="0" ' . Iif(!$user['activated'], 'SELECTED', '') . '>未激活</option></select>',
				DisplayDate($user['joindate']),
				$user['joinip'],
				Iif($user['lastdate'] == 0, '<span class="orange">从未登陆</span>', DisplayDate($user['lastdate'])),
				$user['lastip'],
				'<input type="checkbox" name="deleteuserids[]" value="' . $user['userid'] . '" ' . Iif($user['userid'] == $this->admin->data['userid'], 'disabled') .'>'));
			}

			$totalpages = ceil($maxrows['value'] / $NumPerPage);

			if($totalpages > 1){
				TableRow(GetPageList(BURL('users'), $totalpages, $page, 10, 'key', $letter, 's', urlencode($search)));
			}

		}

		TableFooter();

		echo '<div class="submit"><input type="submit" name="updateusers" value="保存更新" class="save"><input type="submit" name="deleteusers" onclick="'.Confirm('确定删除所选用户吗?<br><br><span class=red>注: 这里删除用户, 用户发表的信息不会被删除!</span>', 'form').'" value="删除用户" class="cancel"></div></form>';
	}

} 

?>