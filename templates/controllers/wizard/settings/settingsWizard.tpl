{**
 * templates/controllers/wizard/settings/settingsWizard.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The settings wizard page.
 *}
{assign var="pageTitle" value="manager.settings.wizard"}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#settingsWizard').pkpHandler(
				'$.pkp.controllers.wizard.WizardHandler',
			{ldelim}
				cancelButtonText: '{translate|escape:javascript key="common.cancel"}',
				continueButtonText: '{translate|escape:javascript key="common.continue"}',
				finishButtonText: '{translate|escape:javascript key="common.finish"}'
			{rdelim});
	{rdelim});
</script>
<div id="settingsWizard" class="pkp_controllers_tab">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="masthead" wizardMode=true}">{translate key="manager.setup.masthead"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="contact" wizardMode=true}">{translate key="about.contact"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="showTab" tab="appearance" wizardMode=true}">{translate key="manager.website.appearance"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="submissionStage" wizardMode=true}">{translate key="manager.publication.submissionStage"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="showTab" tab="indexing" wizardMode=true}">{translate key="manager.distribution.indexing"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AccessSettingsTabHandler" op="showTab" tab="users" wizardMode=true}">{translate key="manager.users"}</a></li>
	</ul>
</div>
