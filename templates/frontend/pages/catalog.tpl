{**
 * templates/frontend/pages/catalog.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the catalog.
 *
 * @uses $publishedSubmissions array List of published submissions
 * @uses $contextSeries array List of context series
 * @uses $prevPage int The previous page number
 * @uses $nextPage int The next page number
 * @uses $showingStart int The number of the first item on this page
 * @uses $showingEnd int The number of the last item on this page
 * @uses $total int Count of all published submissions
 *}
{include file="frontend/components/header.tpl" pageTitle="navigation.catalog"}

<div class="page page_catalog">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="navigation.catalog"}
	<h1>{translate key="navigation.catalog"}</h1>

	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$total}
	</div>

	{* Series List *}
	{if $activeTheme && $activeTheme->getOption('showCatalogSeriesListing') && $contextSeries|@count > 1}
		<nav class="pkp_series_nav_menu" role="navigation" aria-label="{translate key="series.series"}">
			<h2>{translate key="series.series"}:</h2>
			<ul>
				{foreach name="seriesListLoop" from=$contextSeries item=series}
					<li class="series_{$series->getId()}">
						<a href="{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="series" path=$series->getPath()|escape}">{$series->getLocalizedTitle()|escape}</a>{if !$series@last}<span aria-hidden="true">{translate key="common.commaListSeparator"}</span>{/if}
					</li>
				{/foreach}
			</ul>
		</nav>
	{/if}

	{* No published titles *}
	{if !$publishedSubmissions|@count}
		<h2>
			{translate key="catalog.category.heading"}
		</h2>
		<p>{translate key="catalog.noTitles"}</p>

	{* Monograph List *}
	{else}
		{include file="frontend/components/monographList.tpl" monographs=$publishedSubmissions featured=$featuredMonographIds authorUserGroups=$authorUserGroups}

		{* Pagination *}
		{if $prevPage > 1}
			{capture assign=prevUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="page" path=$prevPage}{/capture}
		{elseif $prevPage === 1}
			{capture assign=prevUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog"}{/capture}
		{/if}
		{if $nextPage}
			{capture assign=nextUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="page" path=$nextPage}{/capture}
		{/if}
		{include
			file="frontend/components/pagination.tpl"
			prevUrl=$prevUrl
			nextUrl=$nextUrl
			showingStart=$showingStart
			showingEnd=$showingEnd
			total=$total
		}
	{/if}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
