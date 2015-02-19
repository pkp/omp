{**
 * templates/catalog/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Entry page for the public-facing catalog
 *}
{strip}
{assign var="pageTitle" value="navigation.catalog"}
{include file="common/header.tpl" suppressPageTitle=true}
{/strip}

{* Include the carousel view of featured content *}
{url|assign:carouselUrl router=$smarty.const.ROUTE_COMPONENT component="carousel.CarouselHandler" op="fetch" escape=false}
{load_url_in_div id="carousel" url=$carouselUrl}

{* Include the full monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

{include file="common/footer.tpl"}
