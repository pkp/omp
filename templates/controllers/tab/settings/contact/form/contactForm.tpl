{**
 * controllers/tab/settings/contact/form/contact.tpl
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

<form id="contactForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="contact"}">
	{include file="common/formErrors.tpl"}

	<h3>1.1 {translate key="manager.setup.principalContact"}</h3>
	<p>{translate key="manager.setup.principalContactDescription"}</p>

	{fbvFormArea id="principalContact"}
		{fbvFormSection title="user.name" required=true for="contactName"}
			{fbvElement type="text" id="contactName" value=$contactName maxlength="60"}
		{/fbvFormSection}
		{fbvFormSection title="user.title" for="contactTitle"}
			{fbvTextInput multilingual=true name="contactTitle" id="contactTitle" value=$contactTitle maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.affiliation" for="contactAffiliation"}
			{fbvTextArea multilingual=true name="contactAffiliation" id="contactAffiliation" value=$contactAffiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2}
		{/fbvFormSection}
		{fbvFormSection title="user.email" for="contactEmail" required=true}
			{fbvElement type="text" id="contactEmail" value=$contactEmail maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.phone" for="contactPhone" float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="contactPhone" value=$contactPhone maxlength="24"}
		{/fbvFormSection}
		{fbvFormSection title="user.fax" for="contactFax" float=$fbvStyles.float.RIGHT}
			{fbvElement type="text" id="contactFax" value=$contactFax maxlength="24"}
		{/fbvFormSection}
		{fbvFormSection title="common.mailingAddress" for="contactMailingAddress"}
			{fbvTextArea multilingual=true name="contactMailingAddress" id="contactMailingAddress" value=$contactMailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF2 rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	<h3>1.2 {translate key="manager.setup.technicalSupportContact"}</h3>

	<p>{translate key="manager.setup.technicalSupportContactDescription"}</p>

	{fbvFormArea id="technicalSupportContact"}
		{fbvFormSection title="user.name" for="supportName" required=true}
			{fbvElement type="text" id="supportName" value=$supportName maxlength="60"}
		{/fbvFormSection}
		{fbvFormSection title="user.email" for="supportEmail" required=true float=$fbvStyles.float.LEFT}
			{fbvElement type="text" id="supportEmail" value=$supportEmail maxlength="90"}
		{/fbvFormSection}
		{fbvFormSection title="user.phone" for="supportPhone" float=$fbvStyles.float.RIGHT}
			{fbvElement type="text" id="supportPhone" value=$supportPhone maxlength="24"}
		{/fbvFormSection}
	{/fbvFormArea}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{include file="form/formButtons.tpl" submitText="common.save"}
</form>