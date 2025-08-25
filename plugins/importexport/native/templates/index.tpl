{**
 * plugins/importexport/native/templates/index.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}
{extends file="layouts/backend.tpl"}

{block name="page"}
	<h1 class="app__pageHeading">
		{$pageTitle|escape}
	</h1>

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
		<li><a href="#exportSubmissions-tab">{translate key="plugins.importexport.native.export"}</a></li>
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
	<div id="exportSubmissions-tab">
		{if !$currentContext->getData('publisher') || !$currentContext->getData('location') || !$currentContext->getData('codeType') || !$currentContext->getData('codeValue')}
			{capture assign="contextSettingsUrl"}{url page="management" op="settings" path="context"}{/capture}
			{translate key="plugins.importexport.native.onix30.pressMissingFields" url=$contextSettingsUrl}
		{/if}
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportXmlForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
			{rdelim});
		</script>
		<form id="exportXmlForm" class="pkp_form" action="{plugin_url path="exportSubmissionsBounce"}" method="post">
			{csrf}
			{fbvFormArea id="exportForm"}
				<submissions-list-panel
					v-bind="components.submissions"
					@set="set"
				>

					<template v-slot:item="{ldelim}item{rdelim}">
						<div class="listPanel__itemSummary">
							<label>
								<input
									type="checkbox"
									name="selectedSubmissions[]"
									:value="item.id"
									v-model="selectedSubmissions"
								/>
								<span
                                    class="listPanel__itemSubTitle"
                                    v-strip-unsafe-html="localize(
                                        item.publications.find(p => p.id == item.currentPublicationId).fullTitle,
                                        item.publications.find(p => p.id == item.currentPublicationId).locale
                                    )"
                                >
								</span>
							</label>
							<pkp-button element="a" :href="item.urlWorkflow" style="margin-left: auto;">
								{{ __('common.view') }}
							</pkp-button>
						</div>
					</template>
				</submissions-list-panel>
				{fbvFormSection}
					<pkp-button :disabled="!components.submissions.itemsMax" @click="toggleSelectAll">
						<template v-if="components.submissions.itemsMax && selectedSubmissions.length >= components.submissions.itemsMax">
							{translate key="common.selectNone"}
						</template>
						<template v-else>
							{translate key="common.selectAll"}
						</template>
					</pkp-button>
					<pkp-button @click="submit('#exportXmlForm')" type="submit">
						{translate key="plugins.importexport.native.exportSubmissions"}
					</pkp-button>
				{/fbvFormSection}
			{/fbvFormArea}
		</form>
	</div>
</div>

{/block}
