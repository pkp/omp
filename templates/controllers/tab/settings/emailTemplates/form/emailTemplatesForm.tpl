{**
 * controllers/tab/settings/emailTemplates/form/emailTemplatesForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Email templates management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#emailTemplatesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="emailTemplatesForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="emailTemplates"}">
	{include file="common/formErrors.tpl"}

	{url|assign:preparedEmailsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.preparedEmails.preparedEmailsGridHandler" op="fetchGrid"}
	{load_url_in_div id="preparedEmailsGridDiv" url=$preparedEmailsGridUrl}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.emails"}</h3>

	<p>{translate key="manager.setup.emailSignatureDescription"}</p>

	{fbvFormArea id="emails"}
		{fbvFormSection title="manager.setup.emailSignature" for="emailSignature"}
			{fbvElement type="textarea" id="emailSignature" value=$emailSignature size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.2OF3}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.emailBounceAddress" for="envelopeSender"}
			<p>{translate key="manager.setup.emailBounceAddressDescription"}</p>
			{fbvElement type="text" id="envelopeSender" value=$envelopeSender maxlength="90" disabled=$envelopeSenderDisabled size=$fbvStyles.size.LARGE}
			{if $envelopeSenderDisabled}
				<div class="pkp_helpers_clear"></div>
				<p>{translate key="manager.setup.emailBounceAddressDisabled"}</p>
			{/if}
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
