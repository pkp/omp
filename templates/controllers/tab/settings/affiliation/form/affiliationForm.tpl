{**
 * controllers/tab/settings/affiliation/form/affiliationForm.tpl
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
		$('#affiliationForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="affiliationForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="affiliationAndSupport"}">
	<h3>{translate key="manager.setup.sponsors"}</h3>
	<p>{translate key="manager.setup.sponsorsDescription"}</p>

	{fbvFormArea id="sponsors"}
		{fbvFormSection title="manager.setup.note" for="sponsorNote"}
			{fbvElement type="textarea" multilingual=true name="sponsorNote" id="sponsorNote" value=$sponsorNote size=$fbvStyles.size.SMALL rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:sponsorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.sponsorGridHandler" op="fetchGrid"}
	{load_url_in_div id="sponsorGridDiv" url=$sponsorGridUrl}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.contributors"}</h3>
	<p>{translate key="manager.setup.contributorsDescription"}</p>

	{fbvFormArea id="contributor"}
		{fbvFormSection title="manager.setup.note" for="contributorNote"}
			{fbvElement type="textarea" id="contributorNote" multilingual=true name="contributorNote" value=$contributorNote size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:contributorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="fetchGrid"}
	{load_url_in_div id="contributorGridDiv" url=$contributorGridUrl}

	{fbvFormButtons id="affiliationFormSubmit" submitText="common.save" hideCancel=true}
</form>