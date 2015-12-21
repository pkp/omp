{**
 * templates/frontend/pages/catalogSeries.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view books in a series in the catalog.
 *
 * @uses $series Series Current series being viewed
 * @uses $publishedMonographs array List of published monographs in this series
 * @uses $featuredMonographIds array List of featured monograph IDs in this series
 * @uses $newReleasesMonographs array List of new monographs in this series
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$series->getLocalizedTitle()}

<div class="page page_catalog_series">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs_catalog.tpl" type="series" currentTitle=$series->getLocalizedTitle()}

	{* Count of monographs in this series *}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* Image and description *}
	{assign var="image" value=$series->getImage()}
	{assign var="description" value=$series->getLocalizedDescription()|strip_unsafe_html}
	<div class="about_section{if $image} has_image{/if}{if $description} has_description{/if}">
		{if $image}
			<div class="cover" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="fullSize" type="series" id=$series->getId()}">
				<img src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$series->getId()}" alt="{$series->getLocalizedTitle()|escape}" />
			</div>
		{/if}
		<div class="description">
			{$description|nl2br|strip_unsafe_html}
		</div>
	</div>

	{* No published titles in this category *}
	{if empty($publishedMonographs)}
		<h3>
			{translate key="catalog.allBooks"}
		</h3>
		<p>{translate key="catalog.noTitlesSection"}</p>

	{else}

		{* New releases *}
		{if !empty($newReleasesMonographs)}
			{include file="frontend/components/monographList.tpl" monographs=$newReleasesMonographs titleKey="catalog.newReleases"}
		{/if}

		{* All monographs *}
		{include file="frontend/components/monographList.tpl" monographs=$publishedMonographs featured=$featuredMonographIds titleKey="catalog.allBooks"}

	{/if}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
