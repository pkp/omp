{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}

<script type="text/javascript">{literal}

	// Create callbacks to handle plupload actions
	function attachCallbacks(uploader) {
		// Prevent > 1 files from being added
		uploader.bind('FilesAdded', function(up, files) {
			if(up.files.length > 1) {
				up.splice(0,1);
				up.refresh();
			}
		});

		// Add the file type field to the form
		uploader.bind('QueueChanged', function(up, files) {
			$("#plupload").pluploadQueue().settings.multipart_params = { fileType : $('#fileType').val()};
		});

		// Handler the server's JSON response
		uploader.bind('FileUploaded', function(up, files, ret) {
			returnString = eval("("+ret.response+")");

			if (returnString.status == true) {
		    		$('#fileType').attr("disabled", "disabled");
		    		$('div#fileUploadTabs').last().tabs('url', 0, returnString.fileFormUrl);
		    		$('div#fileUploadTabs').last().tabs('url', 1, returnString.metadataUrl);
		    		$('#deleteUrl').val(returnString.deleteUrl);
				$('#continueButton').button( "option", "disabled", false);
		    		$('div#fileUploadTabs').last().tabs('enable', 1);

		    		// If the file name is similar to an existing filename, show the possible revision control
		    		if(returnString.possibleRevision == true) {
		    			$('#confirmUrl').val(returnString.revisionConfirmUrl);
		    			$('#possibleRevision').show('slide');
		    		}
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
			url : '{/literal}{url op="uploadFile" monographId=$monographId fileId=$fileId escape=false}{literal}',
			max_file_size : '20mb',
			multi_selection: false,
			file_data_name: 'submissionFile',
			multipart: true,
			multipart_params : {fileType : $('#fileType').val()},

			// Flash settings
			flash_swf_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.silverlight.xap'
		});
		{/literal}{if !$fileId}{literal}$('div#fileUploadTabs').last().tabs('option', 'disabled', [1,2,3,4]);{/literal}{/if}{literal}  // Disable next tabs when adding new file
		$('.button').button();

		// Set 'confirm revision' button behavior
		$("#confirmRevision").click(function() {
			confirmUrl = $('#confirmUrl').val();
			if(confirmUrl != "") {
				$.getJSON(confirmUrl, function(jsonData) {
					if (jsonData.status === true) {
						$("#possibleRevision").hide();
						$('div#fileUploadTabs').last().tabs('url', 0, jsonData.fileFormUrl);
						$('div#fileUploadTabs').last().tabs('url', 1, jsonData.metadataUrl);
			    		$('#deleteUrl').val(jsonData.deleteUrl);
					}
				});
			}
			return false;
		});
		$("#denyRevision").click(function() {
			$("#possibleRevision").hide();
			return false;
		});

		// Set cancel/continue button behaviors
		$("#continueButton").click(function() {
			$('div#fileUploadTabs').last().tabs('select', 1);
			return false;
		});
		$("#cancelButton").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			deleteUrl = $('#deleteUrl').val();
			if(deleteUrl != "") {
				$.post(deleteUrl);
			}

			$('div#fileUploadTabs').last().parent().dialog('close');
			return false;
		});


	});
	{/literal}
</script>

<form name="uploadForm" id="uploadForm" action="{url op="uploadFile" monographId=$monographId fileId=$fileId fileStage=$fileStage}" method="post">
	{fbvFormArea id="file"}
		{fbvFormSection title="common.fileType" required=1}
			{fbvSelect name="fileType" id="fileType" from=$bookFileTypes translate=false selected=$currentFileType}
		{/fbvFormSection}
		{if $fileId}
			{fbvFormSection title="submission.submit.currentFile"}
				{$monographFileName}
			{/fbvFormSection}
		{/if}
		{fbvFormSection title="submission.submit.selectFile"}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}

	<div id="possibleRevision" class="possibleRevision response" style="display: none;">
		<div id="revisionWarningIcon" class="warning"></div>
		<div id="revisionWarningText">
			<h5>{translate key="submission.upload.possibleRevision"}</h5>
			<p>{translate key="submission.upload.possibleRevisionDescription"}</p>
			<span><a href="#" id="confirmRevision">{translate key="submission.upload.possibleRevisionConfirm"}</a></span>
			<span><a href="#" id="denyRevision">{translate key="submission.upload.possibleRevisionDeny"}</a></span>
		</div>
	</div>

	<div class="separator"></div>

	{fbvFormArea id="buttons"}
	    {fbvFormSection}
	        {fbvLink id="cancelButton" label="common.cancel"}
	        {if !$fileId}{assign var="buttonDisabled" value="disabled"}{/if}
	        {fbvButton id="continueButton" label="common.continue" disabled=$buttonDisabled align=$fbvStyles.align.RIGHT}
	    {/fbvFormSection}
	{/fbvFormArea}

	<!--  After file is uploaded, store URLs to handler actions in these fields -->
	<input type="hidden" id="deleteUrl" name="deleteUrl" value="" />
	<input type="hidden" id="confirmUrl" name="confirmUrl" value="" />
</form>
