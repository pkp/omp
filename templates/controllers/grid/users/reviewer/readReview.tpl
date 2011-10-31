{**
 * templates/controllers/grid/users/reviewer/readReview.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Screen to let user read a review.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#readReviewForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="readReviewForm" method="post" action="{url op="reviewRead"}">
	{fbvFormArea id="readReview"}
		<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignment->getId()|escape}" />
		<input type="hidden" name="monographId" value="{$reviewAssignment->getSubmissionId()|escape}" />
		<input type="hidden" name="stageId" value="{$reviewAssignment->getStageId()|escape}" />

		{fbvFormSection}
			{fbvElement type="text" id="reviewer" inline=true size=$fbvStyles.size.MEDIUM label="user.role.reviewer" value=$reviewAssignment->getReviewerFullName() disabled=true}
			{fbvElement type="text" id="reviewCompleted" inline=true size=$fbvStyles.size.MEDIUM label="editor.review.reviewCompleted" value=$reviewAssignment->getDateCompleted() disabled=true}
		{/fbvFormSection}

		{if $reviewAssignment->getReviewFormId()}
			{** FIXME: add review forms **}
		{else}
			{fbvFormSection}
				{fbvElement type="textarea" id="reviewCompleted" label="editor.review.reviewerComments" value=$reviewerComment->getComments() disabled=true}
			{/fbvFormSection}
		{/if}
		{fbvFormSection}
			{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.EditorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() reviewId=$reviewAssignment->getId() stageId=$reviewAssignment->getStageId() escape=false}
			{load_url_in_div id="readReviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
		{/fbvFormSection}
		{fbvFormButtons id="closeButton" hideCancel=true submitText="common.close"}
	{/fbvFormArea}
</form>