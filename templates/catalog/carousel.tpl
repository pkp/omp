{**
 * templates/catalog/carousel.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a carousel in the public-facing catalog view.
 *}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#featuresCarousel').pkpHandler(
			'$.pkp.pages.catalog.CarouselHandler'
		);
	{rdelim});
</script>

<!-- Features carousel -->
<div class="pkp_catalog_carousel" id="featuresCarousel">
	{foreach from=$publishedMonographs item=publishedMonograph}
		{* Only include features in the carousel *}
		{assign var="monographId" value=$publishedMonograph->getId()}
		{if isset($featuredMonographIds[$monographId])}
			<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" monographId=$publishedMonograph->getId()}" alt="{$publishedMonograph->getLocalizedTitle()|escape}" data-caption="#publishedMonograph-{$monographId}-caption" />
		{/if}
	{/foreach}
</div>
{* assemble the captions for each of the featured items in the carousel *}
{foreach from=$publishedMonographs item=publishedMonograph}
	{assign var="monographId" value=$publishedMonograph->getId()}
	{if isset($featuredMonographIds[$monographId])}
		<span class="orbit-caption" id="publishedMonograph-{$monographId}-caption"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monographId}">{$publishedMonograph->getLocalizedTitle()|escape}</a></span>
	{/if}
{/foreach}
