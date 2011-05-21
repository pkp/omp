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

<form id="pressIdentificationForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="pressIdentification"}">
	{include file="common/formErrors.tpl"}

	<h3>{translate key="manager.setup.publicIdentifier"}</h3>

	<p>{translate key="manager.setup.uniqueIdentifierDescription"}</p>

	{fbvFormArea id="publicIdentifier"}
		{fbvFormSection title="manager.setup.uniqueIdentifier" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="enablePublicMonographId" value="1" checked=$enablePublicMonographId label="manager.setup.enablePublicMonographId"}
			{fbvElement type="checkbox" id="enablePublicGalleyId" value="1" checked=$enablePublicGalleyId label="manager.setup.enablePublicGalleyId"}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.pageNumberIdentifier" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="enablePageNumber" value="1" checked=$enablePageNumber label="manager.setup.enablePageNumber"}
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>