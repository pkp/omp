{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph list in the catalog.
 *}
<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#monographListContainer').pkpHandler(
			'$.pkp.pages.catalog.MonographPublicListHandler'
		);
	{rdelim});
</script>
<div class="pkp_catalog_monographs" id="monographListContainer">
	{if $monographListTitleKey}
		{translate|assign:"monographListTitle" key=$monographListTitleKey}
	{else}
		{translate|assign:"monographListTitle" key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	{/if}
	<h2><em>{$monographListTitle}</em></h2>
	{if $publishedMonographs|@count}
		<ul class="pkp_helpers_clear">
		{foreach from=$publishedMonographs item=publishedMonograph}
			{include file="catalog/monograph.tpl" publishedMonograph=$publishedMonograph}
		{/foreach}
		</ul>
	{/if}
</div>
