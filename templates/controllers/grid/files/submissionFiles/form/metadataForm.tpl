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
		$('.button').button();
		$('#metadataForm-{/literal}{$fileId}{literal}').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		if(returnString.isEditing) { // User was editing existing item, save and close
			    		saveAndUpdate('{/literal}{url router=$smarty.const.ROUTE_COMPONENT op="returnFileRow" fileId=$fileId}{literal}',
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
			validator = $('#metadataForm-{/literal}{$fileId}{literal}').validate();
			if($('#metadataForm-{/literal}{$fileId}{literal}').valid()) {
				$('#metadataForm-{/literal}{$fileId}{literal}').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});
		$("#cancelButton2-{/literal}{$fileId}{literal}").click(function() {
			//  The user has cancelled the modal without filling out the  metadata form
			deleteUrl = '{/literal}{url router=$smarty.const.ROUTE_COMPONENT op="deleteFile" fileId=$fileId}{literal}';
			newFile = $('#newFile').val();
			if(newFile != "") {
				$.post(deleteUrl);
			}

			$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('close');
		});
	});
	{/literal}
</script>

<form name="metadataForm-{$fileId}" id="metadataForm-{$fileId}" action="{url op="saveMetadata" monographId=$monographId fileId=$fileId}" method="post">
	<h3>{translate key='submission.fileDetails'}</h3>
	{fbvFormArea id="fileMetaData"}
		{fbvFormSection title="common.name"}
			{fbvElement type="text" label="common.name" id="name" value=$monographFile->getLocalizedName() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormSection title="common.note"}
		{if $note}
			{fbvTextarea id="note" value=$note->getContents() size=$fbvStyles.size.SMALL}
		{else}
			{fbvTextarea id="note" size=$fbvStyles.size.SMALL}
		{/if}
	{/fbvFormSection}

	<h4>{translate key="submission.submit.readOnlyInfo"}</h4>
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.originalFileName" float=$fbvStyles.float.LEFT}
			{$monographFile->getOriginalFileName()}
		{/fbvFormSection}
		{fbvFormSection title="common.type" float=$fbvStyles.float.LEFT}
			{$monographFile->getFileType()}
		{/fbvFormSection}
		{fbvFormSection title="common.size" float=$fbvStyles.float.RIGHT}
			{$monographFile->getNiceFileSize()}
		{/fbvFormSection}
		{fbvFormSection title="common.dateUploaded" float=$fbvStyles.float.LEFT}
			{$monographFile->getDateUploaded()}
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
