{**
 * templates/controllers/timeline/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission timeline "graph"
 *}

<div class="pkp_controllers_timeline">
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
		<span class="timelineLabel pastStep"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="submission" path=$monographId}">{translate key="submission.submission"}</a></span>
			<span class="timelineLabel pastStep"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="internalReview" path=$monographId}">{translate key="workflow.review.internalReview"}</a></span>
			<span class="timelineLabel pastStep"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="externalReview" path=$monographId}">{translate key="workflow.review.externalReview"}</a></span>
			<span class="timelineLabel center pastStep"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="copyediting" path=$monographId}">{translate key="submission.editorial"}</a></span>
			<span class="timelineLabel right pastStep"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="production" path=$monographId}">{translate key="submission.production"}</a></span>
	</div>

</div>
