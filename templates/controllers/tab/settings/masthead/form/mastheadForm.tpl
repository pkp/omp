{**
 * controllers/tab/settings/masthead/form/mastheadForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Masthead management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#mastheadForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="mastheadForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="masthead"}">
	{include file="common/formErrors.tpl"}

	<h3>1.1 {translate key="manager.setup.generalInformation"}</h3>

	{fbvFormArea id="generalInformation"}
		{fbvFormSection title="manager.setup.pressName" for="name" required=true}
			{fbvElement type="text" multilingual=true name="name" id="name" value=$name maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.pressInitials" for="initials" required=true}
			{fbvElement type="text" multilingual=true name="initials" id="initials" value=$initials maxlength="16" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.pressDescription" for="description" float=$fbvStyles.float.LEFT}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
		{/fbvFormSection}
		{fbvFormSection title="common.mailingAddress" for="mailingAddress" group=true float=$fbvStyles.float.RIGHT}
			{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.1OF1}
			<p>{translate key="manager.setup.mailingAddressDescription"}</p>
		{/fbvFormSection}
		{fbvFormSection layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="pressEnabled" value="1" checked=$pressEnabled label="manager.setup.enablePressInstructions"}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	{url|assign:mastheadGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.mastheadGridHandler" op="fetchGrid"}
	{load_url_in_div id="mastheadGridDiv" url=$mastheadGridUrl}

	<div class="separator"></div>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
