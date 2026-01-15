{**
 * templates/frontend/pages/search.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to search and view search results.
 *
 * @uses $results array List of search results
 * @uses $query string The search query, if one was just made
 * @uses $orderBy string Order option
 * @uses $orderDir string When set, either 'asc' or 'desc'
 *}
{include file="frontend/components/header.tpl" pageTitle="common.search"}

<div class="page page_search">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs.tpl" type="category" currentTitleKey="common.search"}
	<h1>{translate key="common.search"}</h1>

	{* No query - this may happen because of a screen reader, so don't show an
	   error, just leave them with the search form *}
	{if $query == '' }

	{* No published titles *}
	{elseif $results->count() == 0}
		<div class="search_results" role="status">
			{translate key="catalog.noTitlesSearch" searchQuery=$query|escape}
			<a href="#search-form">
				{translate key="search.searchAgain"}
			</a>
		</div>

	{* Monograph List *}
	{else}
		<div class="monograph_count">
			{translate key="catalog.browseTitles" numTitles=$results->total()}
		</div>

		<div class="search_results" role="status">
			{if $results->count() > 1}
				{translate key="catalog.foundTitlesSearch" searchQuery=$query|escape number=$results->total()}
			{else}
				{translate key="catalog.foundTitleSearch" searchQuery=$query|escape}
			{/if}
			<a href="#search-form">
				{translate key="search.searchAgain"}
			</a>
		</div>
		<div class="cmp_monographs_list">
			{assign var=counter value=1}
			{foreach from=$results item=result}
				{if $counter is odd by 1}
					<div class="row">
				{/if}
					{include file="frontend/objects/monograph_summary.tpl" monograph=$result.submission press=$result.context heading="h2"}
				{if $counter is even by 1}
					</div>
				{/if}
				{assign var=counter value=$counter+1}
			{/foreach}
			{* Close .row if we have an odd number of titles *}
			{if $counter > 1 && $counter is even by 1}
				</div>
			{/if}
		</div>
		<div class="cmp_pagination">
			{page_info iterator=$results}
			{page_links anchor="results" iterator=$results name="search" query=$query searchContext=$searchContext authors=$authors dateFromMonth=$dateFromMonth dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateToMonth=$dateToMonth dateToDay=$dateToDay dateToYear=$dateToYear orderBy=$orderBy orderDir=$orderDir}
		</div>
	{/if}

	<a name="search-form"></a>
	{include file="frontend/components/searchForm_simple.tpl"}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
