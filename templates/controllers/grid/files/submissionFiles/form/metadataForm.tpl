{**
 * metadataForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File metadata form.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#metadataForm').pkpHandler('$.pkp.controllers.FormHandler');
	{rdelim});
</script>

<form id="metadataForm" action="{url op="saveMetadata" monographId=$monographFile->getMonographId() fileId=$monographFile->getFileId() params=$additionalActionArgs escape=false}" method="post">
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
</form>
