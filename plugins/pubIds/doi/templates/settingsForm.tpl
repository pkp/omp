{**
 * plugins/pubIds/doi/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * DOI plugin settings
 *
 *}
<div id="description">{translate key="plugins.pubIds.doi.manager.settings.description"}</div>

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#doiSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="doiSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="pubIds" plugin=$pluginName verb="settings" save="true"}">
	{include file="common/formErrors.tpl"}
	{fbvFormArea id="enableDoiSettingsFormArea" class="border" title="plugins.pubIds.doi.manager.settings.doiSettings"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.pubIds.doi.manager.settings.doiPrefixPattern"}</p>
			{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiPrefix" required="true" id="doiPrefix" value=$doiPrefix maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="doiSuffixPatternFormArea" class="border" title="plugins.pubIds.doi.manager.settings.doiSuffix"}
		{fbvFormSection label="plugins.pubIds.doi.manager.settings.doiSuffixDescription" list="true"}
			{if $doiSuffix eq "pattern"}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}
			{fbvElement type="radio" id="doiSuffix" name="doiSuffix" value="pattern" checked=$checked label="plugins.pubIds.doi.manager.settings.doiSuffixPattern"}
		{/fbvFormSection}
		{fbvFormSection}
			<p class="pkp_help">{fieldLabel name="doiSuffixPattern" key="plugins.pubIds.doi.manager.settings.doiSuffixPattern.example"}</p>
			{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiSuffixPattern.publicationFormats" id="doiPublicationFormatSuffixPattern" value=$doiPublicationFormatSuffixPattern maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection list="true"}
			{if !in_array($doiSuffix, array("pattern", "publisherId", "customId"))}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}
			{fbvElement type="radio" id="doiSuffixDefault" name="doiSuffix" required="true" value="default" checked=$checked label="plugins.pubIds.doi.manager.settings.doiSuffixDefault"}
			<span class="instruct">{translate key="plugins.pubIds.doi.manager.settings.doiSuffixDefault.description"}</span>
			{if $doiSuffix eq "customId"}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}
			{fbvElement type="radio" id="doiSuffixCustomIdentifier" name="doiSuffix" required="true" value="customId" checked=$checked label="plugins.pubIds.doi.manager.settings.doiSuffixCustomIdentifier"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="doiSuffixReassignFormArea" class="border" title="plugins.pubIds.doi.manager.settings.doiReassign"}
		{fbvFormSection}
			<span class="instruct">{translate key="plugins.pubIds.doi.manager.settings.doiReassign.description"}</span><br/>
			{include file="linkAction/linkAction.tpl" action=$clearPubIdsLinkAction contextId="doiSettingsForm"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
