{**
 * templates/frontend/pages/catalog.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the catalog.
 *
 * @uses $publishedMonographs array List of published monographs
 * @uses $featuredMonographIds array List of featured monograph ids
 * @uses $itemsPerPage int Number of items to show per page
 * @uses $page int Current page being displayed
 * @uses $pageCount int Total number of pages available
 * @uses $nextPage int The next page, if one exists
 * @uses $prevPage int The previous page, if one exists
 *}
{include file="frontend/components/header.tpl" pageTitle="navigation.catalog"}

<div class="page page_catalog">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="navigation.catalog"}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* No published titles *}
	{if !$publishedMonographs|@count}
		<h2>
			{translate key="catalog.allBooks"}
		</h2>
		<p>{translate key="catalog.noTitles"}</p>

	{* Monograph List *}
	{else}
		{include file="frontend/components/monographList.tpl" monographs=$publishedMonographs featured=$featuredMonographIds}

		{if $prevPage || $nextPage}
			<div class="cmp_pagination" aria-label="{translate|escape key="catalog.pagination.label"}">
				{if $prevPage}
					{url|assign:"prevUrl" router=$smarty.const.ROUTE_PAGE page="catalog" op="page" path=$prevPage}
					{if $prevPage === 1}
						{url|assign:"prevUrl" router=$smarty.const.ROUTE_PAGE page="catalog"}
					{/if}
					<a class="prev" href="{$prevUrl}">
						{translate key="catalog.pagination.previous"}
					</a>
				{/if}
				<span class="current">
					{translate key="catalog.pagination" first=$page last=$pageCount}
				</span>
				{if $nextPage}
					<a class="next" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="page" path=$nextPage}">
						{translate key="catalog.pagination.next"}
					</a>
				{/if}
			</div>
		{/if}
	{/if}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
