{**
 * templates/controllers/grid/files/copyedit/form/copyeditingFileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting Files grid form -- Allow users to upload files to their copyediting responses
 *}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#uploadForm #plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadCopyeditedFile" monographId=$monographId signoffId=$signoffId escape=false}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="uploadForm" action="{url op="saveCopyeditedFile" monographId=$monographId signoffId=$signoffId}" method="post">
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
	{fbvFormArea id="file"}
		{fbvFormSection title="common.file"}
			{if !$copyeditingFile}
				<div id="plupload"></div>
			{else}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/if}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}
</form>
