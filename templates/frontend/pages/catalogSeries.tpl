{**
 * templates/frontend/pages/catalogSeries.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view books in a series in the catalog.
 *
 * @uses $series Series Current series being viewed
 * @uses $publishedSubmissions array List of published submissions in this series
 * @uses $featuredMonographIds array List of featured monograph IDs in this series
 * @uses $newReleasesMonographs array List of new monographs in this series
 * @uses $prevPage int The previous page number
 * @uses $nextPage int The next page number
 * @uses $showingStart int The number of the first item on this page
 * @uses $showingEnd int The number of the last item on this page
 * @uses $total int Count of all published submissions in this series
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$series->getLocalizedTitle()|escape}

<div class="page page_catalog_series">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs_catalog.tpl" type="series" currentTitle=$series->getLocalizedTitle()}
	<h1>{$series->getLocalizedTitle()|escape}</h1>

	{* Count of monographs in this series *}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$total}
	</div>

	{* Image and description *}
	{assign var="image" value=$series->getImage()}
	{assign var="description" value=$series->getLocalizedDescription()|strip_unsafe_html}
	<div class="about_section{if $image} has_image{/if}{if $description} has_description{/if}">
		{if $image}
			<div class="cover" href="{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="fullSize" type="series" id=$series->getId()}">
				<img src="{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$series->getId()}" alt="{$series->getLocalizedTitle()|escape|default: 'null'}" />
			</div>
		{/if}
		<div class="description">
			{$description|strip_unsafe_html}
		</div>
		{if $series->getOnlineISSN()}
			<div class="onlineISSN">
				{translate key="catalog.manage.series.onlineIssn"} {$series->getOnlineISSN()|escape}
			</div>
		{/if}
		{if $series->getPrintISSN()}
			<div class="printISSN">
				{translate key="catalog.manage.series.printIssn"} {$series->getPrintISSN()|escape}
			</div>
		{/if}
	</div>

	{* No published titles in this category *}
	{if empty($publishedSubmissions)}
		<h2>
			{translate key="category.heading"}
		</h2>
		<p>{translate key="catalog.noTitles"}</p>

	{else}

		{* New releases *}
		{if !empty($newReleasesMonographs)}
			{include file="frontend/components/monographList.tpl" monographs=$newReleasesMonographs titleKey="catalog.newReleases" authorUserGroups=$authorUserGroups}
		{/if}

		{* All monographs *}
		{include file="frontend/components/monographList.tpl" monographs=$publishedSubmissions featured=$featuredMonographIds titleKey="category.heading" authorUserGroups=$authorUserGroups}

		{* Pagination *}
		{if $prevPage > 1}
			{capture assign=prevUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="series" path=$series->getPath()|to_array:$prevPage}{/capture}
		{elseif $prevPage === 1}
			{capture assign=prevUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="series" path=$series->getPath()}{/capture}
		{/if}
		{if $nextPage}
			{capture assign=nextUrl}{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="series" path=$series->getPath()|to_array:$nextPage}{/capture}
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
