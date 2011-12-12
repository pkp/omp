{**
 * templates/authorDashboard/authorDashboard.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the author dashboard.
 *}
{strip}
{assign var="pageTitleTranslated" value=$monograph->getLocalizedTitle()|concat:" - "|concat:$monograph->getAuthorString(true)}
{include file="common/header.tpl" suppressPageTitle=true}
{/strip}

{assign var="stageId" value=$monograph->getStageId()}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#authorDashboard').pkpHandler(
				'$.pkp.pages.authorDashboard.AuthorDashboardHandler',
				{ldelim} currentStage: {$stageId} {rdelim});
	{rdelim});
</script>

<div id="authorDashboard">
	<div id="submissionHeader" class="pkp_submission_header">
		<div style="float:right;">
			<ul class="submission_actions pkp_helpers_flatlist">
				{if $uploadFileAction}
					<li id="{$uploadFileAction->getId()}">
						{include file="linkAction/linkAction.tpl" action=$uploadFileAction contextId="authorDashboard"}
					</li>
				{/if}
				<li id="{$viewMetadataAction->getId()}">
					{include file="linkAction/linkAction.tpl" action=$viewMetadataAction contextId="authorDashboard"}
				</li>
			</ul>
		</div>
		<div style="float: left;"><span class="h2">{$pageTitleTranslated}</span></div>
		
		<div class="pkp_helpers_clear"></div>
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="authorDashboardNotification" requestOptions=$authorDashboardNotificationRequestOptions}
	</div>
	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_SUBMISSION, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="submission">
			<h3><a href="#">{translate key='submission.submission'}</a></h3>
			<div id="submissionContent">
				{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.AuthorSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
				{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="internalReview">
			<h3><a href="#">{translate key='workflow.review.internalReview'}</a></h3>
			<div id="internalReviewContent">
				{if !$internalReviewRounds->wasEmpty()}
					<div id="internalReviewRoundTabs">
						<ul>
							{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$internalReviewRounds} 
						</ul>
					</div>
				{/if}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="externalReview">
			<h3><a href="#">{translate key='workflow.review.externalReview'}</a></h3>
			<div id="externalReviewContent">
				{if !$externalReviewRounds->wasEmpty()}
					<div id="externalReviewRoundTabs">
						<ul>
							{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$externalReviewRounds}
						</ul>
					</div>
				{/if}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_EDITING, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="copyediting">
			<h3><a href="#">{translate key='submission.copyediting'}</a></h3>
			<div id="copyeditingContent">
				{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EDITING}
					<!-- Display editor's message to the author -->
					{if $monographEmails}
						<h6>{translate key="editor.review.personalMessageFromEditor"}:</h6>
						{iterate from=monographEmails item=monographEmail}
							<textarea class="pkp_authorDashboard_editorPersonalMessage textArea" disabled=true>{$monographEmail->getBody()|escape}</textarea>
						{/iterate}
						<br />
					{/if}
	
					<!-- Display copyediting files grid -->
					{url|assign:copyeditingFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.AuthorCopyeditingSignoffFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_EDITING escape=false}
					{load_url_in_div id="copyeditingFilesGridDiv" url=$copyeditingFilesGridUrl}
				{/if}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_PRODUCTION, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="production">
			<h3><a href="#">{translate key='submission.production'}</a></h3>
			<div id="productionContent">&nbsp;</div>
		</div>
	{/if}
</div>

{include file="common/footer.tpl"}
