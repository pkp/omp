{**
 * header.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header that contains details about the submission
 *}

{assign var="stageId" value=$monograph->getCurrentStageId()}
<div class="submissionHeader">
	<div class="headerTop">
		<div class="heading">
			{if $stageId == 2}
				{translate key="workflow.review.internalReview"}:
			{elseif $stageId == 3}
				{translate key="workflow.review.externalReview"}:
			{/if}
			{assign var="primaryAuthor" value=$monograph->getPrimaryAuthor()}
			{$primaryAuthor->getLastName()} - {$monograph->getLocalizedTitle()}
		</div>

		<div class="action">
			{url|assign:"allParticipantsUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionParticipants.SubmissionParticipantsHandler" op="fetch" stageId=$monograph->getCurrentStageId() monographId=$monograph->getId() escape=false}
			{modal url="$allParticipantsUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.step1.viewAllDetails' button="#allParticipants"}
			<a id="allParticipants"  class="user_list" href="{$metadataUrl}">{translate key="submission.submit.allParticipants"}</a>
		</div>

		<div class="action">
			{url|assign:"metadataUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler" op="fetch" stageId=$monograph->getCurrentStageId() monographId=$monograph->getId() escape=false}
			{modal url="$metadataUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.step1.viewAllDetails' button="#viewMetadata"}
			<a id="viewMetadata" class="more_info" href="{$metadataUrl}">{translate key="submission.submit.metadata"}</a>
		</div>
	</div>
	<div class="clear"></div>

	<div class="headerTimeline">
		{if $stageId > 0}<span class="pastStep">{translate key="submission.submission"}</span>{else}<span class="futureStep">{translate key="submission.submission"}</span>{/if} &#187;
		{if $stageId > 1}<span class="pastStep">{translate key="workflow.review.internalReview"}</span>{else}<span class="futureStep">{translate key="workflow.review.internalReview"}</span>{/if} &#187;
		{if $stageId > 2}<span class="pastStep">{translate key="workflow.review.externalReview"}</span>{else}<span class="futureStep">{translate key="workflow.review.externalReview"}</span>{/if} &#187;
		{if $stageId > 3}<span class="pastStep">{translate key="submission.editorial"}</span>{else}<span class="futureStep">{translate key="submission.editorial"}</span>{/if} &#187;
		{if $stageId > 4}<span class="pastStep">{translate key="submission.production"}</span>{else}<span class="futureStep">{translate key="submission.production"}</span>{/if}
	</div>
	<div class="clear"></div>

	<div class="headerBottom">
		<div class="userInfo">
			{** FIXME #5734: Leaving blank until we have actual content to display here
			<div id="roundStatus" class="statusContainer">
				<span class='icon' ></span><span class="alert">User Alert</span>
			</div>
			<div class="stageMetadata">
				Stage-specific Metadata <br />
				More-stage Specific metadata
			</div>
			**}
		</div>
		<div class="stageParticipants">
			{url|assign:stageParticipantGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$monograph->getCurrentStageId() escape=false}
			{load_url_in_div id="stageParticipantGridContainer" url="$stageParticipantGridUrl"}
		</div>
	</div>
</div>
<div class="clear"></div>