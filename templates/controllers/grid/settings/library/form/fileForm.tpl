{**
 * templates/controllers/grid/settings/library/form/fileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Library Files form
 *}

<script type="text/javascript">{literal}
	<!--
	//Create callbacks to handle plupload actions
	function attachCallbacks(uploader) {
		// Prevent > 1 files from being added
		uploader.bind('FilesAdded', function(up, files) {
			if(up.files.length > 1) {
				up.splice(0,1);
				up.refresh();
			}
		});

		// Handler the server's JSON response
		uploader.bind('FileUploaded', function(up, files, ret) {
			returnString = eval("("+ret.response+")");

			if (returnString.status == true) {
	    			$("#submitModalButton").button( "option", "disabled", false);
		    		$('#deleteUrl').val(returnString.deleteUrl);
	    			$("#metadataRowId").val(returnString.elementId);
			} else {
				alert(returnString.content);
			}
		});
	}

	$(function() {
		$('.button').button();
		$('#uploadForm').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons

		// Setup the upload widget
		$("#plupload").pluploadQueue({
			// General settings
			setup: attachCallbacks,
			runtimes : 'html5,flash,silverlight,html4',
			url : '{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType escape=false}{literal}',
			max_file_size : '20mb',
			multi_selection: false,
			file_data_name: 'libraryFile',

			// Flash settings
			flash_swf_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.silverlight.xap'
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
			return false;
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
	// -->
</script>


<form id="uploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="uploadFile" fileType=$fileType}" method="post">
	{fbvFormArea id="file"}
		{if !$libraryFile}
			{fbvFormSection title="common.file"}
				<div id="plupload"></div>
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
</form>


<form id="metadataForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="saveMetadata"}" method="post">
	<input type="hidden" id="metadataRowId" name="rowId" value="{$rowId|escape}" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="name" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$rowId}{assign var="buttonDisabled" value="true"}{/if}
	{include file="form/formButtons.tpl" submitText="common.saveAndClose" submitDisabled=$buttonDisabled}
</form>


{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="newFile" value="{$newFile|escape}" />

