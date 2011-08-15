{**
 * controllers/tab/settings/contact/form/contactForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Contact management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#contactForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="contactForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="contact"}">
	{include file="common/formErrors.tpl"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{fbvFormArea id="contactFormArea"}
		{fbvFormSection label="manager.setup.principalContact" description="manager.setup.principalContactDescription"}
			{fbvElement type="text" multilingual=true id="contactTitle" label="user.title" value=$contactTitle inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="contactName" label="user.name" value=$contactName required=true inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="contactEmail" label="user.email" value=$contactEmail required=true inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="contactPhone" label="user.phone" value=$contactPhone inline=true}
			{fbvElement type="text" id="contactFax" label="user.fax" value=$contactFax inline=true}
			{fbvElement type="textarea" multilingual=true id="contactAffiliation" label="user.affiliation" value=$contactAffiliation}
			{fbvElement type="textarea" multilingual=true id="contactMailingAddress" label="common.mailingAddress" value=$contactMailingAddress rich=true}
		{/fbvFormSection}

		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="manager.setup.technicalSupportContact" description="manager.setup.technicalSupportContactDescription"}
				{fbvElement type="text" id="supportName" label="user.name" value=$supportName required=true inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="text" id="supportEmail" label="user.email" value=$supportEmail required=true inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="text" id="supportPhone" label="user.phone" value=$supportPhone inline=true size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{if !$wizardMode}
		{fbvFormButtons id="contactFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>