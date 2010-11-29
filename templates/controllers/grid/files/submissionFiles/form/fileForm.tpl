{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form.
 *
 * This form implements two workflows:
 * 1) Uploading of a revision to an existing file.
 * 2) Upload of a file that may or may not be a revision
 *    of an existing file (free upload).
 *
 * In the first case the selection of a file to associate
 * with the revision will be mandatory. In the second case
 * it will be optional.
 *}

<script type="text/javascript">{literal}
	// Create callbacks to handle plupload actions.
	function attachCallbacks(uploader) {
		// Prevent > 1 files from being added.
		uploader.bind('FilesAdded', function(up, files) {
			if(up.files.length > 1) {
				up.splice(0,1);
				up.refresh();
			}
		});

		// Add the file type field to the form.
		uploader.bind('QueueChanged', function(up, files) {{/literal}
			{if !empty($monographFileOptions)}
				var $revisedFileId = $('#uploadForm #revisedFileId');
				$revisedFileId.attr("disabled", "disabled");
			{/if}{literal}
			var $fileType = $('#uploadForm #fileType');
			$fileType.attr("disabled", "disabled");

			$("#uploadForm #plupload").pluploadQueue().settings.multipart_params = {{/literal}
				{if !empty($monographFileOptions)}
					revisedFileId: $revisedFileId.val(),
				{/if}{literal}
				fileType: $fileType.val()
			};
		});

		// Handle the server's JSON response.
		uploader.bind('FileUploaded', function(up, files, ret) {
			returnString = eval("("+ret.response+")");

			if (returnString.status == true) {
				$('div#fileUploadTabs').last().tabs('url', 0, returnString.fileFormUrl);
				$('div#fileUploadTabs').last().tabs('url', 1, returnString.metadataUrl);
				$('#deleteUrl').val(returnString.deleteUrl);
				$('#continueButton').button( "option", "disabled", false);
				$('div#fileUploadTabs').last().tabs('enable', 1);
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
			runtimes: 'html5,flash,silverlight,html4',
			url: $("#uploadForm").attr('action'),
			max_file_size: '20mb',
			multi_selection: false,
			file_data_name: 'submissionFile',
			multipart: true,

			// Flash settings
			flash_swf_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.silverlight.xap'
		});

		// Disable following tabs while uploading a file.
		$('div#fileUploadTabs').last().tabs('option', 'disabled', [1,2]);

		// Style buttons.
		$('.button').button();

		{/literal}{if !$isRevision}{literal}
			// When a user selects a submission to revise then the
			// the file type chooser must be disabled.
			var $revisedFileId = $('#uploadForm #revisedFileId');
			var $fileType = $('#uploadForm #fileType');
			$revisedFileId.change(function() {
				// All file genres currently assigned to monograph files.
				var monographFileGenres = {
					{/literal}{foreach name=currentMonographFileGenres from=$currentMonographFileGenres key=monographFileId item=fileGenre}
						{$monographFileId}: {$fileGenre}{if !$smarty.foreach.currentMonographFileGenres.last},{/if}
					{/foreach}{literal}
				};
				if ($revisedFileId.val() == 0) {
					// New file...
					$fileType.attr('disabled', '');
				} else {
					// Revision...
					$fileType.val(monographFileGenres[$revisedFileId.val()]);
					$fileType.attr('disabled', 'disabled');
				}
			});
		{/literal}{/if}{literal}


		// Set cancel/continue button behaviors
		$("#uploadForm #continueButton").click(function() {
			$('div#fileUploadTabs').last().tabs('select', 1);
			return false;
		});
		$("#uploadForm #cancelButton").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			deleteUrl = $('#deleteUrl').val();
			alert(deleteUrl);
			if(deleteUrl != "") {
				$.post(deleteUrl);
			}

			// Close the modal.
			$('div#fileUploadTabs').last().parent().dialog('close');
			return false;
		});
	});
	{/literal}
</script>

<form name="uploadForm" id="uploadForm" action="{url op="uploadFile" monographId=$monographId}" method="post">
	{fbvFormArea id="file"}
		{if $revisedFileId}
			{fbvFormSection title="submission.submit.currentFile"}
				{$revisedMonographFileName}
			{/fbvFormSection}
		{elseif !empty($monographFileOptions)}
			{fbvFormSection title="submission.originalFile" required=$isRevision}
				<p>
					{if $isRevision}
						{translate key="submission.upload.selectMandatoryFileToRevise"}
					{else}
						{translate key="submission.upload.selectOptionalFileToRevise"}
					{/if}
				</p>
				{fbvSelect name="revisedFileId" id="revisedFileId" from=$monographFileOptions translate=false} <br />
			{/fbvFormSection}
		{/if}

		{if !$isRevision}
			{fbvFormSection title="common.fileType" required=1}
				{fbvSelect name="fileType" id="fileType" from=$monographFileGenres translate=false selected=$currentFileType}
			{/fbvFormSection}
		{/if}

		{fbvFormSection title="submission.submit.selectFile" required=1}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvLink id="cancelButton" label="common.cancel"}
			{fbvButton id="continueButton" label="common.continue" disabled="disabled" align=$fbvStyles.align.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}

	{* After file is uploaded, store URLs to handler actions in these fields *}
	<input type="hidden" id="deleteUrl" name="deleteUrl" value="" />
</form>
