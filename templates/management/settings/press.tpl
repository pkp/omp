{**
 * templates/management/settings/press.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The press settings page.
 *}
{include file="common/header.tpl" pageTitle="manager.settings.pressSettings"}

{if $newVersionAvailable}
	<div class="pkp_notification">
		{translate|assign:"notificationContents" key="site.upgradeAvailable.manager" currentVersion=$currentVersion latestVersion=$latestVersion siteAdminName=$siteAdmin->getFullName() siteAdminEmail=$siteAdmin->getEmail()}
		{include file="controllers/notification/inPlaceNotificationContent.tpl" notificationId="upgradeWarning-"|uniqid notificationStyleClass="notifyWarning" notificationTitle="common.warning"|translate notificationContents=$notificationContents}
	</div>
{/if}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#pressSettingsTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="pressSettingsTabs" class="pkp_controllers_tab">
	<ul>
		<li><a name="masthead" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="masthead"}">{translate key="manager.setup.masthead"}</a></li>
		<li><a name="contact" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="contact"}">{translate key="about.contact"}</a></li>
		<li><a name="policies" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="policies"}">{translate key="about.policies"}</a></li>
		<li><a name="guidelines" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="guidelines"}">{translate key="about.guidelines"}</a></li>
		<li><a name="series" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="series"}">{translate key="series.series"}</a></li>
		<li><a name="categories" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="categories"}">{translate key="grid.category.categories"}</a></li>
		<li><a name="affiliationAndSupport" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="affiliationAndSupport"}">{translate key="manager.affiliationAndSupport"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
