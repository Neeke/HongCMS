{include header.tpl}

<!-- 关于我们等企业内容页 start -->
<div id="info" class="w">
	<div class="i_left">
		<!-- 常态内容导航 start -->
		<div class="i_menu">
			<a href="{PURL('about')}" {if $id==1}class="on"{/if}>{$langs.aboutus}</a>
			<a href="{PURL('about?id=2')}" {if $id==2}class="on"{/if}>{$langs.contactus}</a>
			<a href="{PURL('about?id=14')}" {if $id==14}class="on"{/if}>{$langs.culture}</a>
			<a href="{PURL('about?id=15')}" {if $id==15}class="on"{/if}>{$langs.organization}</a>
			<a href="{PURL('about?id=11')}" {if $id==11}class="on"{/if}>{$langs.company1}</a>
			<a href="{PURL('about?id=12')}" {if $id==12}class="on"{/if}>{$langs.company2}</a>
			<a href="{PURL('about?id=13')}" {if $id==13}class="on"{/if}>{$langs.company3}</a>
		<!-- 常态内容导航 end -->
		</div>

		<!-- 产品展示 start -->
		<div class="i_pros">
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

	<!-- 常态内容 start -->
	<div class="i_right">
	{if $errorinfo}
		<!-- 输出错误信息 start -->
		{PrintInfo($errorinfo)}
		<!-- 输出错误信息 end -->
	{else}
		<div class="title">{$content.title}</div>
		<div class="content">{$content.content}</div>
	{/if}
	</div>
	<!-- 常态内容 end -->
</div>
<!-- 关于我们等企业内容页 end -->

{include footer.tpl}
