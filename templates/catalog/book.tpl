{**
 * templates/catalog/series.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing series view in the catalog.
 *}
{strip}
{assign var="suppressPageTitle" value=true}
{assign var="pageTitleTranslated" value=$publishedMonograph->getLocalizedTitle()}
{include file="common/header.tpl"}
{/strip}

<div class="pkp_catalog_book">

{include file="catalog/bookSpecs.tpl"}

<div class="bookInfo">
	<h3>{$publishedMonograph->getLocalizedTitle()|strip_unsafe_html}</h3>
	<div class="authorName">{$publishedMonograph->getAuthorString()}</div>
</div>

</div><!-- bookContainer -->

{include file="common/footer.tpl"}
