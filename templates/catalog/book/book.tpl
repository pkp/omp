{**
 * templates/catalog/book/book.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing book view in the catalog.
 *}
{strip}
{assign var="suppressPageTitle" value=true}
{assign var="pageTitleTranslated" value=$publishedMonograph->getLocalizedTitle()}
{include file="common/header.tpl"}
{/strip}

<div class="pkp_catalog_book">

{include file="catalog/book/bookSpecs.tpl"}

{include file="catalog/book/bookInfo.tpl"}

</div><!-- pkp_catalog_book -->

{include file="common/footer.tpl"}
