{**
 * templates/frontend/objects/monograph_summary.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a summary view of a monograph for display in lists
 *
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $authorUserGroups Traversible The set of author user groups
 * @uses $isFeatured bool Is this a featured monograph?
 *}
<div class="obj_monograph_summary{if $isFeatured} is_featured{/if}">
	{assign var=currentPublication value=$monograph->getCurrentPublication()}
		<a {if $press}href="{url press=$press->getPath() page="catalog" op="book" path=$monograph->getBestId()}"{else}href="{url page="catalog" op="book" path=$monograph->getBestId()}"{/if} class="cover">
			{assign var="coverImage" value=$currentPublication->getLocalizedData('coverImage')}
			<img
				src="{$monograph->getCurrentPublication()->getLocalizedCoverImageThumbnailUrl($monograph->getData('contextId'))}"
				alt="{$coverImage.altText|escape|default:''}"
			>
		</a>
		{if $currentPublication->getData('seriesPosition')}
			<div class="seriesPosition">
				{$currentPublication->getData('seriesPosition')|escape}
			</div>
		{/if}
		<{$heading} class="title">
			<a {if $press}href="{url press=$press->getPath() page="catalog" op="book" path=$monograph->getBestId()}"{else}href="{url page="catalog" op="book" path=$monograph->getBestId()}"{/if}>
				{$currentPublication->getLocalizedFullTitle(null, 'html')|strip_unsafe_html}
			</a>
		</{$heading}>
		<div class="author">
			{$currentPublication->getAuthorString($authorUserGroups, true)|escape}
		</div>
		<div class="date">
			{$currentPublication->getData('datePublished')|date_format:$dateFormatLong}
		</div>
</div><!-- .obj_monograph_summary -->
