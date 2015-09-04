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
{include file="common/frontend/header.tpl" pageTitleTranslated=$series->getLocalizedTitle()}

<div class="page page_catalog_series">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs.tpl" type="series" currentTitle=$series->getLocalizedTitle()}

	{* Page title *}
	<h1 class="page_title">
		{$series->getLocalizedTitle()}
	</h1>
	<h2 class="page_subtitle">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</h2>

	{* Image *}
	{assign var="image" value=$series->getImage()}
	{if $image}
		<a class="cover" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="fullSize" type="series" id=$series->getId()}">
			<img src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$series->getId()}" alt="{$series->getLocalizedTitle()|escape}" />
		</a>
	{/if}

	{* Description *}
	{$series->getLocalizedDescription()|strip_unsafe_html}

	{* No published titles in this category *}
	{if empty($publishedMonographs)}
		<p>{translate key="catalog.noTitlesSection"}</p>

	{else}

		{* Featured monographs *}
		{if !empty($featuredMonographIds)}
			<h3>
				{translate key="catalog.featuredBooks"}
			</h3>
			<ul class="cmp_monographs_list featured">
				{foreach from=$featuredMonographIds item=featuredMonographId}
					{if array_key_exists($featuredMonographId, $publishedMonographs)}
						<li>
							{include file="frontend/objects/monograph_summary.tpl" monograph=$publishedMonographs[$featuredMonographId]}
						</li>
					{/if}
				{/foreach}
			</ul>
		{/if}

		{* New releases *}
		{if !empty($newReleasesMonographs)}
			<h3>
				{translate key="catalog.newReleases"}
			</h3>
			<ul class="cmp_monographs_list new">
				{foreach from=$newReleasesMonographs item=monograph}
					<li>
						{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
					</li>
				{/foreach}
			</ul>
		{/if}

		{* All monographs *}
		<h3>
			{translate key="catalog.allBooks"}
		</h3>
		<ul class="cmp_monographs_list">
			{foreach from=$publishedMonographs item=monograph}
				<li>
					{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
				</li>
			{/foreach}
		</ul>

	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
