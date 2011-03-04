{**
 * submissionHeader.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission header.
 *
 * Parameters:
 *  stageId: The current workflow stage.
 *  monograph: The submission we're looking at.
 *}
<div class="pkp_common_submissionHeaderHeading">
	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW}
		{translate key="workflow.review.internalReview"}:
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		{translate key="workflow.review.externalReview"}:
	{/if}
	{assign var="primaryAuthor" value=$monograph->getPrimaryAuthor()}
	{$primaryAuthor->getLastName()} - {$monograph->getLocalizedTitle()}
</div>
