<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{$title}</title>
<meta name="description" content="{$description}">
<meta name="Keywords" content="{$keywords}">

<link rel="stylesheet" href="{$t_url}css/styles.css" type="text/css">
<link rel="stylesheet" href="{$t_url}css/menu.css" type="text/css">
{if !IS_CHINESE}
<link rel="stylesheet" href="{$t_url}css/english.css" type="text/css"><!-- 英文页面兼容CSS -->
{/if}
<script type="text/javascript">
siteConfig={
	siteurl:"{$baseurl}",
	sitename:"{$sitename}",
	scrolltop:"{$langs.backtotop}"
};
</script>

<script type="text/javascript" src="{$public}js/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="{$public}js/jquery.addon.js"></script>
<script type="text/javascript" src="{$public}js/common.js"></script>
</head>
<body>

<!-- 顶部导航栏 start -->
<div id="shortcut">
	<div class="w">
		<ul class="fl lh">
			{if IS_CHINESE}
				<li class="fore1"><div class="cn_on" title="{$langs.chinese}"></div></li>
				<li><div class="en" title="{$langs.change_lan}"></div></li>
			{else}
				<li class="fore1"><div class="cn" title="{$langs.change_lan}"></div></li>
				<li><div class="en_on" title="{$langs.english}"></div></li>
			{/if}
		</ul>
		<ul class="fr lh">
			<li class="fore1 ld"><b></b><a href="javascript:addToFavorite()">{$langs.addfavorite}</a></li>
			<li><a href="{PURL('about')}">{$langs.aboutus}</a></li>
			<li><a href="{PURL('about?id=2')}">{$langs.contactus}</a></li>
			<li class="menu">
				<dl>
					<dt class="ld">{$langs.services}<b></b></dt>
					<dd>
						<div><a href="{PURL('about?id=14')}">{$langs.culture}</a></div>
						<div><a href="{PURL('about?id=15')}">{$langs.organization}</a></div>
					</dd>
				</dl>
			</li>
			<li class="menu w1">
				<dl class="w2">
					<dt class="ld">{$langs.companys}<b></b></a></dt>
					<dd>
						<div><a href="{PURL('about?id=11')}">{$langs.company1}</a></div>
						<div><a href="{PURL('about?id=12')}">{$langs.company2}</a></div>
						<div><a href="{PURL('about?id=13')}">{$langs.company3}</a></div>
					</dd>
				</dl>
			</li>
		</ul>
		<span class="clr"></span>
	</div>
</div>
<!-- 顶部导航栏 end -->

<!-- Logo栏 start -->
<div id="header" class="w">
	<div id="logo"><a href="{PURL()}" hidefocus="true" title="{$sitename}"><img src="{$t_url}images/logo.png" width="260" height="80" alt="{$sitename}"></a></div>
	<div id="top_adv"><img src="{$t_url}images/top_adv{echo rand(1, 6)}.png"></div>
</div>
<!-- Logo栏 end -->

<!-- 菜单栏 start -->
<div class="w topmenu">
	<div id="menu">
		<ul class="sf-menu">
			<li><a href="{PURL()}" hidefocus="true" {if $menu=='home'}class="on"{/if}>{$langs.home}</a></li>
			<li><a href="{PURL('news')}" hidefocus="true" {if $menu=='news'}class="on"{/if}>{$langs.news}</a></li>
			<li>
				<a href="{PURL('products')}" hidefocus="true" {if $menu=='products'}class="on"{/if}>{$langs.products}</a>
				{$pcategories}
			</li>
			<li><a href="{PURL('about')}" hidefocus="true" {if $menu=='about'}class="on"{/if}>{$langs.aboutus}</a>
				<ul>
					<li><a href="{PURL('about?id=14')}">{$langs.culture}</a></li>
					<li><a href="{PURL('about?id=15')}">{$langs.organization}</a></li>
					<li><a href="{PURL('about?id=11')}">{$langs.company1}</a></li>
					<li><a href="{PURL('about?id=12')}">{$langs.company2}</a></li>
					<li><a href="{PURL('about?id=13')}">{$langs.company3}</a></li>
				</ul>
			</li>
			<li><a href="{PURL('about?id=2')}" hidefocus="true" {if $menu=='contact'}class="on"{/if}>{$langs.contactus}</a></li>
		</ul>


	</div>
	<div id="searchform">
		<form action="{PURL('products')}" method="post" id="search_form" onSubmit="return CheckSpace('searchkey', '{$langs.search_err}');">
			<span class=searchicon></span>
			<input type="text" name="s" id="searchkey" value="{$keyword}">&nbsp;
			<input type="submit" class="submit" id="search_submit" name="searchbtn" value="{$langs.search}" hidefocus="true">
		</form>
	</div>
</div>
<!-- 菜单栏 end -->

<!-- 当前位置导航栏 start -->
{if $pagenav}
<div class="w">
	<div class="nav">
		<div id="pagenav"><span class=navicon></span>{$langs.yourarehere}&nbsp;&nbsp;{$pagenav}</div>
	</div>
</div>
{/if}
<!-- 当前位置导航栏 end -->

<script type="text/javascript">
(function() {
	//固定顶部Div不随页面滚动
	js_scrolly({
		id:'shortcut', l:0, t:0, f:1
	});

	$("#shortcut .menu").Jdropdown({
		delay: 50
	});

	//加载scrolltop
	scrolltotop.init();

	//切换语言动作
	$("#shortcut .cn").click(function(){
		setCookie('{echo COOKIE_KEY}lang', 'Chinese', 30);
		document.location=window.location.href.replace(/#[\w]*/ig, '');
	});
	$("#shortcut .en").click(function(){
		setCookie('{echo COOKIE_KEY}lang', 'English', 30);
		document.location=window.location.href.replace(/#[\w]*/ig, '');
	});

	//JQuery多级菜单
	$("ul.sf-menu").superfish();

	//搜索关键词变化
	var searchkey_obj =$("#searchkey");
	var keyword = searchkey_obj.val();
	searchkey_obj.bind("focus",function(){
		if (this.value==keyword){
			this.value="";
			this.style.color="#333";
			this.style.background="#FFF";
			this.style.borderColor="#CC3300";
		}
	}).bind("blur",function(){
		if (!this.value){
			this.value=keyword;
			this.style.color="#999";
			this.style.background="#d8d8d8";
			this.style.borderColor="#3C3C3C";
		}
	});
})();
</script>