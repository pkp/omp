{**
 * templates/index/spotlights.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display spotlights on a press' home page.
 *}
<div id="spotlightsHome" class="pkp_helpers_dotted_underline">
	<h2 class="pkp_helpers_text_center"><em>{translate key="spotlight.title.homePage"}</em></h2>
	<ul>
		{foreach from=$spotlights item=spotlight name=loop}
			{assign var="item" value=$spotlight->getSpotlightItem()}
			<li class="pkp_helpers_align_left pkp_helpers_third">
				<h4 class="pkp_helpers_text_center">{$spotlight->getLocalizedTitle()|strip_unsafe_html}</h4>
				<div class="pkp_catalog_spotlight">
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_BOOK}
						{assign var=coverImage value=$item->getCoverImage()}
						{if $coverImage}
							<a class="pkp_helpers_image_right" href="{url page="catalog" op="book" path=$item->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$item->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$item->getId()}" /></a>
						{/if}
						<div class="pkp_catalog_spotlight_itemTitle">{$item->getLocalizedFullTitle()|strip_unsafe_html}</div>
						<div class="pkp_catalog_spotlight_itemAuthorship">{$item->getAuthorString()|strip_unsafe_html}</div>
						<div class="pkp_catalog_spotlight_itemDescription">
							{if $spotlight->getLocalizedDescription()}
								{$spotlight->getLocalizedDescription()|strip_unsafe_html}
							{else}
								{$item->getLocalizedAbstract()|truncate:150|strip_unsafe_html}
							{/if}
						</div>
						<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$item->getId()}">{translate key="common.plusMore"}</a></div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_SERIES}
						{assign var=image value=$item->getImage()}
						{if $image}
							<a class="pkp_helpers_image_right" href="{url page="catalog" op="fullSize" type="series" id=$item->getId()}"><img height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" alt="{$item->getLocalizedFullTitle()|strip_tags|escape}" src="{url page="catalog" op="thumbnail" type="series" id=$item->getId()}" /></a>
						{/if}
						<div class="pkp_catalog_spotlight_itemTitle">
							{translate key="series.series"}: {$item->getLocalizedFullTitle()|strip_unsafe_html}
						</div>
						{assign var=editorsString value=$item->getEditorsString()}
						{if $editorsString}
							<div class="pkp_catalog_spotlight_itemAuthorship">
							{translate key="user.role.editors"}: {$editorsString|escape}
							</div>
						{/if}
						<div class="pkp_catalog_spotlight_itemDescription">
							{if $spotlight->getLocalizedDescription()}
								{$spotlight->getLocalizedDescription()|strip_unsafe_html}
							{else}
								{$item->getLocalizedDescription()|truncate:150|strip_unsafe_html}
							{/if}
						</div>
						<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$item->getPath()}">{translate key="common.plusMore"}</a></div>
					{/if}
					{if $spotlight->getAssocType() == $smarty.const.SPOTLIGHT_TYPE_AUTHOR}
						{assign var=monograph value=$item->getPublishedMonograph()}
						{if $monograph}
							{assign var=coverImage value=$monograph->getCoverImage()}
							{if $coverImage}
								<a class="pkp_helpers_image_right" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}"><img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" alt="{$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId()}" /></a>
							{/if}
						{/if}
						{assign var="authorName" value=$item->getFullName()|strip_unsafe_html}
						{if $monograph}
							<div class="pkp_catalog_spotlight_itemTitle">{$monograph->getLocalizedFullTitle()|strip_unsafe_html}</div>
							<div class="pkp_catalog_spotlight_itemAuthorship">{$authorName}</div>
							<div class="pkp_catalog_spotlight_itemDescription">
								{if $spotlight->getLocalizedDescription()}
									{$spotlight->getLocalizedDescription()|strip_unsafe_html}
								{else}
									{$monograph->getLocalizedAbstract()|truncate:150|strip_unsafe_html}
								{/if}
							</div>
							<div class="pkp_catalog_readMore"><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}">{translate key="common.plusMore"}</a></div>
						{else}
							<div class="pkp_catalog_spotlight_itemTitle">{$authorName}</div>
							<div class="pkp_catalog_spotlight_itemDescription">
								{$spotlight->getLocalizedDescription()|strip_unsafe_html}
							</div>
						{/if}
					{/if}
				</div>
			</li>
		{/foreach}
	</ul>
</div>
