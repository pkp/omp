{**
 * templates/frontend/pages/catalog.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the catalog.
 *
 * @uses $publishedMonographs array List of published monographs
 *}
{include file="common/frontend/header.tpl" pageTitle="navigation.catalog"}

<div class="page page_catalog">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="navigation.catalog"}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* No published titles *}
	{if !$publishedMonographs|@count}
		<h3>
			{translate key="catalog.allBooks"}
		</h3>
		<p>{translate key="catalog.noTitles"}</p>

	{* Monograph List *}
	{else}
		{include file="frontend/components/monographList.tpl" monographs=$publishedMonographs}
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
