{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Attachment Files grid form
 *
 *}

{modal_title id="#uploadForm" key='grid.reviewAttachments.add' iconClass="fileManagement" canClose=1}

<script type="text/javascript">{literal}
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
	    			$("#submitModalButton").button("option", "disabled", false);
		    		$('#deleteUrl').val(returnString.deleteUrl);
		    		$('#saveUrl').val(returnString.saveUrl);
			} else {
				alert(returnString.content);
			}
		});
	}
	$(function() {
		// Setup the upload widget
		$("#plupload").pluploadQueue({
			// General settings
			setup: attachCallbacks,
			runtimes : 'html5,flash,silverlight,html4',
			url : '{/literal}{url router=$smarty.const.ROUTE_COMPONENT op="saveFile" monographId=$monographId reviewId=$reviewId escape=false}{literal}',
			max_file_size : '20mb',
			multi_selection: false,
			file_data_name: 'attachment',

			// Flash settings
			flash_swf_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.silverlight.xap'
		});

		// Set cancel/continue button behaviors
		$("#submitModalButton").click(function() {
			saveAndUpdate($('#saveUrl').val(),
				'append',
				'#component-{/literal}{$gridId}{literal}-table',
				'#uploadForm'
			);
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
</script>

<div id="uploadForm">
	{fbvFormArea id="file"}
		{if !$attachmentFile}
			{fbvFormSection title="common.file"}
				<div id="plupload"></div>
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/files/reviewAttachments/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}

	{if !$rowId}{assign var="buttonDisabled" value="true"}{/if}
	{init_button_bar id="#uploadForm" submitText="common.saveAndClose" submitDisabled=$buttonDisabled}
</div>

{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
<input type="hidden" id="deleteUrl" value="" />
<input type="hidden" id="saveUrl" value="" />
<input type="hidden" id="newFile" value="{$newFile}" />


