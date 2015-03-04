{**
 * templates/catalog/series.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing series view in the catalog.
 *
 * Available data:
 *  $series Series
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *}
{include file="common/header.tpl" suppressPageTitle=true pageTitleTranslated=$series->getLocalizedFullTitle()}

<h2 class="pkp_helpers_text_center"><em>{$series->getLocalizedFullTitle()}</em></h2>

<div class="catalogContainer">

{if $series}
	{assign var="image" value=$series->getImage()}
	{if $series->getLocalizedDescription() || $image}
		<div class="pkp_catalog_seriesDescription">
			{if $image}
				<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="fullSize" type="series" id=$series->getId()}">
					<img class="pkp_helpers_align_left" height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$series->getId()}" alt="{$series->getLocalizedFullTitle()|escape}" />
				</a>
			{/if}
			{$series->getLocalizedDescription()|strip_unsafe_html}
		</div>
	{/if}

	{* Include the carousel view of featured content *}
	{if $featuredMonographIds|@count}
		{include file="catalog/carousel.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}
	{/if}

	{* Include the highlighted feature *}
	{include file="catalog/feature.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}

	{* Include the new release monograph list *}
	{if !empty($newReleasesMonographs)}
		{include file="catalog/monographs.tpl" publishedMonographs=$newReleasesMonographs monographListTitleKey="navigation.newReleases"}
	{/if}

	{* Include the full monograph list *}
	{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}
{/if}
</div><!-- catalogContainer -->

{include file="common/footer.tpl"}
