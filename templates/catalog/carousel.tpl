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
		$('#carouselContainer').pkpHandler(
			'$.pkp.pages.catalog.CarouselHandler',
			{ldelim}
				previewFetchUrlTemplate: '{url|escape:"javascript" op="preview" monographId=MONOGRAPH_ID escape=false}'
			{rdelim}
		);
	{rdelim});
</script>

<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/orbit-1.2.3.css" />

<div id="carouselContainer">

<!-- Features carousel -->
<div class="pkp_catalog_carousel" id="featuresCarousel">
	{foreach from=$publishedMonographs item=publishedMonograph}
		{* Only include features in the carousel *}
		{assign var="monographId" value=$publishedMonograph->getId()}
		{if isset($featuredMonographIds[$monographId])}
			<img id="carousel-monograph-{$monographId|escape}" src="{$baseUrl}/templates/images/book-default.png" alt="{$publishedMonograph->getLocalizedTitle()|escape}" />
		{/if}
	{/foreach}
</div>

<div id="previewContainer">
	{* Will be filled in via JavaScript *}
</div>

</div>
