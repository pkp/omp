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

<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/orbit-1.2.3.css" />

<!-- Features carousel -->
<div class="pkp_catalog_carousel" id="featuresCarousel">
	{foreach from=$publishedMonographs item=publishedMonograph}
		{* Only include features in the carousel *}
		{assign var="monographId" value=$publishedMonograph->getId()}
		{if isset($featuredMonographIds[$monographId])}
			<a href="{url op="book" path=$publishedMonograph->getId()}"><img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" monographId=$publishedMonograph->getId()}" alt="{$publishedMonograph->getLocalizedTitle()|escape}" /></a>
		{/if}
	{/foreach}
</div>
