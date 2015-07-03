{**
 * templates/catalog/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Entry page for the public-facing catalog
 *
 * Available data:
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *}
{include file="common/header.tpl" suppressPageTitle=true pageTitle="navigation.catalog"}

{* Include the carousel view of featured content *}
{url|assign:carouselUrl router=$smarty.const.ROUTE_COMPONENT component="carousel.CarouselHandler" op="fetch" escape=false}
{load_url_in_div id="carousel" url=$carouselUrl}

{* Include the full monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}

{include file="common/footer.tpl"}
