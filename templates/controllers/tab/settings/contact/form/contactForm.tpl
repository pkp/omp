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
	{include file="controllers/tab/settings/wizardMode.tpl wizardMode=$wizardMode}

	<h3>{translate key="manager.setup.principalContact"}</h3>
	<p>{translate key="manager.setup.principalContactDescription"}</p>

	{fbvFormArea id="contactFormArea"}
		{fbvFormSection title="user.name" required=true for="contactName"}
			{fbvElement type="text" id="contactName" value=$contactName maxlength="60"}
		{/fbvFormSection}
		{fbvFormSection title="user.title" for="contactTitle"}
			{fbvElement type="text" multilingual=true name="contactTitle" id="contactTitle" value=$contactTitle maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.affiliation" for="contactAffiliation"}
			{fbvElement type="textarea" multilingual=true name="contactAffiliation" id="contactAffiliation" value=$contactAffiliation size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection title="user.email" for="contactEmail" required=true}
			{fbvElement type="text" id="contactEmail" value=$contactEmail maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.phone" for="contactPhone"}
			{fbvElement type="text" id="contactPhone" value=$contactPhone maxlength="24"}
		{/fbvFormSection}
		{fbvFormSection title="user.fax" for="contactFax"}
			{fbvElement type="text" id="contactFax" value=$contactFax maxlength="24"}
		{/fbvFormSection}
		{fbvFormSection title="common.mailingAddress" for="contactMailingAddress"}
			{fbvElement type="textarea" multilingual=true name="contactMailingAddress" id="contactMailingAddress" value=$contactMailingAddress size=$fbvStyles.size.SMALL  rich=true}
		{/fbvFormSection}

		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			<h3>{translate key="manager.setup.technicalSupportContact"}</h3>
			<p>{translate key="manager.setup.technicalSupportContactDescription"}</p>
			{fbvFormSection title="user.name" for="supportName" required=true}
				{fbvElement type="text" id="supportName" value=$supportName maxlength="60"}
			{/fbvFormSection}
			{fbvFormSection title="user.email" for="supportEmail" required=true}
				{fbvElement type="text" id="supportEmail" value=$supportEmail maxlength="90"}
			{/fbvFormSection}
			{fbvFormSection title="user.phone" for="supportPhone"}
				{fbvElement type="text" id="supportPhone" value=$supportPhone maxlength="24"}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{fbvFormButtons id="contactFormSubmit" submitText="common.save" hideCancel=true}
</form>