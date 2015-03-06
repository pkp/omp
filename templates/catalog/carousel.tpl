{**
 * templates/catalog/carousel.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a carousel in the public-facing catalog view.
 *
 * Available data:
 *  $publishedMonographs array Array of PublishedMonograph objects to display.
 *  $featuredMonographIds array Array of (monographId => sequence)
 *}

{* Only include if there are actually monographs to display *}
{if $publishedMonographs|@count > 0}

<script type="text/javascript">
	$(function() {ldelim}
		$('.pkp_catalog_carousel_wrapper').pkpHandler(
			'$.pkp.pages.catalog.CarouselHandler'
		);
	{rdelim});
</script>

<!-- Features carousel -->
<div class="pkp_catalog_carousel_wrapper">
	<div class="pkp_catalog_carousel">
		{foreach from=$publishedMonographs item=publishedMonograph}
			{* Only include features in the carousel *}
			{assign var="submissionId" value=$publishedMonograph->getId()}
			{if isset($featuredMonographIds[$submissionId])}
			<div id="publishedMonograph-{$submissionId}" class="monograph">
				<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" submissionId=$publishedMonograph->getId() random=$publishedMonograph->getId()|uniqid}" alt="{$publishedMonograph->getLocalizedFullTitle()|strip_tags|escape}" data-caption="#publishedMonograph-{$submissionId}-caption"/>
				<div class="details_box" id="publishedMonograph-{$submissionId}-details">
					<h4>{$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html}</h4>
					<div class="pkp_catalog_monograph_authorship">{$publishedMonograph->getAuthorString()|escape}</div>
					{if $publishedMonograph->getPublicationFormatString()}
						<div class="pkp_catalog_formats">
							<strong>{translate key="monograph.carousel.publicationFormats"}</strong><br />
							{$publishedMonograph->getPublicationFormatString()|escape}
						</div>
					{/if}
					<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$submissionId}">{translate key="common.plusMore"}</a></div>
				</div>
			</div>
			{/if}
		{/foreach}
	</div>
</div>
{/if}{* $publishedMonographs|@count > 0 *}
