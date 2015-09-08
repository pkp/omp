{**
 * templates/frontend/pages/catalogNewReleases.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view new release in the catalog.
 *
 * @uses $publishedMonographs array List of published monographs in this category
 *}
{include file="common/frontend/header.tpl" pageTitle="catalog.newReleases"}

<div class="page page_catalog_new_releases">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="catalog.newReleases"}

	{* Page title *}
	<h1 class="page_title">
		{translate key="catalog.newReleases"}
	</h1>
	<h2 class="page_subtitle">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</h2>

	{* No published titles in this category *}
	{if empty($publishedMonographs)}
		<p>{translate key="catalog.noTitlesNew"}</p>

	{else}
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
