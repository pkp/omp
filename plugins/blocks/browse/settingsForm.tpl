{**
 * plugins/blocks/browse/settingsForm.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Browse block plugin settings
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#browseBlockSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="browseBlockSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="plugin" category="blocks" plugin=$pluginName verb="settings" save="true"}">
	{include file="common/formErrors.tpl"}
	{fbvFormArea id="browseBlockSettingsFormArea" class="border" title="plugins.block.browse.settings.title"}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="browseNewReleases" value="1" checked=$browseNewReleases label="plugins.block.browse.newReleases"}
			{fbvElement type="checkbox" id="browseCategories" value="1" checked=$browseCategories label="plugins.block.browse.category"}
			{fbvElement type="checkbox" id="browseSeries" value="1" checked=$browseSeries label="plugins.block.browse.series"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
