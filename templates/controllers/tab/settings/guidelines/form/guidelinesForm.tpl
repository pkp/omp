{**
 * controllers/tab/settings/guidelines/form/guidelinesForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Guidelines management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#guidelinesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="guidelinesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="guidelines"}">
	{include file="common/formErrors.tpl"}
	<h3>{translate key="manager.setup.authorGuidelines"}</h3>

	<p>{translate key="manager.setup.authorGuidelinesDescription"}</p>

	{fbvFormArea id="focusAndScopeDescription"}
		{fbvFormSection}
			{fbvElement type="textarea" multilingual=true name="authorGuidelines" id="authorGuidelines" value=$authorGuidelines size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="guidelinesFormSubmit" submitText="common.save" hideCancel=true}
</form>
