<!-- templates/controllers/grid/files/submissionFiles/form/fileForm.tpl -->

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
		    		$('#fileType-{/literal}{$uniqueId}{literal}').attr("disabled", "disabled");
		    		$('#submissionFile-{/literal}{$uniqueId}{literal}').attr("disabled", "disabled");
		    		$('div#fileUploadTabs').last().tabs('url', 0, returnString.fileFormUrl);
		    		$('div#fileUploadTabs').last().tabs('url', 1, returnString.metadataUrl);
		    		$('#deleteUrl-{/literal}{$uniqueId}{literal}').val(returnString.deleteUrl);
		    		$('#uploadButton-{/literal}{$uniqueId}{literal}').button("option", "disabled", true);
					$('#continueButton-{/literal}{$uniqueId}{literal}').button( "option", "disabled", false);
		    		$('div#fileUploadTabs').last().tabs('enable', 1);

		    		// If the file name is similar to an existing filename, show the possible revision control
		    		if(returnString.possibleRevision == true) {
		    			$('#confirmUrl-{/literal}{$uniqueId}{literal}').val(returnString.revisionConfirmUrl);
		    			$('#possibleRevision-{/literal}{$uniqueId}{literal}').show('slide');
		    		}
				}
	    		$('#loadingText-{/literal}{$uniqueId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set 'confirm revision' button behavior
		$("#confirmRevision-{/literal}{$uniqueId}{literal}").click(function() {
			confirmUrl = $('#confirmUrl-{/literal}{$uniqueId}{literal}').val();
			if(confirmUrl != "") {
				$.getJSON(confirmUrl, function(jsonData) {
					if (jsonData.status === true) {
						$("#possibleRevision-{/literal}{$uniqueId}{literal}").hide();
						$('div#fileUploadTabs').last().tabs('url', 0, jsonData.fileFormUrl);
						$('div#fileUploadTabs').last().tabs('url', 1, jsonData.metadataUrl);
			    		$('#deleteUrl-{/literal}{$uniqueId}{literal}').val(jsonData.deleteUrl);
					}
				});
			}
			return false;
		});
		$("#denyRevision-{/literal}{$uniqueId}{literal}").click(function() {
			$("#possibleRevision-{/literal}{$uniqueId}{literal}").hide();
		});

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$uniqueId}{literal}").click(function() {
			$('div#fileUploadTabs').last().tabs('select', 1);
			return false;
		});
		$("#cancelButton-{/literal}{$uniqueId}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			deleteUrl = $('#deleteUrl-{/literal}{$uniqueId}{literal}').val();
			if(deleteUrl != "") {
				$.post(deleteUrl);
			}

			$('div#fileUploadTabs').last().parent().dialog('close');
			return false;
		});


	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$uniqueId}" action="{url op="uploadFile" monographId=$monographId fileId=$fileId}" method="post">
	{fbvFormArea id="file"}
		{fbvFormSection title="common.fileType" required=1}
			{fbvSelect name="fileType" id="fileType-$uniqueId" from=$bookFileTypes translate=false selected=$currentFileType}
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
			<input type="submit" id="uploadButton-{$uniqueId}" name="submitFile" value="{translate key="common.upload"}" class="button" />
		{/fbvFormSection}
	{/fbvFormArea}
	<div id="uploadOutput-{$uniqueId}">
		<div id='loading' class='throbber' style='margin: 0px;' ></div>
		<ul><li id='loadingText-{$uniqueId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>

	<div id="possibleRevision-{$uniqueId}" class="possibleRevision response" style="display: none;">
		<div id="revisionWarningIcon" class="warning"></div>
		<div id="revisionWarningText">
			<h5>{translate key="submission.upload.possibleRevision"}</h5>
			<p>{translate key="submission.upload.possibleRevisionDescription"}</p>
			<span><a href="#" id="confirmRevision-{$uniqueId}">{translate key="submission.upload.possibleRevisionConfirm"}</a></span>
			<span><a href="#" id="denyRevision-{$uniqueId}">{translate key="submission.upload.possibleRevisionDeny"}</a></span>
		</div>
	</div>

	<div class="separator"></div>

	{fbvFormArea id="buttons"}
	    {fbvFormSection}
	        {fbvLink id="cancelButton-$uniqueId" label="common.cancel"}
	        {if !$fileId}{assign var="buttonDisabled" value="disabled"}{/if}
	        {fbvButton id="continueButton-$uniqueId" label="common.continue" disabled=$buttonDisabled align=$fbvStyles.align.RIGHT}
	    {/fbvFormSection}
	{/fbvFormArea}

	<!--  After file is uploaded, store URLs to handler actions in these fields -->
	<input type="hidden" id="deleteUrl-{$uniqueId}" name="deleteUrl" value="" />
	<input type="hidden" id="confirmUrl-{$uniqueId}" name="confirmUrl" value="" />
</form>
<!-- / templates/controllers/grid/files/submissionFiles/form/fileForm.tpl -->

