{**
 * templates/controllers/timeline/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission timeline "graph"
 *}

<div class="pkp_controllers_timeline">

	{assign var=stageId value=$monograph->getStageId()}

	{** Determine the amount the progress bar should be filled **}
	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW}{assign var="fillerClass" value="accepted"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}{assign var="fillerClass" value="internallyReviewed"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EDITING}{assign var="fillerClass" value="reviewed"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}{assign var="fillerClass" value="copyedited"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_PUBLISHED}{assign var="fillerClass" value="published"}
	{else}{assign var="fillerClass" value=""}{/if}

	<div class="timelineContainer">
		<div class="timelineFiller {$fillerClass|escape}"></div>
	</div>

	<div class="timelineLabelContainer">
		<span class="timelineLabel pastStep">{translate key="submission.submission"}</span>
		<span class="timelineLabel center {if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW}pastStep{else}futureStep{/if}">{translate key="workflow.review.internalReview"}</span>
		<span class="timelineLabel center {if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}pastStep{else}futureStep{/if}">{translate key="workflow.review.externalReview"}</span>
		<span class="timelineLabel center {if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EDITING}pastStep{else}futureStep{/if}">{translate key="submission.editorial"}</span>
		<span class="timelineLabel right {if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}pastStep{else}futureStep{/if}">{translate key="submission.production"}</span>
	</div>

</div>
