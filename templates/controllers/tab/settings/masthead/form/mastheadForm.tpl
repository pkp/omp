{**
 * controllers/tab/settings/masthead/form/mastheadForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Masthead management form.
 *
 *}

{* Help Link *}
{help file="settings.md" section="context" class="pkp_help_tab"}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#mastheadForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="mastheadForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="masthead"}">
	{csrf}

	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="mastheadNotification"}

	{fbvFormArea id="mastheadNameContainer"}
		{fbvFormSection title="manager.setup.contextName" for="name" required=true inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" multilingual=true name="name" id="name" value=$name required=true}
		{/fbvFormSection}

		{fbvFormSection title="manager.setup.pressInitials" for="acronym" required=true inline=true size=$fbvStyles.size.SMALL}
			{fbvElement type="text" multilingual=true name="acronym" id="acronym" value=$acronym required=true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="mastheadDetailsContainer"}
		{fbvFormSection label="manager.setup.pressDescription" for="description" description="manager.setup.pressDescription.description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.editorialTeam" for="editorialTeam" description="manager.setup.editorialTeam.description"}
			{fbvElement type="textarea" multilingual=true id="editorialTeam" value=$editorialTeam rich=true}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.aboutPress" for="about" description="manager.setup.aboutPress.description"}
			{fbvElement type="textarea" multilingual=true name="about" id="about" value=$about rich="extended" rows=30}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$wizardMode}
		{fbvFormButtons id="mastheadFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
