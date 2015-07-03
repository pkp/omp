{**
 * templates/catalog/monograph.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph in the catalog.
 *
 * Available data:
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *  $publishedMonograph PublishedMonograph The published monograph object to display
 *}
<li class="pkp_catalog_monograph{if $inline} pkp_helpers_align_left{/if}">
	<a href="{url page="catalog" op="book" path=$publishedMonograph->getId()}">
		{include file="controllers/monographList/coverImage.tpl" monograph=$publishedMonograph}
	</a>
	<div class="pkp_catalog_monographDetails">
		<div class="pkp_catalog_monographTitle"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$publishedMonograph->getId()}">{$publishedMonograph->getLocalizedFullTitle()}</a></div>
		<div class="pkp_catalog_monograph_authorship pkp_helpers_clear">
			{$publishedMonograph->getAuthorString()|escape}
		</div>
	</div>
	<div class="pkp_catalog_monograph_date">
			{$publishedMonograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
</li>
