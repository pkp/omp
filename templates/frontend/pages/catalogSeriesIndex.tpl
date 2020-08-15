{**
 * templates/frontend/pages/catalogSeries.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view books in a series in the catalog.
 *
 * @uses $series Series Current series being viewed
 * @uses $publishedMonographs array List of published monographs in this series
 * @uses $featuredMonographIds array List of featured monograph IDs in this series
 * @uses $newReleasesMonographs array List of new monographs in this series
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.block.browse.series"}

<div class="page page_catalog_seriesIndex">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs_catalog.tpl" type="series" currentTitleKey="plugins.block.browse.series"}

	<h2>
		{translate key="plugins.block.browse.series"}
	</h2>

	{* Index with series *}
	<div class="seriesIndex">
	<ul>

		{* Series *}
		{if $browseSeriesFactory && $browseSeriesFactory->getCount()}

		{iterate from=browseSeriesFactory item=browseSeriesItem}				
		<li>
		
		<div class="imageDescription">		

	{* Image and description *}

			<div class="cover"> <a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}">
			<img src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$browseSeriesItem->getId()|escape}" alt="{$browseSeriesItem->getLocalizedFullTitle()|escape}" /></a>
			</div>

			<div class="metadata">				
			<h3><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}"> {$browseSeriesItem->getLocalizedFullTitle()|escape}</a></h3>
		
			<div class="description">{$browseSeriesItem->getLocalizedDescription()|strip_unsafe_html|truncate:800}</div>

			{if $browseSeriesItem->getPrintISSN()}
			<div class="printISSN">{translate key="catalog.manage.series.printIssn"} {$browseSeriesItem->getPrintISSN()|escape}</div>{/if}

			{if $browseSeriesItem->getOnlineISSN()}
			<div class="onlineISSN">{translate key="catalog.manage.series.onlineIssn"} {$browseSeriesItem->getOnlineISSN()|escape}</div>{/if}
		
			</div>								
			
		</li>
		{/iterate}

		{/if}
	</ul>
	</div>

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
