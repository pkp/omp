{**
 * templates/catalog/category.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing category view in the catalog.
 *}
{strip}
{if $category}{assign var="pageTitleTranslated" value=$category->getLocalizedTitle()}{/if}
{include file="common/header.tpl"}
{/strip}

<div class="catalogContainer">

{if $category}
	<div class="pkp_catalog_categoryDescription">
		{$category->getLocalizedDescription()}
	</div>

	{* Include the carousel view of featured content *}
	{if $featuredMonographIds|@count}
		{include file="catalog/carousel.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}
	{/if}

	{* Include the highlighted feature *}
	{include file="catalog/feature.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}

	{* Include the full monograph list *}
	{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

	</div><!-- catalogContainer -->
{/if}

{include file="common/footer.tpl"}
