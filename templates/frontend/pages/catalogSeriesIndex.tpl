{**
 * templates/frontend/pages/catalogSeriesIndex.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University Library
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a page with an overview the Book series in the press.
 * @uses $browseSeriesFactory array List of series
 *}
 
{include file="frontend/components/header.tpl" pageTitle="series.series"}

<div class="page page_catalog_seriesIndex">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs_catalog.tpl" type="series" currentTitleKey="series.series"}

	<h1>
		{translate key="series.series"}
	</h1>

	{* Index with series *}
	<div class="series_list">
		<ul>
			{* Series *}
			{if $browseSeriesFactory && $browseSeriesFactory->getCount()}
				{iterate from=browseSeriesFactory item=browseSeriesItem}				
					<li>
						<div class="series_description">		

							{* Image and description *}
							<div class="cover">
								<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}">
								{assign var="image" value=$browseSeriesItem->getImage()}
								{if $image}
								<img src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$browseSeriesItem->getId()|escape}" alt="{$browseSeriesItem->getLocalizedFullTitle()|escape}" /></a>
								{/if}
							</div>

							<div class="metadata">				
								<h2>
									<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}"> {$browseSeriesItem->getLocalizedFullTitle()|escape}</a>
								</h2>

								<div class="description">
									{$browseSeriesItem->getLocalizedDescription()|strip_unsafe_html}
								</div>

								{if $browseSeriesItem->getPrintISSN()}
									<div class="printISSN">
										{translate key="catalog.manage.series.printIssn"} {$browseSeriesItem->getPrintISSN()|escape}
									</div>
								{/if}

								{if $browseSeriesItem->getOnlineISSN()}
									<div class="onlineISSN">
										{translate key="catalog.manage.series.onlineIssn"} {$browseSeriesItem->getOnlineISSN()|escape}
									</div>
								{/if}	
							</div>								
						</div>
					</li>
				{/iterate}
			{/if}
		</ul>
	</div>
</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
