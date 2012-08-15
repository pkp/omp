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
			<li class="pkp_helpers_align_left pkp_helpers_third">
				<h4 class="pkp_helpers_text_center">{$spotlight->getLocalizedTitle()|escape}</h4>
				<div class="pkp_catalog_feature">
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_BOOK}
						{assign var=coverImage value=$item->getCoverImage()}
						<a class="pkp_helpers_image_right" href="{url page="catalog" op="book" path=$item->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$item->getLocalizedTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$item->getId()}" /></a>
						{assign var="monographTitle" value=$item->getLocalizedPrefix()|concat:' ':$item->getLocalizedTitle()|strip_unsafe_html}
						<div class="pkp_catalog_monographTitle"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$item->getId()}">{$monographTitle}</a></div>
						{if $item->getLocalizedSubtitle() != ''}<div class="pkp_catalog_monographSubtitle">{$item->getLocalizedSubtitle()}</div>{/if}
						<div class="pkp_catalog_monographAbstract">{$item->getLocalizedAbstract()|strip_unsafe_html}</div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_SERIES}
						{assign var=image value=$item->getImage()}
						<a class="pkp_helpers_image_right" href="{url page="catalog" op="fullSize" type="series" id=$item->getId()}"><img height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" alt="{$item->getLocalizedTitle()|escape}" src="{url page="catalog" op="thumbnail" type="series" id=$item->getId()}" /></a>
						{assign var="seriesTitle" value=$item->getLocalizedPrefix()|concat:' ':$item->getLocalizedTitle()|strip_unsafe_html}
						<div class="pkp_catalog_monographTitle"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$item->getPath()}">{$seriesTitle}</a></div>
						{if $item->getLocalizedSubtitle() != ''}<div class="pkp_catalog_monographSubtitle">{$item->getLocalizedSubtitle()}</div>{/if}
						<div class="pkp_catalog_monographAbstract">{$item->getLocalizedDescription()|strip_unsafe_html}</div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_AUTHOR}
						{assign var=monograph value=$item->getPublishedMonograph()}
						{if $monograph}
							{assign var=coverImage value=$monograph->getCoverImage()}
							<a class="pkp_helpers_image_right" href="{url page="catalog" op="book" path=$monograph->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$monograph->getLocalizedTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$monograph->getId()}" /></a>
						{/if}
						{assign var="authorName" value=$item->getFullName()|strip_unsafe_html}
						<div class="pkp_catalog_monographTitle">{$authorName}</div>
						{if $monograph}<div class="pkp_catalog_monographSubtitle">{translate key="spotlight.author"} {$monograph->getLocalizedTitle()}</div>{/if}
					{/if}
				</div>
			</li>
		{/foreach}
	</ul>
</div>
