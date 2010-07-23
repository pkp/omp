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
		{/literal}{if !$fileId}{literal}$('#fileUploadTabs-').tabs('option', 'disabled', [1,2,3,4]);{/literal}{/if}{literal}  // Disable next tabs when adding new file
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
		    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('url', 0, returnString.fileFormUrl);
		    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('url', 1, returnString.metadataUrl);
		    		$('#deleteUrl').val(returnString.deleteUrl);
		  			$('#continueButton-{/literal}{$fileId}{literal}').button( "option", "disabled", false );
		    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('enable', 1);
	    		}
	    		$('#loadingText-{/literal}{$randomId}{literal}').text(returnString.content);  // Set to error or success message
	        }
	    });

		// Set cancel/continue button behaviors
		$("#continueButton-{/literal}{$fileId}{literal}").click(function() {
			$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('select', 1);
		});
		$("#cancelButton-{/literal}{$fileId}{literal}").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			deleteUrl = $('#deleteUrl').val();
			if(deleteUrl != "") {
				$.post(deleteUrl);
			}

			$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('close');
		});
	});
	{/literal}
</script>


<form name="uploadForm" id="uploadForm-{$randomId}" action="{url op="uploadFile" monographId=$monographId fileId=$fileId}" method="post">
	{fbvFormArea id="file"}
		{fbvFormSection title="common.fileType" required=1}
			{if $fileId}{assign var="selectDisabled" value="disabled"}{/if}
			{fbvSelect name="fileType" id="fileType-$randomId" from=$bookFileTypes translate=false selected=$currentFileType disabled=$selectDisabled}
		{/fbvFormSection}
		{if !$fileId}
			{fbvFormSection title="submission.submit.selectFile"}
				<div class="fileInputContainer">
					<input type="file" id="submissionFile" name="submissionFile" />
				</div>
				<input type="submit" name="submitFile" value="{translate key="common.upload"}" class="button" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				<h4>{$monographFileName}</h4>
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput-{$randomId}">
		<div id='loading' class='throbber' style='margin: 0px;' ></div>
		<ul><li id='loadingText-{$randomId}' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
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
<input type="hidden" id="deleteUrl" name="deleteUrl" value="" />
{if $gridId}
<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $fileId}
<input type="hidden" name="fileId" value="{$fileId|escape}" />
{/if}

