{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Website appearance management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#appearanceForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="appearanceForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="appearance"}">
	{include file="common/formErrors.tpl"}

	<h3>{translate key="manager.setup.pressHomepageHeader"}</h3>
	<p>{translate key="manager.setup.pressHomepageHeaderDescription"}</p>
	<h4>{translate key="manager.setup.pressName"}</h4>
	{fbvFormArea id="homepageHeader"}
		{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS measure=$fbvStyles.measure.1OF2}
				{fbvElement type="radio" name="homeHeaderTitleType" id="homeHeaderTitleType-0" value=0 checked=!$homeHeaderTitleType label="manager.setup.useTextTitle"}
				{fbvElement type="text" name="homeHeaderTitle" id="homeHeaderTitle" value=$homeHeaderTitle multilingual=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>