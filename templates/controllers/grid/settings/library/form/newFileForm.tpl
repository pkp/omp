{**
 * templates/controllers/grid/settings/library/form/newFileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Library Files form
 *}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.grid.settings.library.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#uploadForm #plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadFile" escape=false}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form id="uploadForm" action="{url op="saveFile" fileType=$fileType}" method="post">
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
	{fbvFormArea id="name"}
		{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="libraryFileName" value=$libraryFileName maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="file"}
		{fbvFormSection title="common.file"}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl"}
</form>

