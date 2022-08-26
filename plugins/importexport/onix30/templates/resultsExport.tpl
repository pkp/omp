{**
 * plugins/importexport/native/templates/resultsExport.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Result of operations this plugin performed
 *}
{if $errorsFound}
	{translate key="plugins.importexport.onix30.processFailed"}
{else}
	{translate key="plugins.importexport.onix30.export.completed"}
	{translate key="plugins.importexport.onix30.export.completed.downloadFile"}

	<div id="export-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportXmlForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportXmlForm" class="pkp_form" action="{plugin_url path="downloadExportFile"}" method="post">
			{csrf}
			<input type="hidden" name="downloadFilePath" id="downloadFilePath" value="{$exportPath|escape}" />
			{fbvFormArea id="xmlForm"}
				{fbvFormButtons submitText="plugins.importexport.native.onix30.download.results" hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
{/if}

{assign var=templatePath value=$onixPlugin->getTemplateResource('innerResults.tpl')}
{include file=$templatePath key='warnings' errorsAndWarnings=$errorsAndWarnings}
{include file=$templatePath key='errors' errorsAndWarnings=$errorsAndWarnings}

{if $validationErrors}
	<h2>{translate key="plugins.importexport.common.validationErrors"}</h2>
	<ul>
		{foreach from=$validationErrors item=validationError}
			<li>{$validationError->message|escape}</li>
		{/foreach}
	</ul>
{/if} 
