{**
 * plugins/importexport/onix30/index.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.onix30.displayName"}
{include file="common/header.tpl"}
{/strip}

<div class="pkp_page_content">
	{if !$currentcontext->getData('publisher') || !$currentcontext->getData('location') || !$currentcontext->getData('codeType') || !$currentcontext->getData('codeValue')}
		{translate key="plugins.importexport.onix30.pressMissingFields"}
	{else}
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
					<div id="export-submissions-list-handler-{$uuid}">
						<script type="text/javascript">
							pkp.registry.init('export-submissions-list-handler-{$uuid}', 'SelectSubmissionsListPanel', {$exportSubmissionsListData|json_encode});
						</script>
					</div>
				{/fbvFormSection}
				{fbvFormButtons submitText="plugins.importexport.native.export" hideCancel="true"}
			{/fbvFormArea}
		</form>
	{/if}
</div>


{include file="common/footer.tpl"}
