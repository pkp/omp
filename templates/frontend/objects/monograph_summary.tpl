{**
 * templates/frontend/objects/monograph_summary.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a summary view of a monograph for display in lists
 *
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $isFeatured bool Is this a featured monograph?
 *}
<div class="obj_monograph_summary{if $isFeatured} is_featured{/if}">
	<a href="{url page="catalog" op="book" path=$monograph->getBestId()}" class="cover">
		<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
	</a>
	{if $monograph->getSeriesPosition()}
		<div class="seriesPosition">
			{$monograph->getSeriesPosition()|escape}
		</div>
	{/if}
	<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getBestId()}" class="title">
		{$monograph->getLocalizedFullTitle()|escape}
	</a>
	<div class="author">
		{$monograph->getAuthorOrEditorString()|escape}
	</div>
	<div class="date">
		{$monograph->getDatePublished()|date_format:$dateFormatLong}
	</div>
</div><!-- .obj_monograph_summary -->
