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
		<p>{translate key="catalog.noTitles"}</p>

	{* Monograph List *}
	{else}
		<div class="cmp_monographs_list">
			{foreach name="monographLoop" from=$publishedMonographs item=monograph}
				{if $smarty.foreach.monographLoop.iteration is odd by 1}
					<div class="row">
				{/if}
					{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
				{if $smarty.foreach.monographLoop.iteration is even by 1}
					</div>
				{/if}
			{/foreach}
			{* Close .row if we have an odd number of titles *}
			{if $smarty.foreach.monographLoop.iteration is odd by 1}
				</div>
			{/if}
		</div>
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
