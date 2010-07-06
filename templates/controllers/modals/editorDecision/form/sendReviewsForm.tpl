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
<script type="text/javascript">
{literal}
$(function() {
	$('.button').button();
{/literal}
</script>
<h2>{translate key="editor.review.sendReviews"}: {$monograph->getLocalizedTitle()}</h2>
<form name="sendReviews" id="sendReviews" method="post" action="{url op="sendReviews"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />
	
	{fbvFormSection}
		{fbvElement type="text" id="authorName" name="authorName" label="user.role.author" value=$authorName disabled=true}
	{/fbvFormSection}

	<!--  Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}
	</form> 
	{** FIXME: this form was copied from reviewAttachments but must be changed **}
	<form name="uploadForm" id="uploadForm-{$randomId}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewAttachments.ReviewAttachmentsGridHandler" op="saveFile" reviewId=$reviewId}" method="post">
		<!-- Max file size of 5 MB -->
		<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
		{fbvFormArea id="file"}
			{if !$attachmentFile}
				{fbvFormSection title="common.file"}
					<input type="file" id="attachment-{$randomId}" name="attachment" />
					<input type="submit" name="attachmentFileSubmit-{$randomId}" value="{translate key="common.upload"}" class="button uploadFile" />
				{/fbvFormSection}
			{else}
				{fbvFormSection title="common.file"}
					{include file="controllers/grid/files/reviewAttachments/form/fileInfo.tpl"}
				{/fbvFormSection}
			{/if}
		{/fbvFormArea}
		<div id="uploadOutput-{$randomId}">
			<div id='loading' class='throbber' style="margin: 0px;"></div>
			<ul><li id='loadingText-{$randomId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
		</div>
	</form>
	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.ReviewAttachmentsGridHandler" op="fetchGrid" readOnly=1 monographId=$monographId escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	</div>
