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
 * @uses $searchQuery string The search query, if one was just made
 *}
{include file="frontend/components/header.tpl" pageTitle="common.search"}

<div class="page page_search">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs.tpl" type="category" currentTitleKey="common.search"}
	<h1>{translate key="common.search"}</h1>
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$results->getCount()}
	</div>

	{* No query - this may happen because of a screen reader, so don't show an
	   error, just leave them with the search form *}
	{if $searchQuery == '' }

	{* No published titles *}
	{elseif $results->getCount() == 0}
		<div class="search_results" role="status">
			{translate key="catalog.noTitlesSearch" searchQuery=$searchQuery|escape}
			<a href="#search-form">
				{translate key="search.searchAgain"}
			</a>
		</div>

	{* Monograph List *}
	{else}
		<div class="search_results" role="status">
			{if $results->getCount() > 1}
				{translate key="catalog.foundTitlesSearch" searchQuery=$searchQuery|escape number=$results->getCount()}
			{else}
				{translate key="catalog.foundTitleSearch" searchQuery=$searchQuery|escape}
			{/if}
			<a href="#search-form">
				{translate key="search.searchAgain"}
			</a>
		</div>
		<div class="cmp_monographs_list">
			{assign var=counter value=1}
			{iterate from=results item=result}
				{if $counter is odd by 1}
					<div class="row">
				{/if}
					{include file="frontend/objects/monograph_summary.tpl" monograph=$result.publishedSubmission press=$result.press heading="h2" authorUserGroups=$authorUserGroups}
				{if $counter is even by 1}
					</div>
				{/if}
				{assign var=counter value=$counter+1}
			{/iterate}
			{* Close .row if we have an odd number of titles *}
			{if $counter > 1 && $counter is even by 1}
				</div>
			{/if}
		</div>
		<div class="cmp_pagination">
			{page_info iterator=$results}
			{page_links anchor="results" iterator=$results name="search" query=$searchQuery}
		</div>
	{/if}

	<a name="search-form"></a>
	{include file="frontend/components/searchForm_simple.tpl"}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
