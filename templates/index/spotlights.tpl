{**
 * templates/index/spotlights.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display spotlights on a press' home page.
 *}
<div id="spotlightsHome">
	<h3 class="pkp_helpers_text_center">{translate key="spotlight.title.homePage"}</h3>
	<ul>
		{foreach from=$spotlights item=spotlight name=loop}
			{assign var="item" value=$spotlight->getSpotlightItem()}
				{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_BOOK}
					<li class="pkp_helpers_align_left pkp_helpers_third">
						<h4 class="pkp_helpers_text_center">{$spotlight->getLocalizedTitle()|escape}</h4>
						<div class="pkp_catalog_feature">
						{assign var=coverImage value=$item->getCoverImage()}
						<a class="pkp_helpers_image_right" href="{url page="catalog" op="book" path=$item->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$item->getLocalizedTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$item->getId()}" /></a>
						<div class="pkp_catalog_monographTitle">{$item->getLocalizedTitle()|strip_unsafe_html}</div>
						<div class="pkp_catalog_monographAbstract">{$item->getLocalizedAbstract()|strip_unsafe_html}</div>
						</div>
					</li>
				{/if}
		{/foreach}
	</ul>
</div>