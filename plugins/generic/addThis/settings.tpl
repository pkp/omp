{**
 * plugins/generic/addThis/settings.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The basic setting tab for the AddThis plugin.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addThisPluginSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="addThisPluginSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="plugin" category="generic" plugin=$pluginName verb="showTab" tab="basic" save="true"}">
	<input type="hidden" name="tab" value="settings" />
	{fbvFormArea id="addThisDisplayStyle" title="plugins.generic.addThis.settings.displayStyle" class="border"}
			{foreach from=$displayStyles key=style item=image}
				{fbvFormSection list="true"}
					{if $style == $addThisDisplayStyle}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
					{capture assign="content"}<img src="{$pluginBaseUrl|escape}/{$image|escape}" />{/capture}
					{fbvElement type="radio" name="addThisDisplayStyle" id="displayStyle-$style" value=$style checked=$checked translate=false content=$content inline=true}
				{/fbvFormSection}
			{/foreach}
	{/fbvFormArea}
		<p>{translate key="plugins.generic.addThis.form.registerLink"}</p>
	{fbvFormArea id="addThisStatistics" title="plugins.generic.addThis.settings.statistics" class="border"}
		{fbvFormSection for="addThisStatistics"}
			{fbvElement type="text" label="plugins.generic.addThis.form.profileId" id="addThisProfileId" value=$addThisProfileId size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" label="plugins.generic.addThis.form.username" id="addThisUsername" value=$addThisUsername size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" password="true" label="plugins.generic.addThis.form.password" value=$addThisPassword id="addThisPassword" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormButtons submitText="common.save"}
	{/fbvFormArea}
</form>
