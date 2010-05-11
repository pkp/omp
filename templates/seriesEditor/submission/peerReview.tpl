{**
 * peerReview.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the peer review table.
 *
 * $Id$
 *}
<div id="submission">
<h3>{translate key="manuscript.submission"}</h3>

<script type="text/javascript">
	{literal}
	$(function(){
	    $('#editorReviewFileSelectionForm').ajaxForm({
			dataType: 'json',
			beforeSubmit: function() {
		    	var idArray = [];
				$(".editorReviewFileSelect:checked").each(function() {
				    fullId = $(this).attr('id');
				    id = fullId.substring(7);
				    idArray.push(id);
				    $('#selectedFiles').val(idArray.join(',') );
				    alert($('#selectedFiles').val());
				});
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
	    		// FIXME: Do something
	        }
	    });
	});		
	{/literal}
</script>

{url|assign:editorReviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.editorReviewFileSelection.EditorReviewFileSelectionGridHandler" op="fetchGrid" monographId="5"}
{load_url_in_div id="editorReviewFileSelectionGridContainer" loadMessageId="submission.submissionContributors.form.loadMessage" url="$editorReviewFileSelectionGridUrl"}

<form method="post" id="editorReviewFileSelectionForm">
	<input type="hidden" id="selectedFiles" value="">
	<input type="submit" id="submitFiles" value='{translate key="Approve & Send to Review"}' />
</form>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="monograph.authors"}</td>
		<td width="80%">
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() monographId=$submission->getId()}
			{$submission->getAuthorString()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="monograph.title"}</td>
		<td>{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
	</tr>
	<tr>
		<td class="label">{translate key="submissions.series"}</td>
		<td>{$submission->getSeriesAbbrev()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td class="label">{translate key="user.role.editor"}</td>
		<td>
			{assign var=editAssignments value=$submission->getEditAssignments()}
			{foreach from=$editAssignments item=editAssignment}
				{assign var=emailString value=$editAssignment->getEditorFullName()|concat:" <":$editAssignment->getEditorEmail():">"}
				{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle|strip_tags monographId=$submission->getId()}
				{$editAssignment->getEditorFullName()|escape} {icon name="mail" url=$url}
				{if !$editAssignment->getCanEdit() || !$editAssignment->getCanReview()}
					{if $editAssignment->getCanEdit()}
						({translate key="submission.editing"})
					{else}
						({translate key="submission.review"})
					{/if}
				{/if}
				<br/>
			{foreachelse}
				{translate key="common.noneAssigned"}
			{/foreach}
		</td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="submission.reviewVersion"}</td>
		{if $reviewFile}
			<td width="80%" class="value">
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file">{$reviewFile->getFileName()|escape}</a>&nbsp;&nbsp;
				{$reviewFile->getDateModified()|date_format:$dateFormatShort}{if $currentPress->getSetting('showEnsuringLink')}&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="javascript:openHelp('{get_help_id key="editorial.sectionEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.monograph.ensuringBlindReview"}</a>{/if}
			</td>
		{else}
			<td width="80%" class="nodata">{translate key="common.none"}</td>
		{/if}
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<form method="post" action="{url op="uploadReviewVersion"}" enctype="multipart/form-data">
				{translate key="editor.monograph.uploadReviewVersion"}
				<input type="hidden" name="monographId" value="{$submission->getId()}" />
				{** fbvFileInput id="upload" submit="submit"**}
			</form>
		</td>
	</tr>
</table>

<div class="separator"></div>
</div>

<div id="peerReview">

<table class="data" width="100%">
	<tr valign="middle">
		<td width="22%"><h3>{translate key="workflow.review.internalReview"}</h3></td>
		<td width="14%"><h4>{translate key="submission.round" round=$round}</h4></td>
		<td width="64%" class="nowrap">
			<a href="{url op="selectReviewer" path=$submission->getId()}" class="action">{translate key="editor.monograph.selectReviewer"}</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="{url op="submissionRegrets" path=$submission->getId()}" class="action">{translate|escape key="editor.regrets.link"}</a>
		</td>
	</tr>
</table>
{include file="seriesEditor/submission/reviews.tpl"}

{include file="seriesEditor/submission/editorDecision.tpl"}

</div>
