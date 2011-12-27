{**
 * templates/catalog/category.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing category view in the catalog.
 *}
{strip}
{assign var="pageTitleTranslated" value=$category->getLocalizedTitle()}
{include file="common/header.tpl"}
{/strip}

<div class="catalogContainer">

<div class="pkp_catalog_categoryDescription">
	{$category->getLocalizedDescription()}
</div>

{* Include the carousel view of featured content *}
{if $featuredMonographIds|@count}
	{include file="catalog/carousel.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}
{/if}

{* Include the full monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

</div><!-- catalogContainer -->

{include file="common/footer.tpl"}
