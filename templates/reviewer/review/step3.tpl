{**
 * templates/reviewer/review/step3.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
		$('#reviewStep3Form').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="reviewStep3Form" method="post" action="{url op="saveStep" path=$submission->getId() step="3"}">
	{include file="common/formErrors.tpl"}
{fbvFormArea id="reviewStep3"}
	{fbvFormSection label="common.download"}
		{url|assign:reviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.ReviewerReviewFilesGridHandler" op="fetchGrid" monographId=$submission->getId() stageId=$reviewAssignment->getStageId() reviewRoundId=$reviewRoundId escape=false}
		{load_url_in_div id="reviewFiles" url=$reviewFilesGridUrl}
	{/fbvFormSection}

	{fbvFormSection label="submission.review"}
		{if $viewGuidelinesAction}
			<div id="viewGuidelines" class="pkp_helpers_align_right">
				{include file="linkAction/linkAction.tpl" action=$viewGuidelinesAction contextId="viewGuidelines"}
			</div>
		{/if}
		{fbvElement type="textarea" id="comments" name="comments" required=true value=$reviewAssignment->getComments()|escape disabled=$reviewIsComplete}

		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.ReviewerReviewAttachmentsGridHandler" op="fetchGrid" assocType=$smarty.const.ASSOC_TYPE_REVIEW_ASSIGNMENT assocId=$submission->getReviewId() monographId=$submission->getId() stageId=$submission->getStageId() reviewIsComplete=$reviewIsComplete escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	{/fbvFormSection}

	{url|assign:cancelUrl page="reviewer" op="submission" path=$submission->getId() step=2 escape=false}
	{fbvFormButtons submitText="reviewer.monograph.submitReview" confirmSubmit="reviewer.confirmSubmit" cancelText="navigation.goBack" cancelUrl=$cancelUrl submitDisabled=$reviewIsComplete}
{/fbvFormArea}
</form>
{include file="common/footer.tpl"}


