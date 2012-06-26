{**
 * controllers/tab/settings/homepage/form/announcementSettingsForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement settings form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#announcementSettingsForm').pkpHandler('$.pkp.controllers.tab.settings.announcements.form.AnnouncementSettingsFormHandler');
	{rdelim});
</script>

<form id="announcementSettingsForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="homepage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="announcementSettingsFormNotification"}

	{fbvFormArea id="announcements" border="true" title="manager.setup.announcements"}
		{fbvFormSection list=true description="manager.setup.announcementsDescription"}
			{fbvElement type="checkbox" id="enableAnnouncements" label="manager.setup.enableAnnouncements" value="1" checked=$enableAnnouncements}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="enableAnnouncementsHomepage" label="manager.setup.enableAnnouncementsHomepage1" value="1" checked=$enableAnnouncementsHomepage inline=true}
			{fbvElement type="select" id="numAnnouncementsHomepage" from=$numAnnouncementsHomepageOptions selected=$numAnnouncementsHomepage defaultValue="1" translate=false disabled=$disableAnnouncementsHomepage size=$fbvStyles.size.SMALL inline=true}
			<p>{translate key="manager.setup.enableAnnouncementsHomepage2"}</p>
		{/fbvFormSection}
		{fbvFormSection description="manager.setup.announcementsIntroductionDescription"}
			{fbvElement type="textarea" multilingual="true" id="announcementsIntroduction" value=$announcementsIntroduction rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:announcementTypeGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.content.announcements.AnnouncementTypeGridHandler" op="fetchGrid"}
	{load_url_in_div id="announcementTypeGridContainer" url="$announcementTypeGridUrl"}

	{url|assign:announcementGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.content.announcements.ManageAnnouncementGridHandler" op="fetchGrid"}
	{load_url_in_div id="announcementGridContainer" url="$announcementGridUrl"}

	{fbvFormButtons id="announcementSettingsFormSubmit" submitText="common.save" hideCancel=true}
</form>
