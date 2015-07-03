{**
 * templates/catalog/results.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search results page for the public-facing catalog
 *
 * Available data:
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *}
{include file="common/header.tpl" pageTitle="search.searchResults"}

{* Include the full monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

{include file="common/footer.tpl"}
