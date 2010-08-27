<!-- templates/controllers/modals/editorDecision/form/sendReviewsForm.tpl -->

{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

{assign var='uniqueId' value=""|uniqid}

{translate|assign:"actionLabelTranslated" key="$actionLabel"}
{assign var=titleTranslated value="$actionLabelTranslated"|concat:": ":$monograph->getLocalizedTitle()}
{modal_title id="#sendReviews-$uniqueId" keyTranslated=$titleTranslated iconClass="fileManagement" canClose=1}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		var url = '{/literal}{url op="importPeerReviews" monographId=$monographId}{literal}';
		$('#importPeerReviews-'+{/literal}{$uniqueId}{literal}).click(function() {
			$.getJSON(url, function(jsonData) {
				if (jsonData.status === true) {
					var currentContent = $("textarea#personalMessage-"+{/literal}{$uniqueId}{literal}).val();
					$("textarea#personalMessage-"+{/literal}{$uniqueId}{literal}).val(currentContent + jsonData.content);
				} else {
					// Alert that the modal failed
					alert(jsonData.content);
				}
			});
			return false;
		});
	});
	{/literal}
</script>

<form name="sendReviews" id="sendReviews-{$uniqueId}" method="post" action="{url op="saveSendReviews"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	<!--  Message to reviewer textarea -->
	<p style="text-align: right;"><a id="importPeerReviews-{$uniqueId}" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />

	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage-$uniqueId" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.1OF1 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.EditorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer-$uniqueId" url="$reviewAttachmentsGridUrl"}
	</div>

</form>

{init_button_bar id="#sendReviews-$uniqueId" cancelId="#cancelButton-$uniqueId" submitId="#okButton-$uniqueId"}
{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton-$uniqueId" label="common.cancel"}
        {fbvButton id="okButton-$uniqueId" label="editor.submissionReview.recordDecision" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}
<!-- / templates/controllers/modals/editorDecision/form/sendReviewsForm.tpl -->

