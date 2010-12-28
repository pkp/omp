{**
 * metadataForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File metadata form
 *}
<script type="text/javascript">{literal}
	$(function() {ldelim}
		// Attach the form handler.
		$('#metadataForm').pkpHandler('$.pkp.controllers.FormHandler');
	{rdelim});
</script>

<form name="metadataForm" id="metadataForm" action="{url op="saveMetadata" monographId=$monographId fileId=$fileId}" method="post">
	<h3>{translate key='submission.fileDetails'}</h3>
	{fbvFormArea id="fileMetaData"}
		{fbvFormSection title="common.name" required=1}
			{fbvElement type="text" label="common.name" id="name" value=$monographFile->getLocalizedName() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormSection title="common.note"}
		{if $note}
			{fbvTextArea id="note" value=$note->getContents() size=$fbvStyles.size.SMALL}
		{else}
			{fbvTextArea id="note" size=$fbvStyles.size.SMALL}
		{/if}
	{/fbvFormSection}

	<h4>{translate key="submission.submit.readOnlyInfo"}</h4>
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.originalFileName" float=$fbvStyles.float.LEFT}
			{$monographFile->getOriginalFileName()}
		{/fbvFormSection}
		{fbvFormSection title="common.type" float=$fbvStyles.float.LEFT}
			{$monographFile->getDocumentType()}
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
			{fbvLink id="cancelButton2" label="common.cancel"}
			{fbvButton id="continueButton2" label="common.continue" align=$fbvStyles.align.RIGHT}
		{/fbvFormSection}
	{/fbvFormArea}
</form>
