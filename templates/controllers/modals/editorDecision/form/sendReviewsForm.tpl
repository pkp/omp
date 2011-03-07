{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

<script type="text/javascript">
	<!--
	{literal}
	$(function() {
		$('#sendReviews').pkpHandler('$.pkp.controllers.form.FormHandler');
		var url = '{/literal}{url op="importPeerReviews" monographId=$monographId}{literal}';
		$('#importPeerReviews').live('click', function() {
			$.getJSON(url, function(jsonData) {
				if (jsonData.status === true) {
					var currentContent = $("textarea#personalMessage").val();
					$("textarea#personalMessage").val(currentContent + jsonData.content);
				} else {
					// Alert that the modal failed
					alert(jsonData.content);
				}
			});
			return false;
		});
	});
	{/literal}
	// -->
</script>

<form id="sendReviews" method="post" action="{url op="saveSendReviews"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	<!-- Message to reviewer textarea -->
	<p class="text_right"><a id="importPeerReviews" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />

	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToAuthor" value=$personalMessage measure=$fbvStyles.measure.1OF1 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.attachment.EditorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	</div>

	{include file="form/formButtons.tpl" submitText="editor.submissionReview.recordDecision"}
</form>


