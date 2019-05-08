{**
 * plugins/pubIds/urn/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * URN plugin settings
 *
 *}

<div id="description">{translate key="plugins.pubIds.urn.manager.settings.description"}</div>

<script src="{$baseUrl}/plugins/pubIds/urn/js/URNSettingsFormHandler.js"></script>
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#urnSettingsForm').pkpHandler('$.pkp.plugins.pubIds.urn.js.URNSettingsFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="urnSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="pubIds" plugin=$pluginName verb="save"}">
	{csrf}
	{include file="common/formErrors.tpl"}
	{fbvFormArea id="urnObjectsFormArea" title="plugins.pubIds.urn.manager.settings.urnObjects"}
		{fbvFormSection list="true"}
			<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.explainURNs"}</p>
			{fbvElement type="checkbox" id="enableSubmissionURN" label="plugins.pubIds.urn.manager.settings.enableSubmissionURN" maxlength="40" checked=$enableSubmissionURN|compare:true}
			{fbvElement type="checkbox" id="enableChapterURN" label="plugins.pubIds.urn.manager.settings.enableChapterURN" maxlength="40" checked=$enableChapterURN|compare:true}
			{fbvElement type="checkbox" id="enableRepresentationURN" label="plugins.pubIds.urn.manager.settings.enableRepresentationURN" maxlength="40" checked=$enableRepresentationURN|compare:true}
			{fbvElement type="checkbox" id="enableSubmissionFileURN" label="plugins.pubIds.urn.manager.settings.enableSubmissionFileURN" maxlength="40" checked=$enableSubmissionFileURN|compare:true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnPrefixFormArea" title="plugins.pubIds.urn.manager.settings.urnPrefix"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.urnPrefix.description"}</p>
			{fbvElement type="text" id="urnPrefix" value=$urnPrefix required="true" label="plugins.pubIds.urn.manager.settings.urnPrefix" maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnSuffixFormArea" title="plugins.pubIds.urn.manager.settings.urnSuffix"}
		<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.urnSuffix.description"}</p>
		{fbvFormSection list="true"}
			{if !in_array($urnSuffix, array("pattern", "customId"))}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}
			{fbvElement type="radio" id="urnSuffixDefault" name="urnSuffix" value="default" required="true" label="plugins.pubIds.urn.manager.settings.urnSuffixDefault" checked=$checked}
			<span class="instruct">{translate key="plugins.pubIds.urn.manager.settings.urnSuffixDefault.description"}</span>
		{/fbvFormSection}
		{fbvFormSection list="true"}
			{fbvElement type="radio" id="urnSuffixCustomId" name="urnSuffix" value="customId" required="true" label="plugins.pubIds.urn.manager.settings.urnSuffixCustomIdentifier" checked=$urnSuffix|compare:"customId"}
		{/fbvFormSection}
		{fbvFormSection list="true"}
			{fbvElement type="radio" id="urnSuffixPattern" name="urnSuffix" value="pattern" label="plugins.pubIds.urn.manager.settings.urnSuffixPattern" checked=$urnSuffix|compare:"pattern"}
			<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.urnSuffixPattern.example"}</p>
			{fbvElement type="text" id="urnSubmissionSuffixPattern" value=$urnSubmissionSuffixPattern label="plugins.pubIds.urn.manager.settings.urnSuffixPattern.submissions" maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="urnChapterSuffixPattern" value=$urnChapterSuffixPattern label="plugins.pubIds.urn.manager.settings.urnSuffixPattern.chapters" maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="urnRepresentationSuffixPattern" value=$urnRepresentationSuffixPattern label="plugins.pubIds.urn.manager.settings.urnSuffixPattern.representations" maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="urnSubmissionFileSuffixPattern" value=$urnSubmissionFileSuffixPattern label="plugins.pubIds.urn.manager.settings.urnSuffixPattern.files" maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnCheckNoFormArea" title="plugins.pubIds.urn.manager.settings.checkNo"}
		{fbvFormSection list="true" }
			{fbvElement type="checkbox" id="urnCheckNo" name="urnCheckNo" label="plugins.pubIds.urn.manager.settings.checkNo.label" checked=$urnCheckNo|compare:true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnNamespaceFormArea" title="plugins.pubIds.urn.manager.settings.namespace"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.namespace.description"}</p>
			{fbvElement type="select" id="urnNamespace" required="true" from=$urnNamespaces selected=$urnNamespace translate=false size=$fbvStyles.size.MEDIUM label="plugins.pubIds.urn.manager.settings.namespace"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnResolverFormArea" title="plugins.pubIds.urn.manager.settings.urnResolver"}
		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.pubIds.urn.manager.settings.urnResolver.description"}</p>
			{fbvElement type="text" id="urnResolver" value=$urnResolver required="true" label="plugins.pubIds.urn.manager.settings.urnResolver"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="urnReassignFormArea" title="plugins.pubIds.urn.manager.settings.urnReassign"}
		{fbvFormSection}
			<span class="instruct">{translate key="plugins.pubIds.urn.manager.settings.urnReassign.description"}</span><br/>
			{include file="linkAction/linkAction.tpl" action=$clearPubIdsLinkAction contextId="urnSettingsForm"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
