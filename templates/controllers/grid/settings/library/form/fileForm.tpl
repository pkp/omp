{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Library Files grid form
 *
 * $Id$
 *}
<!--  Need a random ID to give to modal elements so that they are unique in the DOM (can not use
		fileId like elsewhere in the modal, because there may not be an associated file yet-->
{assign var='randomId' value=1|rand:99999}

<script type="text/javascript">
	{literal}
	$(function() {
		$('#uploadForm-{/literal}{$randomId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
		$(".ui-dialog-titlebar-close").remove();  // Hide 'X' close button in dialog
		// Handle upload form
	    $('#uploadForm-{/literal}{$randomId}{literal}').ajaxForm({
	        target: '#uploadOutput-{/literal}{$randomId}{literal}',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText-{/literal}{$randomId}{literal}').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#libraryFile-{/literal}{$randomId}{literal}').attr("disabled", "disabled");
	    			$('#libraryFileSubmit-{/literal}{$randomId}{literal}').attr("disabled", "disabled");
	    			$("#continueButton-{/literal}{$randomId}{literal}").removeAttr("disabled");
		    		$('#deleteUrl-{/literal}{$randomId}{literal}').val(returnString.deleteUrl);
	    			$("#metadataRowId-{/literal}{$randomId}{literal}").val(returnString.elementId);
	    		}
	    		$('#loadingText-{/literal}{$randomId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });
		// Handle metadata form
	    $('#metadataForm-{/literal}{$randomId}{literal}').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		newFile = $('#newFile-{/literal}{$randomId}{literal}').val();
		    		if(newFile != undefined && newFile != "") {
						actType = 'append';
		    		} else {
						actType = 'update';
		    		}
	    			updateItem(actType, '#component-'+'{/literal}{$gridId}{literal}'+'-table', returnString.content);
	    			$('#uploadForm-{/literal}{$randomId}{literal}').parent().dialog('close');
	    		}
	    		$('#loadingText-{/literal}{$randomId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$randomId}{literal}").click(function() {
			validator = $('#metadataForm-{/literal}{$randomId}{literal}').validate();
			if($('#metadataForm-{/literal}{$randomId}{literal}').valid()) {
				$('#metadataForm-{/literal}{$randomId}{literal}').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});

		$("#cancelButton-{/literal}{$randomId}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile-{/literal}{$randomId}{literal}').val();
			deleteUrl = $('#deleteUrl-{/literal}{$randomId}{literal}').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm-{/literal}{$randomId}{literal}').parent().dialog('close');
		});

	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$randomId}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType}" method="post">
	<!-- Max file size of 5 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	{fbvFormArea id="file"}
		{if !$libraryFile}
			{fbvFormSection title="common.file"}
				{fbvFileInput id="libraryFile" submit="libraryFileSubmit-$randomId"}
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput-{$randomId}">
		<div id='loading' class='throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText-{$randomId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>
</form>


<form name="metadataForm" id="metadataForm-{$randomId}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="saveMetadata"}" method="post">
	<input type="hidden" id="metadataRowId-{$randomId}" name="rowId" value="{$rowId|escape}" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="name" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvButton id="cancelButton-$randomId" label="common.cancel" float=$fbvStyles.float.LEFT}
			{if !$rowId}{assign var="buttonDisabled" value="disabled"}{/if}
			{fbvButton id="continueButton-$randomId" label="common.saveAndClose" disabled=$buttonDisabled float=$fbvStyles.float.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>

{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl-{$randomId}" value="" />
<input type="hidden" id="newFile-{$randomId}" value="{$newFile}" />

