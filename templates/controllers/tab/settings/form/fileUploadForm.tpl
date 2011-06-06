{**
 * templates/controllers/tab/settings/form/fileUploadForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File upload form.
 *}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
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

<form id="uploadForm" class="pkp_controllers_form" action="{url op="saveFile" fileSettingName=$fileSettingName formName=$formName}" method="post" class="pkp_controllers_form">
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
	{fbvFormArea id="file"}
		{fbvFormSection title="common.file"}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl"}
</form>