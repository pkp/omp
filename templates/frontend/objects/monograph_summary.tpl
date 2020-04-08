{**
 * templates/frontend/objects/monograph_summary.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a summary view of a monograph for display in lists
 *
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $isFeatured bool Is this a featured monograph?
 *}
<div class="obj_monograph_summary{if $isFeatured} is_featured{/if}">
		<a href="{url page="catalog" op="book" path=$monograph->getBestId()}" class="cover">
			{assign var="coverImage" value=$monograph->getCurrentPublication()->getLocalizedData('coverImage')}
			<img
				src="{$monograph->getCurrentPublication()->getLocalizedCoverImageThumbnailUrl($monograph->getData('contextId'))}"
				alt="{$coverImage.altText|escape|default:''}"
			>
		</a>
		{if $monograph->getSeriesPosition()}
			<div class="seriesPosition">
				{$monograph->getSeriesPosition()|escape}
			</div>
		{/if}
		<{$heading} class="title">
			<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getBestId()}">
				{$monograph->getLocalizedFullTitle()|escape}
			</a>
		</{$heading}>
		<div class="author">
			{$monograph->getAuthorOrEditorString()|escape}
		</div>
		<div class="date">
			{$monograph->getDatePublished()|date_format:$dateFormatLong}
		</div>
</div><!-- .obj_monograph_summary -->
