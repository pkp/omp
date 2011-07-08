{**
 * controllers/tab/settings/indexing/form/indexingForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Indexing management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#indexingForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="indexingForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="saveFormData" tab="indexing"}">
	{include file="common/formErrors.tpl"}
	{include file="controllers/tab/settings/wizardMode.tpl wizardMode=$wizardMode}

	<h3>{translate key="manager.setup.cataloguingMetadata"}</h3>

	{url|assign:cataloguingMetadataUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.CataloguingMetadataListbuilderHandler" op="fetch"}
	{load_url_in_div id="cataloguingMetadataContainer" url=$cataloguingMetadataUrl}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.searchEngineIndexing"}</h3>

	<p>{translate key="manager.setup.searchEngineIndexingDescription"}</p>

	{fbvFormArea id="searchEngineIndexing"}
		{fbvFormSection title="common.description"}
			{fbvElement type="text" multilingual="true" id="searchDescription" name="searchDescription" value=$searchDescription size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection title="common.keywords"}
			{fbvElement type="text" multilingual="true" id="searchKeywords" name="searchKeywords" value=$searchKeywords size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.customTags"}
			{fbvElement type="textarea" multilingual="true" id="customHeaders" name="customHeaders" value=$customHeaders}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		<h3>{translate key="manager.setup.registerPressForIndexing"}</h3>

		{url|assign:"oaiSiteUrl" press=$currentPress->getPath()}
		{url|assign:"oaiUrl" page="oai"}
		<p>{translate key="manager.setup.registerPressForIndexingDescription" siteUrl=$oaiSiteUrl oaiUrl=$oaiUrl}</p>
	</div>

	{fbvFormButtons id="indexingFormSubmit" submitText="common.save" hideCancel=true}
</form>