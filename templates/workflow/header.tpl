{**
 * header.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header that contains details about the submission
 *}

<div class="pkp_submissionHeader">
	<div class="pkp_submissionHeaderTop">
		{include file="common/submissionHeader.tpl" stageId=$stageId monograph=$monograph}

		<div class="action pkp_linkActions">
			{url|assign:"allParticipantsUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionParticipants.SubmissionParticipantsHandler" op="fetch" stageId=$monograph->getCurrentStageId() monographId=$monograph->getId() escape=false}
			{modal url="$allParticipantsUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.step1.viewAllDetails' button="#allParticipants"}
			<a id="allParticipants" class="user_list" href="{$metadataUrl}">{translate key="submission.submit.allParticipants"}</a>
		</div>

		<div class="action pkp_linkActions">
			{url|assign:"metadataUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler" op="fetch" stageId=$monograph->getCurrentStageId() monographId=$monograph->getId() escape=false}
			{modal url="$metadataUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.step1.viewAllDetails' button="#viewMetadata"}
			<a id="viewMetadata" class="more_info" href="{$metadataUrl}">{translate key="submission.submit.metadata"}</a>
		</div>
	</div>
	<div class="pkp_helpers_clear"></div>

	<div class="pkp_submissionTimeline">
		<span class="pastStep">{translate key="submission.submission"}</span> &#187;
		{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW}<span class="pastStep">{translate key="workflow.review.internalReview"}</span>{else}<span class="futureStep">{translate key="workflow.review.internalReview"}</span>{/if} &#187;
		{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}<span class="pastStep">{translate key="workflow.review.externalReview"}</span>{else}<span class="futureStep">{translate key="workflow.review.externalReview"}</span>{/if} &#187;
		{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EDITING}<span class="pastStep">{translate key="submission.editorial"}</span>{else}<span class="futureStep">{translate key="submission.editorial"}</span>{/if} &#187;
		{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}<span class="pastStep">{translate key="submission.production"}</span>{else}<span class="futureStep">{translate key="submission.production"}</span>{/if}
	</div>
	<div class="pkp_helpers_clear"></div>

	<div class="pkp_workflow_headerBottom">
		<div class="pkp_workflow_headerUserInfo">
			{** FIXME #5734: Leaving blank until we have actual content to display here
			<div id="roundStatus" class="pkp_workflow_headerStatusContainer">
				<span class='icon' ></span><span class="alert">User Alert</span>
			</div>
			<div class="pkp_workflow_headerStageMetadata">
				Stage-specific Metadata <br />
				More-stage Specific metadata
			</div>
			**}
		</div>
		<div class="pkp_workflow_headerStageParticipants">
			{url|assign:stageParticipantGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$monograph->getCurrentStageId() escape=false}
			{load_url_in_div id="stageParticipantGridContainer" url="$stageParticipantGridUrl"}
		</div>
	</div>
</div>
<div class="pkp_helpers_clear"></div>
