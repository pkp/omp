{**
 * templates/frontend/pages/searchResults.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the catalog.
 *
 * @uses $publishedMonographs array List of published monographs
 * @uses $searchQuery string The search query, if one was just made
 *}
{include file="common/frontend/header.tpl" pageTitle="search.searchResults"}

<div class="page page_catalog">
	<h1 class="page_title">
		{translate key="search.searchResults"}
	</h1>
	<h2 class="page_subtitle">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</h2>

	{* No published titles *}
	{if !$publishedMonographs|@count}
		<p>{translate key="catalog.noTitlesSearch" searchQuery=$searchQuery}</p>

	{* Monograph List *}
	{else}
		<ul class="cmp_monographs_list">
			{foreach from=$publishedMonographs item=monograph}
				<li>
					{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
				</li>
			{/foreach}
		</ul>
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
