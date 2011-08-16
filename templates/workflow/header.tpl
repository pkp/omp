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
{include file="common/header.tpl"}
{/strip}

<div class="pkp_submissionHeader">
	<div class="pkp_submissionHeaderTop pkp_helpers_text_right">
		<div class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$editMetadataAction}
			{include file="linkAction/linkAction.tpl" action=$submissionInformationCentreAction}

			{** FIXME: This is a huge hack. Just needed an interim solution. **}
			{** Such code would have to go in a Handler, and there should be no inline styles **}
			<script type="text/javascript">
				$(function() {ldelim}
					// Attach the form handler.
					$('#stageParticipantToggle').click(function() {ldelim}
						var $stageParticipants = $(this).next('.pkp_workflow_headerStageParticipants');
						var offset = $(this).offset();
						var width = $(this).width();
						$stageParticipants.toggle();
						$stageParticipants.css({ldelim}left: offset.left + width - 470, top: offset.bottom, width: 500{rdelim});
						return false;
					{rdelim});
				{rdelim});
			</script>
			<a href="#" id="stageParticipantToggle">{translate key="submission.submit.stageParticipants"}</a>
			<div class="pkp_workflow_headerStageParticipants" style="position: absolute; z-index: 1; display:none;">
				{url|assign:stageParticipantGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
				{load_url_in_div id="stageParticipantGridContainer" url="$stageParticipantGridUrl"}
			</div>
		</div>
	</div>

	<div class="pkp_helpers_clear"></div>

	{url|assign:timelineUrl router=$smarty.const.ROUTE_COMPONENT component="timeline.TimelineHandler" op="fetch" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="pkp_submissionTimeline" url="$timelineUrl"}

	<div class="pkp_helpers_clear"></div>

	<div class="pkp_workflow_headerBottom">
		<div class="pkp_workflow_headerUserInfo">
			{include file="controllers/notification/inPlaceNotification.tpl" notificationId="workflowNotification" requestOptions=$workflowNotificationRequestOptions}
		</div>
	</div>
</div>
<div class="pkp_helpers_clear"></div>
