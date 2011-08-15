{**
 * controllers/tab/settings/pressIdentification/form/pressIdentificationForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press identification form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#pressIdentificationForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="pressIdentificationForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="pressIdentification"}">
	{include file="common/formErrors.tpl"}

	{fbvFormArea id="publicIdentifier"}
		{fbvFormSection list="true" label="manager.setup.uniqueIdentifier" description="manager.setup.uniqueIdentifierDescription"}
			{fbvElement type="checkbox" id="enablePublicMonographId" value="1" checked=$enablePublicMonographId label="manager.setup.enablePublicMonographId"}
			{fbvElement type="checkbox" id="enablePublicGalleyId" value="1" checked=$enablePublicGalleyId label="manager.setup.enablePublicGalleyId"}
		{/fbvFormSection}
		{fbvFormSection list="true" title="manager.setup.pageNumberIdentifier"}
			{fbvElement type="checkbox" id="enablePageNumber" value="1" checked=$enablePageNumber label="manager.setup.enablePageNumber"}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$wizardMode}
		{fbvFormButtons id="pressIdentificationFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>