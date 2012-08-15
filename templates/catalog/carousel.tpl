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
<div class="pkp_catalog_carousel_wrapper" id="featuresCarousel">
	<div class="carousel_control" id="nextCarouselItem"></div>
	<div class="carousel_control" id="previousCarouselItem"></div>
	<h2 class="pkp_helpers_text_center">{translate key="catalog.featuredBooks"}</h2>
	<ul class="pkp_catalog_carousel">
		{foreach from=$publishedMonographs item=publishedMonograph}
			{* Only include features in the carousel *}
			{assign var="monographId" value=$publishedMonograph->getId()}
			{if isset($featuredMonographIds[$monographId])}
			<li id="publishedMonograph-{$monographId}" class="mover">
				<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" monographId=$publishedMonograph->getId()}" alt="{$publishedMonograph->getLocalizedFullTitle()|escape}" data-caption="#publishedMonograph-{$monographId}-caption" width="150" height="250"/>
				<div class="details_box" id="publishedMonograph-{$monographId}-details">
					<h4>{$publishedMonograph->getLocalizedFullTitle()|escape}</h4>
					<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monographId}">{translate key="common.moreInfo"}</a>
				</div>
				<div class="pkp_helpers_progressIndicator"></div>
			</li>
			{/if}
		{/foreach}
	</ul>
</div>
