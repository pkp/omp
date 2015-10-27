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
	{include file="frontend/components/breadcrumbs_catalog.tpl" currentTitleKey="catalog.newReleases"}

	{* Count of new releases being dispalyed *}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* No published titles in this category *}
	{if empty($publishedMonographs)}
		<p>{translate key="catalog.noTitlesNew"}</p>

	{else}
		{include file="frontend/components/monographList.tpl" monographs=$publishedMonographs}

	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
