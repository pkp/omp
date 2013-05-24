{**
 * templates/authorDashboard/authorDashboard.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the author dashboard.
 *}
{strip}
{assign var=primaryAuthor value=$submission->getPrimaryAuthor()}
{if !$primaryAuthor}
	{assign var=authors value=$submission->getAuthors()}
	{assign var=primaryAuthor value=$authors[0]}
{/if}
{assign var="pageTitleTranslated" value=$primaryAuthor->getLastName()|concat:", <em>":$submission->getLocalizedTitle():"</em>"|truncate:50}
{include file="common/header.tpl" suppressPageTitle=true}
{/strip}

{assign var="stageId" value=$submission->getStageId()}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#authorDashboard').pkpHandler(
				'$.pkp.pages.authorDashboard.AuthorDashboardHandler',
				{ldelim} currentStage: {$stageId} {rdelim});
	{rdelim});
</script>

<div id="authorDashboard">
	<div id="submissionHeader" class="pkp_page_header">
		<div class="pkp_helpers_align_right">
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
		<div class="pkp_helpers_align_left"><span class="h2">{$pageTitleTranslated}</span></div>
		<div class="pkp_helpers_clear"></div>
		
	<p class="pkp_help">{translate key="submission.authorDashboard.description"}</p>
		<br />
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="authorDashboardNotification" requestOptions=$authorDashboardNotificationRequestOptions}
	</div>
	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_SUBMISSION, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="submission">
			<h3><a href="#">{translate key='submission.submission'}</a></h3>
			<div id="submissionContent">
				{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.AuthorSubmissionDetailsFilesGridHandler" op="fetchGrid" submissionId=$submission->getId()}
				{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="internalReview">
			<h3><a href="#">{translate key='workflow.review.internalReview'}</a></h3>
			<div id="internalReviewContent">
				{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW && !$internalReviewRounds->wasEmpty()}
					{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$internalReviewRounds reviewRoundTabsId="internalReviewRoundTabs" lastReviewRoundNumber=$lastReviewRoundNumber.internalReview}
				{else}
					{translate key="submission.stageNotInitiated"}
				{/if}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="externalReview">
			<h3><a href="#">{translate key='workflow.review.externalReview'}</a></h3>
			<div id="externalReviewContent">
				{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW && !$externalReviewRounds->wasEmpty()}
					{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$externalReviewRounds reviewRoundTabsId="externalReviewRoundTabs" lastReviewRoundNumber=$lastReviewRoundNumber.externalReview}
				{else}
					{translate key="submission.stageNotInitiated"}
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
					{include file="authorDashboard/submissionEmails.tpl" submissionEmails=$copyeditingEmails}

					<!-- Display copyediting files grid -->
					{url|assign:copyeditingFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.AuthorCopyeditingSignoffFilesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_EDITING escape=false}
					{load_url_in_div id="copyeditingFilesGridDiv" url=$copyeditingFilesGridUrl}
				{else}
					{translate key="submission.stageNotInitiated"}
				{/if}
			</div>
		</div>
	{/if}

	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_PRODUCTION, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="production">
			<h3><a href="#">{translate key='submission.production'}</a></h3>
			<div id="productionContent">
				{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}
					{include file="authorDashboard/submissionEmails.tpl" submissionEmails=$productionEmails}

					<!-- Display production files grid -->
					{url|assign:productionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.AuthorProofingSignoffFilesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_EDITING escape=false}
					{load_url_in_div id="productionFilesGridDiv" url=$productionFilesGridUrl}
				{else}
					{translate key="submission.stageNotInitiated"}
				{/if}
			</div>
		</div>
	{/if}
	
	{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_SUBMISSION, $accessibleWorkflowStages)}
		<div class="pkp_authorDashboard_stageContainer" id="documents">
			<h3><a href="#">{translate key='submission.documents'}</a></h3>
			<div id="documentsContent">
				{url|assign:documentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionDocuments.SubmissionDocumentsFilesGridHandler" op="fetchGrid" submissionId=$submission->getId()}
				{load_url_in_div id="documentsGridDiv" url=$documentsGridUrl}
			</div>
		</div>
	{/if}
</div>

{include file="common/footer.tpl"}
