{include header.tpl}

<!-- 产品详情页 start -->
<a name="g"></a><!-- 锚点 -->
<div id="pro" class="w pro_main">
{if $errorinfo}
	<!-- 输出错误信息 start -->
	{PrintInfo($errorinfo)}
	<!-- 输出错误信息 end -->
{else}
	<!-- 产品图片及上一个和下一个 start -->
	<div class="image">
	<img src="{PrintImageURL($product.path, $product.filename, 3)}" width="900" height="900" align="absmiddle" class="bigimage" id="big_image" title="{$product.title}" alt="{$product.title}">
	</div>
	<!-- 产品图片及上一个和下一个 end -->

	<!-- 组图片 start -->
	{if $gimages}
	<div class="gimage">
		<div class="lev_rollLeft"></div>
		<div class="lev_brandList">
			<div class="lev_brandListC" id="groupimages">
			{$gimages}
			</div>
		</div>

		<div class="lev_rollRight"></div>

		<script type="text/javascript">
		$(function() {
			$("#groupimages").ImageMove({
				prevId: ".lev_rollLeft",
				nextId: ".lev_rollRight",
				offbtnleft: "off_lev_rollLeft",
				offbtnright: "off_lev_rollRight"
			})

			$("#groupimages img").hover(function(){
				$(this).addClass("hover");
			},function(){
				$(this).removeClass("hover");
			}).click(function(){
				if(!$(this).attr("now")){
					$("#big_image").removeAttr("src").addClass("loading2").attr("src", $(this).attr("src").replace(/_s/ig, "_l"));

					$("#groupimages img[now=\"1\"]").removeClass("now").removeAttr("now");
					$(this).attr("now", "1").addClass("now");
					window.location.hash  = '#g';
				}
			});
		});
		</script>
	</div>
	{/if}
	<!-- 组图片 end -->

	<!-- 产品介绍 start -->
	<div class="disc">
		<div class="title"><span class=grey>{$langs.protitle}:</span>&nbsp;{$product.title}</div>
		<div class="date grey">
			{if $product.price}<span>{$langs.price}: {$product.price}</span>{/if}
			<span>{$langs.clicks}: {$product.clicks}</span>
			<span>{$langs.editor}: {$product.username}</span>
			<span>{$langs.date}: {echo DisplayDate($product.created)}</span>
		</div>
		<div class="content">{echo html($product.content)}</div>
	</div>
	<!-- 产品介绍 end -->
{/if}
</div>
<!-- 产品详情页 end -->

{include footer.tpl}
