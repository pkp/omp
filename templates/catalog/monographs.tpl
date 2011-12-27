{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph list in the catalog.
 *}

<div class="pkp_catalog_monographs">
	<h3>{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}</h3>

	<ul>
	{foreach from=$publishedMonographs item=publishedMonograph}
		{include file="catalog/monograph.tpl" publishedMonograph=$publishedMonograph}
	{/foreach}
	</ul>
</div>
