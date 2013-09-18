{include header.tpl}

<!-- 首页顶部 start -->
<div class="w index_top">
	<!-- 顶部左侧slide图片广告 start -->
	<div id="slide_box" class="slide_box" style="width:690px;height:280px;margin:0;padding:0;">
		<div class="slide_img">
			<a href="" target="_blank"><img src="" style="border:0;"></a>
		</div>
		<div class="slide-controls">
			<span href="{PURL()}" pic="{$t_url}images/adv_1.jpg">1</span>
			<span href="{PURL()}" pic="{$t_url}images/adv_2.jpg">2</span>
			<span href="{PURL()}" pic="{$t_url}images/adv_3.jpg">3</span>
		</div>
	</div>
	<script type='text/javascript'>
		$(function(){
			var slideObj=new slide_im("#slide_box", 5000);
			slideObj.init();
		});
	</script>
	<!-- 顶部左侧slide图片广告 end -->

	<!-- 顶部右侧新闻公告 start -->
	<div id="index_news">
		<div class="divtop"><span class="span_r"><a href="{PURL('news')}">{$langs.more}</a></span><span class=icon></span><a href="{PURL('news')}">{$langs.latestnews}</a></div>
		<div class="news">
			<ul>
			{foreach $news AS $new}
				<li><a href="{if $new.linkurl}{$new.linkurl}{else}{PURL('news?id=' . $new.n_id)}{/if}" target="_blank">{$new.title}</a> <span class=grey>({echo DisplayDate($new.created)})</span></li>
			{/foreach}
			</ul>
		</div>
	</div>
	<!-- 顶部右侧新闻公告 end -->
</div>
<!-- 首页顶部 end -->

<!-- 首页中部 start -->
<div class="w index_main">
	<!-- 中部左侧企业介绍 start -->

	<!-- 获取首页加载的常态内容, 并分配给变量 -->
	{$homecontent = GetContent(3)}
	<div class="m_left">{$homecontent.content}</div>
	<!-- 中部左侧企业介绍 end -->

	<!-- 中部右侧最新产品 start -->
	<div id="new_pros">
		<div class="divtop"><span class="span_r"><a href="{PURL('products')}">{$langs.more}</a></span><span class=icon></span><a href="{PURL('products')}">{$langs.latestpros}</a></div>
		<div class="pros">
		{foreach $newproducts AS $product}
			<div class="thumb-sml">
			<table>
			<thead class="thumbnail_hover">
			<tr>
			<th><a href="{PURL('products?id=' . $product.pro_id)}" title="{$product.title}" target="_blank"><img original="{PrintImageURL($product.path, $product.filename)}" width="80" class="thumbnail" alt="{$product.title}" onMouseMove="ShowBigImage();"></a></th>
			</tr>
			</thead>
			</table>
			</div>
		{/foreach}
		</div>
	</div>
	<!-- 中部右侧最新产品 end -->
</div>
<!-- 首页中部 end -->

<!-- 底部推荐产品 start -->
<div class="w index_bottom">
	<div class="bottom">
		<div class="title"><span class="span_r"><a href="{PURL('products')}">{$langs.more}</a></span><span class=icon></span><a href="{PURL('products')}">{$langs.reproducts}</a></div>
		<div class="repros">
		{foreach $recommends AS $product}
			<div class="thumb-lrg">
				<table>
				<thead class="thumbnail_hover">
				<tr>
					<th><a href="{PURL('products?id=' . $product.pro_id)}" title="{$product.title}" target="_blank"><img original="{PrintImageURL($product.path, $product.filename, 2)}" width="160" class="thumbnail" alt="{$product.title}"></a></th>
				</tr>
				</thead>
				<tr>
					<td>
						<div><a href="{PURL('products?id=' . $product.pro_id)}" title="{$product.title}" target="_blank">{$product.title}</a></div>
					</td>
				</tr>
				</table>
			</div>
		{/foreach}
		</div>
	</div>
</div>
<!-- 底部推荐产品 end -->

{include footer.tpl}
