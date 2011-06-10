{**
 * templates/controllers/grid/settings/library/form/editFileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Library Files form for editing an existing file
 *}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler'
		);
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="uploadForm" action="{url op="updateFile" fileType=$libraryFile->getType() fileId=$libraryFile->getId()}" method="post" class="pkp_form">
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name"}
			{fbvElement type="text" id="libraryFileName" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE multilingual=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="file"}
		{fbvFormSection title="common.file"}
			<table id="fileInfo" class="data" width="100%">
			<tr valign="top">
				<td width="20%" class="label">{translate key="common.fileName"}</td>
				<td width="80%" class="value">{$libraryFile->getOriginalFileName()|escape}</a></td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="common.fileSize"}</td>
				<td class="value">{$libraryFile->getNiceFileSize()}</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="common.dateUploaded"}</td>
				<td class="value">{$libraryFile->getDateUploaded()|date_format:$datetimeFormatShort}</td>
			</tr>
			</table>
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl"}
</form>

