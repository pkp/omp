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

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs.tpl" type="category" currentTitleKey="search.searchResults"}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* No published titles *}
	{if !$publishedMonographs|@count}
		<div>{translate key="catalog.noTitlesSearch" searchQuery=$searchQuery}</div>

	{* Monograph List *}
	{else}
		<div>
			{if $publishedMonographs|@count > 1}
				{translate key="catalog.foundTitlesSearch" searchQuery=$searchQuery number=$publishedMonographs|@count}
			{else}
				{translate key="catalog.foundTitleSearch" searchQuery=$searchQuery}
			{/if}
		</div>
		{include file="frontend/components/monographList.tpl" monographs=$publishedMonographs}
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
