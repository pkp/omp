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
{assign var='uniqueId' value=""|uniqid}
{modal_title id="#metadataForm-$uniqueId" key='settings.setup.addItem' iconClass="fileManagement"}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#uploadForm-{/literal}{$uniqueId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
		// Handle upload form
	    $('#uploadForm-{/literal}{$uniqueId}{literal}').ajaxForm({
	        target: '#uploadOutput-{/literal}{$uniqueId}{literal}',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText-{/literal}{$uniqueId}{literal}').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#libraryFile-{/literal}{$uniqueId}{literal}').attr("disabled", "disabled");
	    			$('#libraryFileSubmit-{/literal}{$uniqueId}{literal}').button("option", "disabled", true);
	    			$("#continueButton-{/literal}{$uniqueId}{literal}").button( "option", "disabled", false);
		    		$('#deleteUrl-{/literal}{$uniqueId}{literal}').val(returnString.deleteUrl);
	    			$("#metadataRowId-{/literal}{$uniqueId}{literal}").val(returnString.elementId);
	    		}
	    		$('#loadingText-{/literal}{$uniqueId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });
		// Handle metadata form
	    $('#metadataForm-{/literal}{$uniqueId}{literal}').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		newFile = $('#newFile-{/literal}{$uniqueId}{literal}').val();
		    		if(newFile != undefined && newFile != "") {
						actType = 'append';
		    		} else {
						actType = 'replace';
		    		}
	    			updateItem(actType, '#component-'+'{/literal}{$gridId}{literal}'+'-table>tbody:first', returnString.content);
	    			$('#uploadForm-{/literal}{$uniqueId}{literal}').parent().dialog('close');
	    		}
	    		$('#loadingText-{/literal}{$uniqueId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$uniqueId}{literal}").click(function() {
			validator = $('#metadataForm-{/literal}{$uniqueId}{literal}').validate();
			if($('#metadataForm-{/literal}{$uniqueId}{literal}').valid()) {
				$('#metadataForm-{/literal}{$uniqueId}{literal}').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});

		$("#cancelButton-{/literal}{$uniqueId}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile-{/literal}{$uniqueId}{literal}').val();
			deleteUrl = $('#deleteUrl-{/literal}{$uniqueId}{literal}').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm-{/literal}{$uniqueId}{literal}').parent().dialog('close');
			return false;
		});

	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$uniqueId}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType}" method="post">
	<!-- Max file size of 5 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	{fbvFormArea id="file"}
		{if !$libraryFile}
			{fbvFormSection title="common.file"}
				<input type="file" id="libraryFile" name="libraryFile" />
				<input type="submit" id="libraryFileSubmit-{$uniqueId}" name="submitFile" value="{translate key="common.upload"}" class="button" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput-{$uniqueId}">
		<div id='loading' class='throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText-{$uniqueId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>
</form>


<form name="metadataForm" id="metadataForm-{$uniqueId}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="saveMetadata"}" method="post">
	<input type="hidden" id="metadataRowId-{$uniqueId}" name="rowId" value="{$rowId|escape}" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="name" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	{init_button_bar id="#buttons" cancelId="#cancelButton2-$uniqueId" submitId="#continueButton2-$uniqueId"}
	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvLink id="cancelButton-$uniqueId" label="common.cancel" float=$fbvStyles.float.LEFT}
			{if !$rowId}{assign var="buttonDisabled" value="disabled"}{/if}
			{fbvButton id="continueButton-$uniqueId" label="common.saveAndClose" disabled=$buttonDisabled align=$fbvStyles.align.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>


{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl-{$uniqueId}" value="" />
<input type="hidden" id="newFile-{$uniqueId}" value="{$newFile}" />


<!-- / templates/controllers/grid/settings/library/form/fileForm.tpl -->

