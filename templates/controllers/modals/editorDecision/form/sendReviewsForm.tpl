{**
 * templates/controllers/modals/editorDecision/form/sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		$('#sendReviews').pkpHandler(
			'$.pkp.controllers.modals.editorDecision.form.EditorDecisionFormHandler',
			{ldelim} peerReviewUrl: '{$peerReviewUrl|escape:javascript}' {rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="sendReviews" method="post" action="{url op="saveSendReviews"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}


	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		<p class="pkp_helpers_text_right"><a id="importPeerReviews" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />
	{/if}

	<!-- Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToAuthor" value=$personalMessage}
	{/fbvFormSection}

	{** Some decisions can be made before review is initiated (i.e. no attachments). **}
	{if $round}
		<div id="attachments">
			{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.attachment.EditorSelectableReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId round=$round escape=false}
			{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
		</div>
	{/if}

	{fbvFormButtons submitText="editor.submissionReview.recordDecision"}
</form>


