{**
 * templates/frontend/pages/catalogCategory.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view a category of the catalog.
 *
 * @uses $category Category Current category being viewed
 * @uses $publishedMonographs array List of published monographs in this category
 * @uses $featuredMonographIds array List of featured monograph IDs in this category
 * @uses $newReleasesMonographs array List of new monographs in this category
 * @uses $parentCategory Category Parent category if one exists
 * @uses $subcategories array List of subcategories if they exist
 * @uses $alreadyShown array Array of monograph IDs which have already been
 *       displayed. These IDs are excluded from later sections.
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$category->getLocalizedTitle()}

<div class="page page_catalog_category">

	{* Breadcrumb *}
	{include file="frontend/components/breadcrumbs_catalog.tpl" type="category" parent=$parentCategory currentTitle=$category->getLocalizedTitle()}

	{* Count of monographs in this category *}
	<div class="monograph_count">
		{translate key="catalog.browseTitles" numTitles=$publishedMonographs|@count}
	</div>

	{* Image and description *}
	{assign var="image" value=$category->getImage()}
	{assign var="description" value=$category->getLocalizedDescription()|strip_unsafe_html}
	<div class="about_section{if $image} has_image{/if}{if $description} has_description{/if}">
		{if $image}
			<div class="cover" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="fullSize" type="category" id=$category->getId()}">
				<img src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="category" id=$category->getId()}" alt="{$category->getLocalizedTitle()|escape}" />
			</div>
		{/if}
		<div class="description">
			{$description|nl2br|strip_unsafe_html}
		</div>
	</div>

	{if !$subcategories->wasEmpty()}
	<nav class="subcategories" role="navigation">
		<h2>
			{translate key="catalog.subcategories"}
		</h2>
		<ul>
			{iterate from=subcategories item=subcategory}
				<li>
					<a href="{url op="category" path=$subcategory->getPath()}">
						{$subcategory->getLocalizedTitle()|escape}
					</a>
				</li>
			{/iterate}
		</ul>
	</nav>
	{/if}

	{* No published titles in this category *}
	{if empty($publishedMonographs)}
		<h2>
			{translate key="catalog.allBooks"}
		</h2>
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
