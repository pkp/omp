<!-- templates/controllers/modals/editorDecision/form/promoteForm.tpl -->

{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}
{assign var='randomId' value=1|rand:99999}

{translate|assign:"actionLabelTranslated" key="$actionLabel"}
{assign var=titleTranslated value="$actionLabelTranslated"|concat:": ":$monograph->getLocalizedTitle()}
{modal_title id="#promote-$randomId" keyTranslated=$titleTranslated iconClass="fileManagement" canClose=1}

<script type="text/javascript">
{literal}
$(function() {
	$('.button').button();

	var url = '{/literal}{url op="importPeerReviews" monographId=$monographId}{literal}';
	$('#importPeerReviews-'+{/literal}{$randomId}{literal}).click(function() {
		$.getJSON(url, function(jsonData) {
			if (jsonData.status === true) {
				var currentContent = $("textarea#personalMessage-"+{/literal}{$randomId}{literal}).val();
				$("textarea#personalMessage-"+{/literal}{$randomId}{literal}).val(currentContent + jsonData.content);
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

<form name="promote" id="promote-{$randomId}" method="post" action="{url op="savePromote"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />

	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	<!--  Message to reviewer textarea -->
	<p style="text-align: right;"><a id="importPeerReviews-{$randomId}" href="#">{translate key="submission.comments.importPeerReviews"}</a></p><br />

	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage-$randomId" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.1OF1 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.EditorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer-$randomId" url="$reviewAttachmentsGridUrl"}
	</div>

	<div id="availableFiles">
		{url|assign:newRoundRevisionsUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.revisions.RevisionsGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$round isSelectable=1 escape=false}
		{load_url_in_div id="newRoundRevisionsGrid-$randomId" url=$newRoundRevisionsUrl}
	</div>
</form>

{init_button_bar id="#promote-$randomId" cancelId="#cancelButton-$randomId" submitId="#okButton-$randomId"}
{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton-$randomId" label="common.cancel"}
        {fbvButton id="okButton-$randomId" label="editor.submissionReview.recordDecision" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}
<!-- / templates/controllers/modals/editorDecision/form/promoteForm.tpl -->

