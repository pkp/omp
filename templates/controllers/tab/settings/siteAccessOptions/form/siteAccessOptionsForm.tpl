{**
 * controllers/tab/settings/siteAccessOptions/form/siteAccessOptionsForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site access options management form.
 *
 *}

{* Help Link *}
{help file="settings.md" section="users" class="pkp_help_tab"}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#siteAccessOptionsForm').pkpHandler('$.pkp.controllers.tab.settings.siteAccessOptions.form.SiteAccessOptionsFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="siteAccessOptionsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AccessSettingsTabHandler" op="saveFormData" tab="siteAccessOptions"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="siteAccessOptionsFormNotification"}

	{fbvFormArea id="siteAccess" class="border" title="manager.setup.siteAccess"}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="restrictSiteAccess" value="1" checked=$restrictSiteAccess label="manager.setup.restrictSiteAccess"}
			{fbvElement type="checkbox" id="restrictMonographAccess" value="1" checked=$restrictMonographAccess label="manager.setup.restrictMonographAccess"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="userRegistration" class="border" title="manager.setup.userRegistration"}
		{fbvFormSection list=true}
			{fbvElement type="radio" id="disableUserReg-0" name="disableUserReg" value="0" checked=!$disableUserReg label="manager.setup.enableUserRegistration"}
			{fbvElement type="radio" id="disableUserReg-1" name="disableUserReg" value="1" checked=$disableUserReg label="manager.setup.disableUserRegistration"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="siteAccessFormSubmit" submitText="common.save" hideCancel=true}
</form>
