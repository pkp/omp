{**
 * controllers/tab/settings/siteSetup/form/siteSetupForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site settings form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#siteSetupForm').pkpHandler('$.pkp.controllers.tab.settings.form.FileViewFormHandler',
			{ldelim}
				fetchFileUrl: {url|json_encode op="fetchFile" tab="siteSetup" escape=false},
			{rdelim}
		);
	{rdelim});
</script>

<form id="siteSetupForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AdminSettingsTabHandler" op="saveFormData" tab="siteSetup"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="siteSetupFormNotification"}

	{fbvFormSection title="admin.settings.siteTitle" required=true}
		{fbvElement type="text" id="title" name="title" value=$title required=true multilingual=true}
	{/fbvFormSection}

	{fbvFormSection label="admin.settings.siteLogo"}
		<div id="pageHeaderTitleImage">
			{$imageView}
		</div>
		<div id="{$uploadImageLinkAction->getId()}" class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$uploadImageLinkAction contextId="siteSetupForm"}
		</div>
	{/fbvFormSection}

	{fbvFormArea id="pressInformation"}
		{fbvFormSection title="admin.settings.aboutDescription"}
			{fbvElement type="textarea" multilingual=true id="about" value=$about}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="siteRedirection"}
		{fbvFormSection title="admin.settings.redirect"}
			{fbvElement type="select" id="redirect" from=$redirectOptions selected=$redirect translate=false defaultValue="" label="admin.settings.redirectInstructions" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="siteContact"}
		{fbvFormSection title="admin.settings.contactName" required=true}
			{fbvElement type="text" multilingual=true name="contactName" id="contatcName" value=$contactName size=$fbvStyles.size.MEDIUM required=true}
		{/fbvFormSection}
		{fbvFormSection title="admin.settings.contactEmail" required=true}
			{fbvElement type="text" multilingual=true name="contactEmail" id="contactEmail" value=$contactEmail size=$fbvStyles.size.MEDIUM required=true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="security"}
		{fbvFormSection title="admin.settings.minPasswordLength" required=true}
			{fbvElement type="text" id="minPasswordLength" value=$minPasswordLength size=$fbvStyles.size.MEDIUM required=true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="appearance"}
		{fbvFormSection title="admin.settings.siteStyleSheet"}
			<div id="{$uploadCssLinkAction->getId()}" class="pkp_linkActions">
				{include file="linkAction/linkAction.tpl" action=$uploadCssLinkAction contextId="siteSetupForm"}
			</div>
			<div id="siteStyleSheet">
				{$cssView}
			</div>
		{/fbvFormSection}
		{include file="controllers/tab/settings/appearance/form/theme.tpl"}
	{/fbvFormArea}

	{include file="core:statistics/defaultMetricTypeFormElements.tpl"}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{fbvFormButtons id="siteSetupFormSubmit" submitText="common.save" hideCancel=true}
</form>
