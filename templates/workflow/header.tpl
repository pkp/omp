{**
 * templates/workflow/header.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header that contains details about the submission
 *}

{strip}
{assign var="pageTitleTranslated" value=$monograph->getLocalizedTitle()|concat:" - "|concat:$monograph->getAuthorString(true)}
{include file="common/header.tpl" suppressPageTitle=true}
{/strip}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#submissionHeader').pkpHandler(
			'$.pkp.pages.workflow.SubmissionHeaderHandler'
		);
	{rdelim});
</script>

<div id="submissionHeader" class="pkp_submission_header">
	<div class="participant_popover" style="display: none;">
		{url|assign:stageParticipantGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
		{load_url_in_div id="stageParticipantGridContainer" url="$stageParticipantGridUrl"}
	</div>
	<div style="float:right;">
		<ul class="submission_actions pkp_helpers_flatlist">
			<li>{include file="linkAction/linkAction.tpl" action=$editMetadataAction}</li>
			<li>{include file="linkAction/linkAction.tpl" action=$submissionInformationCentreAction}</li>
			<li class="participants"><a href="#" id="participantToggle" class="participants">{translate key="editor.monograph.stageParticipants"}</a></li>
		</ul>
	</div>
	<div style="float: left;"><span class="h2">{$pageTitleTranslated}</span></div>
	{** figure out how the progress for each level **}
	{if $lastCompletedStageId eq $smarty.const.WORKFLOW_STAGE_ID_SUBMISSION}
		{assign var="submissionProgress" value=" current"}
	{/if}
	{if $lastCompletedStageId gte $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW}
		{assign var="submissionProgress" value=" complete"}
		{assign var="internalReviewProgress" value="current"}
	{/if}
	{if $lastCompletedStageId gte $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		{assign var="internalReviewProgress" value="complete"}
		{assign var="externalReviewProgress" value="current"}
	{/if}
	{if $lastCompletedStageId gte $smarty.const.WORKFLOW_STAGE_ID_EDITING}
		{assign var="externalReviewProgress" value="complete"}
		{assign var="editingProgress" value="current"}
	{/if}
	{if $lastCompletedStageId gte $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}
		{assign var="editingProgress" value="complete"}
		{assign var="productionProgress" value="current"}
	{/if}
	{if $lastCompletedStageId eq $smarty.const.WORKFLOW_STAGE_ID_PUBLISHED}
		{assign var="productionProgress" value="complete"}
		{assign var="publicationProgress" value="current "}
	{/if}
	<div class="submission_progress_wrapper">
		<ul class="submission_progress pkp_helpers_flatlist">
			<li class="first{$submissionProgress}"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="submission" path=$monograph->getId()}">{translate key="submission.submission"}</a></li>
			<li class="{$internalReviewProgress}"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="internalReview" path=$monograph->getId()}">{translate key="workflow.review.internalReview"}</a></li>
			<li class="{$externalReviewProgress}"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="externalReview" path=$monograph->getId()}">{translate key="workflow.review.externalReview"}</a></li>
			<li class="{$editingProgress}"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="copyediting" path=$monograph->getId()}">{translate key="submission.editorial"}</a></li>
			<li class="{$productionProgress}"><a href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op="production" path=$monograph->getId()}">{translate key="submission.production"}</a></li>
			<li class="{$publicationProgress}"><a href="#">{translate key="submission.published"}!</a></li>
		</ul>
	</div>
</div>
<br />
<div class="pkp_helpers_clear"></div>

<div class="pkp_workflow_headerBottom">
	<div class="pkp_workflow_headerUserInfo">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="workflowNotification" requestOptions=$workflowNotificationRequestOptions}
	</div>
</div>
<div class="pkp_helpers_clear"></div>
