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
<!--  Need a random ID to give to modal elements so that they are unique in the DOM (can not use
		fileId like elsewhere in the modal, because there may not be an associated file yet-->
{assign var='uniqueId' value=""|uniqid}

<script type="text/javascript">
	{literal}
	$(function() {
		{/literal}{if !$fileId}{literal}$('div#fileUploadTabs').last().tabs('option', 'disabled', [1,2,3,4]);{/literal}{/if}{literal}  // Disable next tabs when adding new file
		$('.button').button();
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
		    		$('#fileType').attr("disabled", "disabled");
		    		$('#submissionFile').attr("disabled", "disabled");
		    		$('div#fileUploadTabs').last().tabs('url', 0, returnString.fileFormUrl);
		    		$('div#fileUploadTabs').last().tabs('url', 1, returnString.metadataUrl);
		    		$('#deleteUrl').val(returnString.deleteUrl);
		    		$('#uploadButton').button("option", "disabled", true);
					$('#continueButton').button( "option", "disabled", false);
		    		$('div#fileUploadTabs').last().tabs('enable', 1);

		    		// If the file name is similar to an existing filename, show the possible revision control
		    		if(returnString.possibleRevision == true) {
		    			$('#confirmUrl').val(returnString.revisionConfirmUrl);
		    			$('#possibleRevision').show('slide');
		    		}
				}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });

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


<form name="uploadForm" id="uploadForm" action="{url op="uploadFile" monographId=$monographId fileId=$fileId}" method="post">
	<!-- Max file size of 20 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="20971520" />
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
			<div class="fileInputContainer">
				<input type="file" id="submissionFile" name="submissionFile" />
			</div>
			<input type="submit" id="uploadButton" name="submitFile" value="{translate key="common.upload"}" class="button" />
		{/fbvFormSection}
	{/fbvFormArea}
	<div id="uploadOutput">
		<div id='loading' class='throbber' style='margin: 0px;' ></div>
		<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>

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
