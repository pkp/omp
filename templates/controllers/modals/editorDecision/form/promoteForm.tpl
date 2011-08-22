{**
 * templates/controllers/modals/editorDecision/form/promoteForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		$('#promote').pkpHandler(
			'$.pkp.controllers.modals.editorDecision.form.EditorDecisionFormHandler',
			{ldelim} peerReviewUrl: '{$peerReviewUrl|escape:javascript}' {rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="promote" method="post" action="{url op="savePromote"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		<!--  Message to reviewer textarea -->
		<p style="pkp_helper_align_right"><a id="importPeerReviews" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />
	{/if}

	{** FIXME: we're using the PromoteForm for send to production, but there
	 *	is no email for that action so should probably not use this template. **}
	{if $stageId < $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		{fbvFormSection}
			{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToAuthor" value=$personalMessage  size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/if}

	{** Some decisions can be made before review is initiated (i.e. no attachments). **}
	{if $round}
		<div id="attachments">
			{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.attachment.EditorSelectableReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId round=$round escape=false}
			{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
		</div>
	{/if}

	<div id="availableFiles">
		{* Show a different grid depending on whether we're in review or before the review stage *}
		{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_SUBMISSION}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SelectableSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
		{elseif $round}
			{** a set $round var implies we are INTERNAL_REVIEW or EXTERNAL_REVIEW **}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.SelectableReviewRevisionsGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId round=$round escape=false}
		{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EDITING}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopy.SelectableFairCopyFilesGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
		{/if}
		{load_url_in_div id="filesToPromoteGrid" url=$filesToPromoteGridUrl}
	</div>
	{fbvFormButtons submitText="editor.submissionReview.recordDecision"}
</form>


