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

<!-- Features carousel -->
<div class="pkp_catalog_carousel" id="featuresCarousel" style="width: 512px; height: 200px; background: #000; overflow: scroll;">
	{foreach from=$publishedMonographs item=publishedMonograph}
		{* Only include features in the carousel *}
		{assign var="monographId" value=$publishedMonograph->getId()}
		{if isset($featuredMonographIds[$monographId])}
			<img class="cloudcarousel" src="{$baseUrl}/templates/images/book-default.png" alt="{$publishedMonograph->getLocalizedTitle()|escape}" title="{$publishedMonograph->getLocalizedTitle()|escape}" />
		{/if}
	{/foreach}
	<div id="left-but" value="&lt;" style="position: absolute; top: 20px; right: 64px;" />
	<div id="right-but" value="&gt;" style="position: absolute; top: 20px; right: 20px;" />

	<p id="title-text"></p>
	<p id="alt-text"></p>
</div>
