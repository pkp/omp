{**
 * templates/catalog/feature.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a single random feature in the catalog.
 *
 * Available data:
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *}

{* Get a random feature. *}
{assign var=featureCount value=$featuredMonographIds|@count}
{assign var=randomOffset value=0|rand:$featureCount-1}
{assign var=randomCounter value=0}
{foreach from=$featuredMonographIds key=monographId item=featureSeq}
	{if $randomCounter == $randomOffset}
		{assign var=featuredMonograph value=$publishedMonographs.$submissionId}
	{/if}
	{assign var=randomCounter value=$randomCounter+1}
{/foreach}
{* $featuredMonograph should now specify the random monograph, if any. *}

{if $featuredMonograph}
<div class="pkp_catalog_feature pkp_catalog_book">
	<h3>{translate key="catalog.feature"}</h3>

	{url|assign:bookImageLinkUrl op="book" path=$featuredMonograph->getId()}
	{include file="catalog/book/bookSpecs.tpl" publishedMonograph=$featuredMonograph}

	<div class="pkp_catalog_featureDetails">
		<h3>{$featuredMonograph->getLocalizedFullTitle()|strip_unsafe_html}</h3>
		<div class="pkp_catalog_feature_authorName">{$featuredMonograph->getAuthorString()|escape}</div>
		<div class="pkp_catalog_feature_abstract">{$featuredMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>
	</div>
</div>

{/if}{* $featuredMonograph *}
