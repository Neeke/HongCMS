{include header.tpl}

<!-- 产品列表页 start -->
<div class="w prolist_main">
	<!-- 产品列表 start -->
	<div class="list">
		<!-- 输出错误信息 start -->
		{PrintInfo($errorinfo)}
		<!-- 输出错误信息 end -->

		{foreach $products AS $product}
		<div class="thumb-lrg bg1">
			<table>
			<thead class="thumbnail_hover">
			<tr>
				<th><a href="{PURL('products?id=' . $product.pro_id)}" title="{$product.title}" target="_blank"><img original="{PrintImageURL($product.path, $product.filename, 2)}" width="160" class="thumbnail bg2" alt="{$product.title}"></a></th>
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
	<!-- 产品列表 end -->

	<!-- 分页 start -->
	{if $pagelist}<div id="pagelist">{$pagelist}</div>{/if}
	<!-- 分页 end -->
</div>
<!-- 产品列表页 end -->

{include footer.tpl}
