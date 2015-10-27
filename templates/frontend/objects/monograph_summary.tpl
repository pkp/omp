{**
 * templates/frontend/objects/monograph_summary.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a summary view of a monograph for display in lists
 *
 * @uses $monograph Monograph The monograph to be displayed
 *}
<div class="obj_monograph_summary">
	<a href="{url page="catalog" op="book" path=$monograph->getId()}" class="cover">
		<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
	</a>
	<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="book" path=$monograph->getId()}" class="title">
		{$monograph->getLocalizedFullTitle()}
	</a>
	<div class="author">
		{$monograph->getAuthorString()|escape}
	</div>
	<div class="date">
		{$monograph->getDatePublished()|date_format:$dateFormatLong}
	</div>
</div><!-- .obj_monograph_summary -->
