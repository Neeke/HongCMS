{include header.tpl}

<!-- 新闻列表页 start -->
<div id="news" class="w">
	<!-- 新闻列表 start -->
	<div class="n_left">
		<div class="title">{$langs.news}</div>
		<div class="list">
			<!-- 输出错误信息 start -->
			{PrintInfo($errorinfo)}
			<!-- 输出错误信息 end -->

			<ul>
			{$num = 1}
			{foreach $news AS $new}
				<li class="{if is_int($num/2)}bg{/if}"><span class="ntitle"><span class="num">{echo $start + $num}.</span><a href="{if $new.linkurl}{$new.linkurl}{else}{PURL('news?id=' . $new.n_id)}{/if}" target="_blank">{$new.title}</a></span><span class="date">{echo DisplayDate($new.created)}</span></li>
				{$num += 1}
			{/foreach}
			</ul>
		</div>

		<!-- 分页 start -->
		{if $pagelist}<div id="pagelist">{$pagelist}</div>{/if}
		<!-- 分页 end -->
	</div>
	<!-- 新闻列表 end -->

	<!-- 最新产品 start -->
	<div class="n_right">
		<div class="divtop"><span class="span_r"><a href="{PURL('products')}">{$langs.more}</a></span><span class=icon></span><a href="{PURL('products')}">{$langs.latestpros}</a></div>
		<div class="pros">
		{foreach $newproducts AS $product}
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
	<!-- 最新产品 end -->
</div>
<!-- 新闻列表页 end -->

{include footer.tpl}
