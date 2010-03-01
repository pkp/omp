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

<script type="text/javascript">
	{literal}
	$(function() {
		{/literal}{if !$fileId}{literal}$('#fileUploadTabs-').tabs('option', 'disabled', [1,2,3,4]);{/literal}{/if}{literal}  // Disable next tabs when adding new file

	    $('#uploadForm').ajaxForm({
	        target: '#uploadOutput',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').throbber({
					bgcolor: "#CED7E1",
					speed: 1
				});
				$('#loading').throbber('enable');
				$('#loadingText').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the next tab
	        success: function(returnString) {
    			$('#loading').throbber("disable");
	    		$('#loading').hide();
	    		if (returnString.status == true) {
		    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('url', 1, returnString.metadataUrl);
		  			$('#continueButton-{/literal}{$fileId}{literal}').removeAttr("disabled");
		    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('enable', 1);
	    		}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors   
		$("#continueButton-{/literal}{$fileId}{literal}").click(function() {
			$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('select', 1);
		});
		$("#cancelButton-{/literal}{$fileId}{literal}").click(function() {
			$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('close');
		});
	});
	{/literal}
</script>

<form name="uploadForm" id="uploadForm" action="{url component="grid.submit.submissionFiles.SubmissionFilesGridHandler" op="uploadFile" monographId=$monographId fileId=$fileId}" method="post">
	{fbvFormArea id="file"}
		{fbvFormSection title="common.fileType"}
			{if $fileId}{assign var="selectDisabled" value="disabled"}{/if}
			{fbvSelect id="fileType" from=$bookFileTypes translate=false selected=$currentFileType disabled=$selectDisabled}
		{/fbvFormSection}
		{if !$fileId}
			{fbvFormSection title="author.submit.submissionFile"}
				<input type="file" name="submissionFile" />
				<input type="submit" value="{translate key='form.submit'}" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				<h4>{$monographFileName}</h4>
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput">
		<div id='loading' class='throbber'></div>
		<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul> 
	</div>
	<div class="separator"></div>
	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvButton id="cancelButton-$fileId" label="common.cancel" float=$fbvStyles.float.LEFT}
			{if !$fileId}{assign var="buttonDisabled" value="disabled"}{/if}
			{fbvButton id="continueButton-$fileId" label="common.continue" disabled=$buttonDisabled float=$fbvStyles.float.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>

<input type="hidden" name="monographId" value="{$monographId|escape}" />
{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $fileId}
<input type="hidden" name="fileId" value="{$fileId|escape}" />
{/if}

