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
	<ul>

		{foreach from=$spotlights item=spotlight}
			{assign var=item value=$spotlight->getSpotlightItem()}
			{assign var=assocType value=$spotlight->getAssocType()}
			{assign var=coverImage value=""}
			{assign var=coverImageUrl value=""}
			{assign var=targetUrl value=""}
			{if $assocType == $smarty.const.SPOTLIGHT_TYPE_BOOK}
				{assign var=coverImage value=$item->getCoverImage()}
				{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$item->getId()}
				{url|assign:coverImageUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" submissionId=$item->getId()}
			{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_SERIES}
				{assign var=coverImage value=$item->getImage()}
				{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$item->getPath()}
				{url|assign:coverImageUrl page="catalog" op="fullSize" type="series" id=$item->getId()}
			{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_AUTHOR}
				{assign var=monograph value=$item->getPublishedMonograph()}
				{if $monograph}
					{assign var=coverImage value=$monograph->getCoverImage()}
					{url|assign:targetUrl router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}
					{url|assign:coverImageUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" submissionId=$monograph->getId()}
				{/if}
			{/if}
			{assign var=hasCoverImage value=""}
			{if $coverImage}
				{assign var=hasCoverImage value="has_image"}
			{/if}
			{assign var=description value=""}
			{if $spotlight->getLocalizedDescription()}
				{assign var=description value=$spotlight->getLocalizedDescription()|truncate:300|strip_unsafe_html}
			{else}
				{if $assocType == $smarty.const.SPOTLIGHT_TYPE_SERIES}
					{assign var=description value=$item->getLocalizedDescription()|truncate:300|strip_unsafe_html}
				{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_BOOK}
					{assign var=description value=$item->getLocalizedAbstract()|truncate:300|strip_unsafe_html}
				{elseif $assocType == $smarty.const.SPOTLIGHT_TYPE_AUTHOR && $monograph}
					{assign var=description value=$monograph->getLocalizedAbstract()}
				{/if}
			{/if}

			<li>
				{include file="frontend/objects/spotlight.tpl"}
			</li>
		{/foreach}
	</ul>
</div>
