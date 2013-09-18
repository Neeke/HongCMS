<div class="w footer">
	<div id="foot">
	    <div>
		<p id="footeradv">{$langs.footer_ad}</p>
		<p id="message">
			<a href="{PURL()}">{$langs.home}</a><em>|</em>
			<a href="{PURL('news')}">{$langs.news}</a><em>|</em>
			<a href="{PURL('products')}">{$langs.products}</a><em>|</em>
			<a href="{PURL('about')}">{$langs.aboutus}</a><em>|</em>
			<a href="{PURL('about?id=2')}">{$langs.contactus}</a>
			{if $sitebeian}
				<em>|</em>
				<a href="http://www.miibeian.gov.cn/" target="_blank">{$sitebeian}</a>
			{/if}
		</p>
		<p id="copy">&copy; {echo date("Y")} <a href="{PURL()}">{$sitename}</a> {Debug()}</p>
	    </div>
	</div>
</div>
</body>
</html>
