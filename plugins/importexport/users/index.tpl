{**
 * plugins/importexport/users/index.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.users.displayName"}
{include file="common/header.tpl"}
{/strip}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#exportUsersForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<h3>{translate key="plugins.importexport.users.export.exportUsers"}</h3>
<div class="pkp_helpers_quarter">
	<form id="exportUsersForm" class="pkp_form" action="{plugin_url path="exportByRole"}" method="post">
		{fbvFormArea id="exportForm"}
			{fbvFormSection for="roles"}
				{fbvElement type="select" id="roles" name="roles[]" from=$roleOptions required="true" multiple="true" label="plugins.importexport.users.export.exportByRole"}
			{/fbvFormSection}
			{fbvFormButtons hideCancel="true" submitText="plugins.importexport.users.export.exportUsers"}
		{/fbvFormArea}
	</form>
</div>
	<p><a href="{plugin_url path="exportAll"}">{translate key="plugins.importexport.users.export.exportAllUsers"}</a></p>

<h3>{translate key="plugins.importexport.users.import.importUsers"}</h3>

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#importUsersForm').pkpHandler('$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#plupload'),
					uploaderOptions: {ldelim}
						uploadUrl: '{plugin_url path="uploadImportXML"}',
						baseUrl: '{$baseUrl|escape:javascript}'
					{rdelim}
			{rdelim}
		);
	{rdelim});
</script>
<form id="importUsersForm" class="pkp_form" action="{plugin_url path="confirm"}" method="post">
	{fbvFormArea id="importForm"}
		{* Container for uploaded file *}
		<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
		<p>{translate key="plugins.importexport.users.import.instructions"}</p>

		<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
		{fbvFormArea id="file"}
			{fbvFormSection title="common.file"}
				{include file="controllers/fileUploadContainer.tpl" id="plupload"}
			{/fbvFormSection}
		{/fbvFormArea}

		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="sendNotify" value="1" label="plugins.importexport.users.import.sendNotify"}
			{fbvElement type="checkbox" id="continueOnError" value="1" label="plugins.importexport.users.import.continueOnError"}
		{/fbvFormSection}

		{fbvFormButtons hideCancel="true"}
	{/fbvFormArea}
</form>

{include file="common/footer.tpl"}
