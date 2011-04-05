{**
 * authorDashboard.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the author dashboard.
 *}

{strip}
{include file="common/header.tpl"}
{/strip}
{assign var="stageId" value=$monograph->getCurrentStageId()}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#authorDashboard').pkpHandler(
				'$.pkp.pages.authorDashboard.AuthorDashboardHandler',
				{ldelim} currentStage: {$stageId} {rdelim});
	{rdelim});
</script>

<div id="authorDashboard">
	<div class="pkp_submissionHeader">
		<div class="pkp_submissionHeaderTop">
			{include file="common/submissionHeader.tpl" stageId=$stageId monograph=$monograph}
		</div>
	</div>
	<div style="clear:both;"></div>

	{** Determine the amount the progress bar should be filled **}
	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}{assign var="fillerClass" value="accepted"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EDITING}{assign var="fillerClass" value="reviewed"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}{assign var="fillerClass" value="copyedited"}
	{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_PUBLISHED}{assign var="fillerClass" value="published"}
	{else}{assign var="fillerClass" value=""}{/if}
	<div id="authorTimeline" class="pkp_submissionTimeline">
		<div id="timelineContainer" class="pkp_authorDashboard_timelineContainer">
			<div id="timelineFiller" class="{$fillerClass|escape}"></div>
		</div>
		<div id="timelineLabelContainer" class="pkp_authorDashboard_timelineLabelContainer">
			<span class="pkp_authorDashboard_timelineLabel {if $stageId > 0}pastStep{else}futureStep{/if}">{translate key="submissions.submitted"}</span>
			<span class="pkp_authorDashboard_timelineLabel {if $stageId > $smarty.const.WORKFLOW_STAGE_ID_SUBMISSION}pastStep{else}futureStep{/if}">{translate key="submission.accepted"}</span>
			<span class="pkp_authorDashboard_timelineLabel center {if $stageId > $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}pastStep{else}futureStep{/if}">{translate key="submission.reviewed"}</span>
			<span class="pkp_authorDashboard_timelineLabel right {if $stageId > $smarty.const.WORKFLOW_STAGE_ID_EDITING}pastStep{else}futureStep{/if}">{translate key="submission.copyedited"}</span>
			<span class="pkp_authorDashboard_timelineLabel right {if $stageId == 0}pastStep{else}futureStep{/if}">{translate key="navigation.published"}</span>
		</div>
	</div>
	<div style="clear:both;"></div>

	{** User Alert **}
	<div id="userAlert"></div>
	<br />

	<!-- Author actions -->
	<div id="authorActions" class="pkp_linkActions">
		{if $uploadFileAction}
			<div id="{$uploadFileAction->getId()}" class="pkp_authorDashboard_authorAction">
				{include file="linkAction/linkAction.tpl" action=$uploadFileAction contextId="authorDashboard"}
			</div>
		{/if}
		<div id="viewMetadata" class="pkp_authorDashboard_authorAction">
			{include file="linkAction/linkAction.tpl" action=$viewMetadataAction contextId="authorDashboard"}
		</div>
	</div>
	<div style="clear:both;"></div>

	<div class="pkp_authorDashboard_stageContainer" id="submission">
		<h3><a href="#">{translate key='submission.submission'}</a></h3>
		<div id="submissionContent">
			{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.AuthorSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
			{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}
		</div>
	</div>

	<div class="pkp_authorDashboard_stageContainer" id="review">
		<h3><a href="#">{translate key='submission.review'}</a></h3>
		<div id="reviewContent">
			{if $stageId > $smarty.const.WORKFLOW_STAGE_ID_SUBMISSION}
				{assign var="currentReviewRound" value=$monograph->getCurrentRound()}

				<div id="reviewRoundTabs">
					<ul>
						{foreach from=$rounds item=round}
							<li><a href="{url op="reviewRoundInfo" round=$round monographId=$monograph->getId() escape=false}">{translate key="submission.round" round=$round}</a></li>
						{/foreach}
					</ul>
				</div>

			{/if}
		</div>
	</div>

	<div class="pkp_authorDashboard_stageContainer" id="copyediting">
		<h3><a href="#">{translate key='submission.copyediting'}</a></h3>
		<div id="copyeditingContent">
			<!-- Display editor's message to the author -->
			{if $monographEmails}
				<h6>{translate key="editor.review.personalMessageFromEditor"}:</h6>
				{iterate from=monographEmails item=monographEmail}
					<textarea class="pkp_authorDashboard_editorPersonalMessage" disabled=true class="textArea">{$monographEmail->getBody()|escape}</textarea>
				{/iterate}
				<br />
			{/if}

			<!-- Display copyediting files grid -->
			{if $showCopyeditingFiles}
				{url|assign:copyeditingFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.AuthorCopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
				{load_url_in_div id="copyeditingFilesGridDiv" url=$copyeditingFilesGridUrl}
			{/if}
		</div>
	</div>

	<div class="pkp_authorDashboard_stageContainer" id="production">
		<h3><a href="#">{translate key='submission.production'}</a></h3>
		<div id="productionContent">&nbsp;</div>
	</div>
</div>

{include file="common/footer.tpl"}
