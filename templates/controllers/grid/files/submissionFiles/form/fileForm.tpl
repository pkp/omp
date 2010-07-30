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
{assign var='randomId' value=1|rand:99999}

<script type="text/javascript">
	{literal}
	$(function() {
		{/literal}{if !$fileId}{literal}$('div#fileUploadTabs').last().tabs('option', 'disabled', [1,2,3,4]);{/literal}{/if}{literal}  // Disable next tabs when adding new file
		$('.button').button();
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
		    		$('#fileType-{/literal}{$randomId}{literal}').attr("disabled", "disabled");
		    		$('#submissionFile-{/literal}{$randomId}{literal}').attr("disabled", "disabled");
		    		$('div#fileUploadTabs').last().tabs('url', 0, returnString.fileFormUrl);
		    		$('div#fileUploadTabs').last().tabs('url', 1, returnString.metadataUrl);
		    		$('#deleteUrl-{/literal}{$randomId}{literal}').val(returnString.deleteUrl);
		  			$('#continueButton-{/literal}{$randomId}{literal}').button( "option", "disabled", false );
		    		$('div#fileUploadTabs').last().tabs('enable', 1);

		    		// If the file name is similar to an existing filename, show the possible revision control
		    		if(returnString.possibleRevision == true) {
		    			$('#confirmUrl-{/literal}{$randomId}{literal}').val(returnString.revisionConfirmUrl);
		    			$('#possibleRevision-{/literal}{$randomId}{literal}').show('slide');
		    		}
				}
	    		$('#loadingText-{/literal}{$randomId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set 'confirm revision' button behavior
		$("#confirmRevision-{/literal}{$randomId}{literal}").click(function() {
			confirmUrl = $('#confirmUrl-{/literal}{$randomId}{literal}').val();
			if(confirmUrl != "") {
				$.getJSON(confirmUrl, function(jsonData) {
					if (jsonData.status === true) {
						$("#possibleRevision-{/literal}{$randomId}{literal}").hide();
						$('div#fileUploadTabs').last().tabs('url', 0, jsonData.fileFormUrl);
						$('div#fileUploadTabs').last().tabs('url', 1, jsonData.metadataUrl);
			    		$('#deleteUrl-{/literal}{$randomId}{literal}').val(jsonData.deleteUrl);
					}
				});
			}
			return false;
		});
		$("#denyRevision-{/literal}{$randomId}{literal}").click(function() {
			$("#possibleRevision-{/literal}{$randomId}{literal}").hide();
		});

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$randomId}{literal}").click(function() {
			$('div#fileUploadTabs').last().tabs('select', 1);
			return false;
		});
		$("#cancelButton-{/literal}{$randomId}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			deleteUrl = $('#deleteUrl-{/literal}{$randomId}{literal}').val();
			if(deleteUrl != "") {
				$.post(deleteUrl);
			}

			$('div#fileUploadTabs').last().parent().dialog('close');
			return false;
		});


	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$randomId}" action="{url op="uploadFile" monographId=$monographId fileId=$fileId}" method="post">
	{fbvFormArea id="file"}
		{fbvFormSection title="common.fileType" required=1}
			{fbvSelect name="fileType" id="fileType-$randomId" from=$bookFileTypes translate=false selected=$currentFileType}
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
			<input type="submit" name="submitFile" value="{translate key="common.upload"}" class="button" />
		{/fbvFormSection}
	{/fbvFormArea}
	<div id="uploadOutput-{$randomId}">
		<div id='loading' class='throbber' style='margin: 0px;' ></div>
		<ul><li id='loadingText-{$randomId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>

	<div id="possibleRevision-{$randomId}" class="possibleRevision response" style="display: none;">
		<div id="revisionWarningIcon" class="warning"></div>
		<div id="revisionWarningText">
			<h5>{translate key="submission.upload.possibleRevision"}</h5>
			<p>{translate key="submission.upload.possibleRevisionDescription"}</p>
			<span><a href="#" id="confirmRevision-{$randomId}">{translate key="submission.upload.possibleRevisionConfirm"}</a></span>
			<span><a href="#" id="denyRevision-{$randomId}">{translate key="submission.upload.possibleRevisionDeny"}</a></span>
		</div>
	</div>

	<div class="separator"></div>

	{fbvFormArea id="buttons"}
	    {fbvFormSection}
	        {fbvLink id="cancelButton-$randomId" label="common.cancel"}
	        {if !$fileId}{assign var="buttonDisabled" value="disabled"}{/if}
	        {fbvButton id="continueButton-$randomId" label="common.continue" disabled=$buttonDisabled align=$fbvStyles.align.RIGHT}
	    {/fbvFormSection}
	{/fbvFormArea}

	<!--  After file is uploaded, store URLs to handler actions in these fields -->
	<input type="hidden" id="deleteUrl-{$randomId}" name="deleteUrl" value="" />
	<input type="hidden" id="confirmUrl-{$randomId}" name="confirmUrl" value="" />
</form>