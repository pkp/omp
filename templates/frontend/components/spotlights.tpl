{**
 * templates/frontend/components/spotlights.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the spotlights in a list. This file prepares a number of
 *  variables to reduce the logic required in the spotlight object template.
 *
 * @uses $spotlights array Selected spotlights to promote on the homepage
 *}
<div class="cmp_spotlights">
	<ul class="list">
		{foreach name="spotlights" from=$spotlights item=spotlight}
			<li{if $smarty.foreach.spotlights.iteration == 1} class="current"{/if}>
				<a href="#" data-spotlight="{$smarty.foreach.spotlights.iteration}">
					{$spotlight->getLocalizedTitle()|escape}
				</a>
			</li>
		{/foreach}
	</ul>

	<ul class="spotlights">
		{foreach name="spotlights" from=$spotlights item=spotlight}
			{assign var=item value=$spotlight->getSpotlightItem()}
			{assign var=assocType value=$spotlight->getAssocType()}
			{assign var=coverImage value=""}
			{assign var=coverImageUrl value=""}
			{assign var=targetUrl value=""}
			{if $assocType == $smarty.const.SPOTLIGHT_TYPE_BOOK}
				{assign var=type value="is_book"}
				{assign var=coverImage value=$item->getCoverImage()}
				{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$item->getId()}
				{url|assign:coverImageUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$item->getId()}
			{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_SERIES}
				{assign var=type value="is_series"}
				{assign var=coverImage value=$item->getImage()}
				{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$item->getPath()}
				{url|assign:coverImageUrl page="catalog" op="thumbnail" type="series" id=$item->getId()}
			{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_AUTHOR}
				{assign var=type value="is_author"}
				{assign var=monograph value=$item->getPublishedMonograph()}
				{if $monograph}
					{assign var=coverImage value=$monograph->getCoverImage()}
					{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}
					{url|assign:coverImageUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId()}
				{/if}
			{/if}
			{assign var=hasCoverImage value=""}
			{if $coverImage}
				{assign var=hasCoverImage value="has_image"}
			{/if}
			{assign var=description value=""}
			{if $spotlight->getLocalizedDescription()}
				{assign var=description value=$spotlight->getLocalizedDescription()|truncate:600|strip_unsafe_html}
			{else}
				{if $assocType == $smarty.const.SPOTLIGHT_TYPE_SERIES}
					{assign var=description value=$item->getLocalizedDescription()|truncate:600|strip_unsafe_html}
				{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_BOOK}
					{assign var=description value=$item->getLocalizedAbstract()|truncate:600|strip_unsafe_html}
				{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_AUTHOR && $monograph}
					{assign var=description value=$monograph->getLocalizedAbstract()}
				{/if}
			{/if}

			<li class="spotlight_{$smarty.foreach.spotlights.iteration}{if $smarty.foreach.spotlights.iteration == 1} current{/if}">
				{include file="frontend/objects/spotlight.tpl"}
			</li>
		{/foreach}
	</ul>
</div>
