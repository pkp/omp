{**
 * templates/frontend/pages/catalogSeriesIndex.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University Library
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the series that are included in a OMP press.
 * @uses browseSeriesItem array List of series in this press
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