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
{modal_title id="#metadataForm" key='settings.setup.addItem' iconClass="fileManagement"}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#uploadForm').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
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
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#libraryFile').attr("disabled", "disabled");
	    			$('#libraryFileSubmit').button("option", "disabled", true);
	    			$("#submitModalButton").button( "option", "disabled", false);
		    		$('#deleteUrl').val(returnString.deleteUrl);
	    			$("#metadataRowId").val(returnString.elementId);
	    		}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });
		// Handle metadata form
	    $('#metadataForm').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		newFile = $('#newFile').val();
		    		if(newFile != undefined && newFile != "") {
						actType = 'append';
		    		} else {
						actType = 'replace';
		    		}
	    			updateItem(actType, '#component-'+'{/literal}{$gridId}{literal}'+'-table>tbody:first', returnString.content);
	    			$('#uploadForm').parent().dialog('close');
	    		}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#submitModalButton").click(function() {
			validator = $('#metadataForm').validate();
			if($('#metadataForm').valid()) {
				$('#metadataForm').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});

		$("#cancelModalButton").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile').val();
			deleteUrl = $('#deleteUrl').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm').parent().dialog('close');
			return false;
		});

	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType}" method="post">
	<!-- Max file size of 5 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	{fbvFormArea id="file"}
		{if !$libraryFile}
			{fbvFormSection title="common.file"}
				<input type="file" id="libraryFile" name="libraryFile" />
				<input type="submit" id="libraryFileSubmit" name="submitFile" value="{translate key="common.upload"}" class="button" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput">
		<div id='loading' class='throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>
</form>


<form name="metadataForm" id="metadataForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="saveMetadata"}" method="post">
	<input type="hidden" id="metadataRowId" name="rowId" value="{$rowId|escape}" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="name" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$rowId}{assign var="buttonDisabled" value="true"}{/if}
	{init_button_bar id="#buttons" submitText="common.saveAndClose" submitDisabled=$buttonDisabled}

</form>


{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl" value="" />
<input type="hidden" id="newFile" value="{$newFile}" />


<!-- / templates/controllers/grid/settings/library/form/fileForm.tpl -->

