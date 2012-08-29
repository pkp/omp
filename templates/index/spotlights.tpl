{**
 * templates/index/spotlights.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display spotlights on a press' home page.
 *}
<div id="spotlightsHome">
	<h2 class="pkp_helpers_text_center">{translate key="spotlight.title.homePage"}</h2>
	<ul>
		{foreach from=$spotlights item=spotlight name=loop}
			{assign var="item" value=$spotlight->getSpotlightItem()}
			<li class="pkp_helpers_align_left pkp_helpers_third">
				<h4 class="pkp_helpers_text_center">{$spotlight->getLocalizedTitle()|escape}</h4>
				<div class="pkp_catalog_feature">
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_BOOK}
						{assign var=coverImage value=$item->getCoverImage()}
						{if $coverImage}
							<a class="pkp_helpers_image_right" href="{url page="catalog" op="book" path=$item->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$item->getLocalizedFullTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$item->getId()}" /></a>
						{/if}
						<div class="pkp_catalog_monographTitle">{$item->getLocalizedFullTitle()}</div>
						<div class="pkp_catalog_monographAbstract">{$item->getLocalizedAbstract()|strip_unsafe_html}</div>
						<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$item->getId()}">{translate key="common.plusMore"}</a></div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_SERIES}
						{assign var=image value=$item->getImage()}
						{if $image}
							<a class="pkp_helpers_image_right" href="{url page="catalog" op="fullSize" type="series" id=$item->getId()}"><img height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" alt="{$item->getLocalizedFullTitle()|escape}" src="{url page="catalog" op="thumbnail" type="series" id=$item->getId()}" /></a>
						{/if}
						<div class="pkp_catalog_monographTitle">{$item->getLocalizedFullTitle()}</div>
						<div class="pkp_catalog_monographAbstract">{$item->getLocalizedDescription()|strip_unsafe_html}</div>
						<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$item->getPath()}">{translate key="common.plusMore"}</a></div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_AUTHOR}
						{assign var=monograph value=$item->getPublishedMonograph()}
						{if $monograph}
							{assign var=coverImage value=$monograph->getCoverImage()}
							{if $coverImage}
								<a class="pkp_helpers_image_right" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$monograph->getLocalizedFullTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$monograph->getId()}" /></a>
							{/if}
						{/if}
						{assign var="authorName" value=$item->getFullName()|strip_unsafe_html}
						<div class="pkp_catalog_monographTitle">{$authorName}</div>
						{if $monograph}
							<div class="pkp_catalog_monographSubtitle">{translate key="spotlight.author"} {$monograph->getLocalizedFullTitle()}</div>
							<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}">{translate key="common.plusMore"}</a></div>
						{/if}
					{/if}
				</div>
			</li>
		{/foreach}
	</ul>
</div>
