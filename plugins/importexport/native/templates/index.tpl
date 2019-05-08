{**
 * plugins/importexport/native/templates/index.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
								uploadUrl: {plugin_url|json_encode path="uploadImportXML" escape=false},
								baseUrl: {$baseUrl|json_encode}
							{rdelim}
					{rdelim}
				);
			{rdelim});
		</script>
		<form id="importXmlForm" class="pkp_form" action="{plugin_url path="importBounce"}" method="post">
			{csrf}
			{fbvFormArea id="importForm"}
				{* Container for uploaded file *}
				<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

				{fbvFormArea id="file"}
					{fbvFormSection title="plugins.importexport.native.import.instructions"}
						{include file="controllers/fileUploadContainer.tpl" id="plupload"}
					{/fbvFormSection}
				{/fbvFormArea}

				{fbvFormButtons submitText="plugins.importexport.native.import" hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
	<div id="export-tab">
		{if !$currentcontext->getData('publisher') || !$currentcontext->getData('location') || !$currentcontext->getData('codeType') || !$currentcontext->getData('codeValue')}
			{translate key="plugins.importexport.native.onix30.pressMissingFields"}
		{/if}
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportXmlForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportXmlForm" class="pkp_form" action="{plugin_url path="export"}" method="post">
			{csrf}
			{fbvFormArea id="exportForm"}
				{fbvFormSection}
					{assign var="uuid" value=""|uniqid|escape}
					<div id="export-submissions-{$uuid}">
						<select-submissions-list-panel
							v-bind="components.exportSubmissionsListPanel"
							@set="set"
						/>
					</div>
					<script type="text/javascript">
						pkp.registry.init('export-submissions-{$uuid}', 'Container', {$exportSubmissionsListData|json_encode});
					</script>
				{/fbvFormSection}
				{fbvFormButtons submitText="plugins.importexport.native.export" hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
