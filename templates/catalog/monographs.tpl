{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph list in the catalog.
 *}
<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#monographListContainer').pkpHandler(
			'$.pkp.pages.catalog.MonographListHandler'
		);
	{rdelim});
</script>
<div class="pkp_catalog_monographs" id="monographListContainer">
	<h3>{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}</h3>

	<ul class="pkp_helpers_clear">
	{foreach from=$publishedMonographs item=publishedMonograph}
		{include file="catalog/monograph.tpl" publishedMonograph=$publishedMonograph}
	{/foreach}
	</ul>
</div>
