<!-- templates/controllers/grid/settings/library/form/fileForm.tpl -->

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
{assign var='timeStamp' value=$smarty.now}
{modal_title id="#metadataForm-$timeStamp" key='settings.setup.addItem' iconClass="fileManagement"}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#uploadForm-{/literal}{$timeStamp}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
		// Handle upload form
	    $('#uploadForm-{/literal}{$timeStamp}{literal}').ajaxForm({
	        target: '#uploadOutput-{/literal}{$timeStamp}{literal}',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText-{/literal}{$timeStamp}{literal}').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#libraryFile-{/literal}{$timeStamp}{literal}').attr("disabled", "disabled");
	    			$('#libraryFileSubmit-{/literal}{$timeStamp}{literal}').button("option", "disabled", true);
	    			$("#continueButton-{/literal}{$timeStamp}{literal}").button( "option", "disabled", false);
		    		$('#deleteUrl-{/literal}{$timeStamp}{literal}').val(returnString.deleteUrl);
	    			$("#metadataRowId-{/literal}{$timeStamp}{literal}").val(returnString.elementId);
	    		}
	    		$('#loadingText-{/literal}{$timeStamp}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });
		// Handle metadata form
	    $('#metadataForm-{/literal}{$timeStamp}{literal}').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		newFile = $('#newFile-{/literal}{$timeStamp}{literal}').val();
		    		if(newFile != undefined && newFile != "") {
						actType = 'append';
		    		} else {
						actType = 'replace';
		    		}
	    			updateItem(actType, '#component-'+'{/literal}{$gridId}{literal}'+'-table>tbody:first', returnString.content);
	    			$('#uploadForm-{/literal}{$timeStamp}{literal}').parent().dialog('close');
	    		}
	    		$('#loadingText-{/literal}{$timeStamp}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$timeStamp}{literal}").click(function() {
			validator = $('#metadataForm-{/literal}{$timeStamp}{literal}').validate();
			if($('#metadataForm-{/literal}{$timeStamp}{literal}').valid()) {
				$('#metadataForm-{/literal}{$timeStamp}{literal}').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});

		$("#cancelButton-{/literal}{$timeStamp}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile-{/literal}{$timeStamp}{literal}').val();
			deleteUrl = $('#deleteUrl-{/literal}{$timeStamp}{literal}').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm-{/literal}{$timeStamp}{literal}').parent().dialog('close');
			return false;
		});

	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$timeStamp}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType}" method="post">
	<!-- Max file size of 5 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	{fbvFormArea id="file"}
		{if !$libraryFile}
			{fbvFormSection title="common.file"}
				<input type="file" id="libraryFile" name="libraryFile" />
				<input type="submit" id="libraryFileSubmit-{$timeStamp}" name="submitFile" value="{translate key="common.upload"}" class="button" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput-{$timeStamp}">
		<div id='loading' class='throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText-{$timeStamp}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>
</form>


<form name="metadataForm" id="metadataForm-{$timeStamp}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="saveMetadata"}" method="post">
	<input type="hidden" id="metadataRowId-{$timeStamp}" name="rowId" value="{$rowId|escape}" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="name" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	{init_button_bar id="#buttons" cancelId="#cancelButton2-$timeStamp" submitId="#continueButton2-$timeStamp"}
	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvLink id="cancelButton-$timeStamp" label="common.cancel" float=$fbvStyles.float.LEFT}
			{if !$rowId}{assign var="buttonDisabled" value="disabled"}{/if}
			{fbvButton id="continueButton-$timeStamp" label="common.saveAndClose" disabled=$buttonDisabled align=$fbvStyles.align.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>


{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl-{$timeStamp}" value="" />
<input type="hidden" id="newFile-{$timeStamp}" value="{$newFile}" />


<!-- / templates/controllers/grid/settings/library/form/fileForm.tpl -->

