{**
 * plugins/importexport/native/templates/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.native.displayName"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#importExportTabs').pkpHandler('$.pkp.controllers.TabHandler');
		$('#importExportTabs').tabs('option', 'cache', true);
	{rdelim});
</script>
<div id="importExportTabs" class="pkp_controllers_tab">
	<ul>
		<li><a href="#import-tab">{translate key="plugins.importexport.native.import"}</a></li>
		<li><a href="#export-tab">{translate key="plugins.importexport.native.export"}</a></li>
	</ul>
	<div id="import-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#importXmlForm').pkpHandler('$.pkp.controllers.form.FileUploadFormHandler',
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
		<form id="importXmlForm" class="pkp_form" action="{plugin_url path="importBounce"}" method="post">
			{fbvFormArea id="importForm"}
				{* Container for uploaded file *}
				<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
				<p>{translate key="plugins.importexport.native.import.instructions"}</p>

				<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
				{fbvFormArea id="file"}
					{fbvFormSection title="common.file"}
						{include file="controllers/fileUploadContainer.tpl" id="plupload"}
					{/fbvFormSection}
				{/fbvFormArea}

				{fbvFormButtons hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
	<div id="export-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportXmlForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportXmlForm" class="pkp_form" action="{plugin_url path="export"}" method="post">
			{fbvFormArea id="exportForm"}
				{url|assign:submissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.exportableSubmissions.ExportableSubmissionsListGridHandler" op="fetchGrid"}
				{load_url_in_div id="submissionsListGridContainer" url=$submissionsListGridUrl}
				{fbvFormButtons hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
