{**
 * templates/reviewer/review/step3.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the step 3 review page
 *}
{strip}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#review').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="reviewStep3Form" method="post" action="{url op="saveStep" path=$submission->getId() step="3"}">
	{include file="common/formErrors.tpl"}
{fbvFormArea id="reviewStep3"}
	{fbvFormSection label="common.download"}
		{url|assign:reviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.ReviewerReviewFilesGridHandler" op="fetchGrid" monographId=$submission->getId() stageId=$submission->getStageId() round=$submission->getCurrentRound() escape=false}
		{load_url_in_div id="reviewFiles" url=$reviewFilesGridUrl}
	{/fbvFormSection}

	{fbvFormSection label="submission.review"}
		{if $viewGuidelinesAction}
			<div id="viewGuidelines" class="pkp_helpers_align_right">
				{include file="linkAction/linkAction.tpl" action=$viewGuidelinesAction contextId="viewGuidelines"}
			</div>
		{/if}
		{fbvElement type="textarea" id="comments" name="comments" value=$reviewAssignment->getComments()|escape}

		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.ReviewerReviewAttachmentsGridHandler" op="fetchGrid" reviewId=$submission->getReviewId() monographId=$submission->getId() round=1 escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	{/fbvFormSection}

	{fbvFormButtons submitText="reviewer.monograph.continueToStepFour" confirmSubmit="reviewer.confirmSubmit" cancelText="navigation.goBack"}
{/fbvFormArea}
</form>
{include file="common/footer.tpl"}


