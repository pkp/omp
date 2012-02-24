{**
 * templates/workflow/header.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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

<div id="submissionHeader" class="pkp_page_header">
	<div class="participant_popover" style="display: none;">
		{url|assign:stageParticipantGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.stageParticipant.StageParticipantGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
		{load_url_in_div id="stageParticipantGridContainer" class="update_source" url="$stageParticipantGridUrl"}
	</div>
	<div class="pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist">
			{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
				<li>{include file="linkAction/linkAction.tpl" action=$catalogEntryAction}</li>
			{/if}
			<li>{include file="linkAction/linkAction.tpl" action=$submissionInformationCentreAction}</li>
			<li class="participants"><a href="#" id="participantToggle" class="sprite participants">{translate key="editor.monograph.stageParticipants"}</a></li>
		</ul>
	</div>
	<div class="pkp_helpers_align_left"><span class="h2">{$pageTitleTranslated}</span></div>
	<div class="submission_progress_wrapper">
		<ul class="submission_progress pkp_helpers_flatlist">
			{foreach key=key from=$workflowStages item=stage}
				{assign var="progressClass" value=""}
				{if $key == $monographStageId}
					{assign var="progressClass" value="current"}
				{/if}
				{if $key < $monographStageId || $monographStageId === (int)$smarty.const.WORKFLOW_STAGE_ID_PUBLISHED}
					{if $key > (int) $smarty.const.WORKFLOW_STAGE_ID_PUBLISHED}
						{assign var="progressClass" value="complete"}
					{/if}
				{/if}
				<li class="{$progressClass}">
					{if array_key_exists($key, $accessibleWorkflowStages)}
						<a class="sprite" href="{url router=$smarty.const.ROUTE_PAGE page="workflow" op=$stage.path path=$monograph->getId()}">{translate key=$stage.translationKey}</a>
					{else}
						<a class="sprite pkp_common_disabled">{translate key=$stage.translationKey}</a>
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
</div>
{include file="controllers/notification/inPlaceNotification.tpl" notificationId="workflowNotification" requestOptions=$workflowNotificationRequestOptions}
<br />
<div class="pkp_helpers_clear"></div>
