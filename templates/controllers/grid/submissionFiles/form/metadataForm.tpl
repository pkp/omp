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
		$('#fileUploadTabs-').attr("id","fileUploadTabs-{/literal}{$fileId}{literal}"); // Rename container to use unique id (necessary to prevent caching)
		//$('#fileUploadTabs-').i  
		$('#metadataForm').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
	    			$('#loading').throbber("disable");
		    		$('#loading').hide();
		    		if(returnString.isEditing) { // User was editing existing item, save and close
			    		saveAndUpdate('{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="grid.submit.submissionFiles.SubmissionFilesGridHandler" op="returnFileRow" fileId=$fileId}{literal}', 
			    				'replace', 
			    				'component-'+'{/literal}{$gridId}{literal}'+'-row-'+'{/literal}{$fileId}{literal}',
        						'#fileUploadTabs-{/literal}{$fileId}{literal}');
		    		} else {
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('url', 2, returnString.finishingUpUrl);
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('enable', 2);
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('select', 2);
		    		}
	    		} else {

	    		}
	        }
	    });

		// Set cancel/continue button behaviors    
		$("#continueButton2-{/literal}{$fileId}{literal}").click(function() {
			validator = $('#metadataForm').validate();
			if($('#metadataForm').valid()) {
				$('#metadataForm').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});
		$("#cancelButton2-{/literal}{$fileId}{literal}").click(function() {
			$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('close');
		});	
	});
	{/literal}
</script>

<form name="metadataForm" id="metadataForm" action="{url component="grid.submit.submissionFiles.SubmissionFilesGridHandler" op="saveMetadata" monographId=$monographId fileId=$fileId}" method="post">
	<h3>File Details</h3>
	{fbvFormArea id="fileMetaData"}
		{fbvFormSection title="common.name"}
			{fbvElement type="text" name="name" id="name" value=$name maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection title="author.submit.readOnlyInfo" float=$fbvStyles.float.LEFT}
			{fbvElement disabled="disabled" type="text" label="common.originalFileName" name="originalFilename" id="originalFilename" value=$monographFile->getOriginalFileName() size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement disabled="disabled" type="text" label="common.type" name="type" id="type" value=$monographFile->getFileType() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.Right}
			{fbvElement disabled="disabled" type="text" label="common.size" name="size" id="size" value=$monographFile->getFileSize() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement disabled="disabled" type="text" label="common.dateUploaded" name="dateUploaded" id="dateUploaded" value=$monographFile->getDateUploaded() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	
	{fbvFormArea id="buttons"}
		{fbvFormSection}
			{fbvButton id="cancelButton2-$fileId" label="common.cancel" float=$fbvStyles.float.LEFT}
			{fbvButton id="continueButton2-$fileId" label="common.continue" float=$fbvStyles.float.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $fileId}
	<input type="hidden" name="fileId" value="{$fileId|escape}" />
{/if}
<br />
