{**
 * templates/catalog/carousel.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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
			<img src="{$baseUrl}/templates/images/book-default.png" alt="{$publishedMonograph->getLocalizedTitle()|escape}" />
		{/if}
	{/foreach}
</div>
