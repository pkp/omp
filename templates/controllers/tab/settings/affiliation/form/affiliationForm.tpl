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

	{fbvFormArea id="sponsorsFormArea"}
		{fbvFormSection label="manager.setup.sponsors" description="manager.setup.sponsorsDescription"}
			{fbvElement type="textarea" multilingual=true id="sponsorNote" label="manager.setup.note" value=$sponsorNote rich=true}
			{url|assign:sponsorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.sponsorGridHandler" op="fetchGrid"}
			{load_url_in_div id="sponsorGridDiv" url=$sponsorGridUrl}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.contributors" description="manager.setup.contributorsDescription"}
			{fbvElement type="textarea" multilingual=true id="contributorNote" label="manager.setup.note" value=$contributorNote rich=true}
			{url|assign:contributorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="fetchGrid"}
			{load_url_in_div id="contributorGridDiv" url=$contributorGridUrl}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="affiliationFormSubmit" submitText="common.save" hideCancel=true}
</form>