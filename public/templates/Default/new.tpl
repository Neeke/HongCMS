{include header.tpl}

<!-- 新闻列表页 start -->
<div id="news" class="w">
	<!-- 新闻内容 start -->
	<div class="n_left">
	{if $errorinfo}
		<!-- 输出错误信息 start -->
		{PrintInfo($errorinfo)}
		<!-- 输出错误信息 end -->
	{else}
		<div class="title"><span class=grey>{$langs.title}:</span>&nbsp;{$news.title}</div>
		<div class="info grey">
			<span>{$langs.clicks}: {$news.clicks}</span>
			<span>{$langs.date}: {echo DisplayDate($news.created)}</span>
		</div>
		<div class="content">{echo html($news.content)}</div>
		<div class="page">
			<div class="prev">{if $prev_news.n_id}<span>{$langs.prevnews}:</span><a href="{PURL('news?id=' . $prev_news.n_id)}" {if $prev_news.linkurl} target="_blank"{/if} title="{$prev_news.title}">{$prev_news.title}</a>{/if}</div>
			<div class="next">{if $next_news.n_id}<span>{$langs.nextnews}:</span><a href="{PURL('news?id=' . $next_news.n_id)}" {if $next_news.linkurl} target="_blank"{/if} title="{$next_news.title}">{$next_news.title}</a>{/if}</div>
		</div>
	{/if}
	</div>
	<!-- 新闻内容 end -->

	<!-- 产品展示 start -->
	<div class="n_right">
		<div class="divtop"><span class="span_r"><a href="{PURL('products')}">{$langs.more}</a></span><span class=icon></span><a href="{PURL('products')}">{$langs.randproduct}</a></div>
		<div class="pros">
		{foreach $products AS $product}
			<div class="thumb-sml sw">
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
	<!-- 产品展示 end -->
</div>
<!-- 新闻列表页 end -->

{include footer.tpl}
