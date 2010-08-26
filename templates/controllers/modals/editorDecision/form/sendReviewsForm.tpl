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

{assign var='timeStamp' value=$smarty.now}

{translate|assign:"actionLabelTranslated" key="$actionLabel"}
{assign var=titleTranslated value="$actionLabelTranslated"|concat:": ":$monograph->getLocalizedTitle()}
{modal_title id="#sendReviews-$timeStamp" keyTranslated=$titleTranslated iconClass="fileManagement" canClose=1}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		var url = '{/literal}{url op="importPeerReviews" monographId=$monographId}{literal}';
		$('#importPeerReviews-'+{/literal}{$timeStamp}{literal}).click(function() {
			$.getJSON(url, function(jsonData) {
				if (jsonData.status === true) {
					var currentContent = $("textarea#personalMessage-"+{/literal}{$timeStamp}{literal}).val();
					$("textarea#personalMessage-"+{/literal}{$timeStamp}{literal}).val(currentContent + jsonData.content);
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

<form name="sendReviews" id="sendReviews-{$timeStamp}" method="post" action="{url op="saveSendReviews"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	<!--  Message to reviewer textarea -->
	<p style="text-align: right;"><a id="importPeerReviews-{$timeStamp}" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />

	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage-$timeStamp" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.1OF1 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.EditorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer-$timeStamp" url="$reviewAttachmentsGridUrl"}
	</div>

</form>

{init_button_bar id="#sendReviews-$timeStamp" cancelId="#cancelButton-$timeStamp" submitId="#okButton-$timeStamp"}
{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton-$timeStamp" label="common.cancel"}
        {fbvButton id="okButton-$timeStamp" label="editor.submissionReview.recordDecision" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}
<!-- / templates/controllers/modals/editorDecision/form/sendReviewsForm.tpl -->

