{**
 * templates/catalog/category.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing category view in the catalog.
 *
 * Available data:
 *  $category Category
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *}
{include file="common/header.tpl" suppressPageTitle=true}

{if $category}
	<h2 class="pkp_helpers_text_center"><em>{$category->getLocalizedTitle()}</em></h2>
{/if}

<div class="catalogContainer">

{if $category}
	{assign var="image" value=$category->getImage()}
	{if $category->getLocalizedDescription() || $image}
		<div class="pkp_catalog_categoryDescription">
			{if $image}
				<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="fullSize" type="category" id=$category->getId()}">
					<img class="pkp_helpers_align_left" height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="category" id=$category->getId()}" alt="{$category->getLocalizedTitle()|escape}" />
				</a>
			{/if}
			{$category->getLocalizedDescription()|strip_unsafe_html}
		</div>
	{/if}

	{if $parentCategory}
		<div class="pkp_catalog_parentCategory">
			<h3>{translate key="catalog.parentCategory"}</h3>
			<ul><li><a href="{url op="category" path=$parentCategory->getPath()}">{$parentCategory->getLocalizedTitle()}</a></li></ul>
		</div>
	{/if}{* parentCategory *}

	{if !$subcategories->wasEmpty()}
		<div class="pkp_catalog_subcategories">
			<h3>{translate key="catalog.subcategories}</h3>
			<ul>
			{iterate from=subcategories item=subcategory}
				<li><a href="{url op="category" path=$subcategory->getPath()}">{$subcategory->getLocalizedTitle()}</a></li>
			{/iterate}{* subcategories *}
			</ul>
		</div>
	{/if}{* !$subcategories->wasEmpty() *}

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
