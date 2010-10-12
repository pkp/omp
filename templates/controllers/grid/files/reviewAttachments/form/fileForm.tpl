{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Attachment Files grid form
 *
 * $Id$
 *}
<!--  Need a random ID to give to modal elements so that they are unique in the DOM (can not use
		fileId like elsewhere in the modal, because there may not be an associated file yet-->
{modal_title id="#uploadForm" key='grid.reviewAttachments.add' iconClass="fileManagement" canClose=1}

<script type="text/javascript">
	{literal}
	$(function() {
		// Handle upload form
	    $('#uploadForm').ajaxForm({
	        target: '#uploadOutput',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the continue button
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#attachment').attr("disabled", "disabled");
	    			$('#attachmentFileSubmit').button("option", "disabled", true);
	    			$("#submitModalButton").button("option", "disabled", false);
		    		$('#deleteUrl').val(returnString.deleteUrl);
		    		$('#saveUrl').val(returnString.saveUrl);
	    		}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#submitModalButton").click(function() {
			saveAndUpdate($('#saveUrl').val(),
    	    		'append',
    	    		'#component-{/literal}{$gridId}{literal}-table',
    	    		'#uploadForm'
			);
		});

		$("#cancelModalButton").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile').val();
			deleteUrl = $('#deleteUrl').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm').parent().dialog('close');
		});
	});
	{/literal}
</script>

<form name="uploadForm" id="uploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveFile" monographId=$monographId reviewId=$reviewId escape=false}" method="post">
	<!-- Max file size of 20 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="20971520" />
	{fbvFormArea id="file"}
		{if !$attachmentFile}
			{fbvFormSection title="common.file"}
				<input type="file" id="attachment" name="attachment" />
				<input type="submit" name="attachmentFileSubmit" value="{translate key="common.upload"}" class="button uploadFile" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/files/reviewAttachments/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput">
		<div id='loading' class='throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>

	{if !$rowId}{assign var="buttonDisabled" value="true"}{/if}
	{init_button_bar id="#uploadForm" submitText="common.saveAndClose" submitDisabled=$buttonDisabled}
</form>

{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl" value="" />
<input type="hidden" id="saveUrl" value="" />
<input type="hidden" id="newFile" value="{$newFile}" />


