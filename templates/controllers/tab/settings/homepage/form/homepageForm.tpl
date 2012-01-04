{**
 * controllers/tab/settings/homepage/form/homepageForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Homepage information and settings management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#homepageForm').pkpHandler('$.pkp.controllers.tab.settings.homepage.form.HomepageFormHandler');
	{rdelim});
</script>

<form id="homepageForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="homepage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="homepageFormNotification"}

	{fbvFormArea id="announcements"}
		{fbvFormSection list=true label="manager.setup.announcements" description="manager.setup.announcementsDescription"}
			{fbvElement type="checkbox" id="enableAnnouncements" label="manager.setup.enableAnnouncements" value="1" checked=$enableAnnouncements}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="enableAnnouncementsHomepage" label="manager.setup.enableAnnouncementsHomepage1" value="1" checked=$enableAnnouncementsHomepage inline=true}
			{fbvElement type="select" id="numAnnouncementsHomepage" from=$numAnnouncementsHomepageOptions selected=$numAnnouncementsHomepage defaultValue="1" translate=false disabled=$disableAnnouncementsHomepage size=$fbvStyles.size.MEDIUM inline=true}
			<p>{translate key="manager.setup.enableAnnouncementsHomepage2"}</p>
		{/fbvFormSection}
		{fbvFormSection description="manager.setup.announcementsIntroductionDescription"}
			{fbvElement type="textarea" multilingual="true" id="announcementsIntroduction" label="manager.setup.announcementsIntroduction" value=$announcementsIntroduction rich=true}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.information" description="manager.setup.information.description"}
			{fbvElement type="textarea" multilingual=true id="readerInformation" label="manager.setup.information.forReaders" value=$readerInformation rich=true}
			{fbvElement type="textarea" multilingual=true id="authorInformation" label="manager.setup.information.forAuthors" value=$authorInformation rich=true}
			{fbvElement type="textarea" multilingual=true id="librarianInformation" label="manager.setup.information.forLibrarians" value=$librarianInformation rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="homepageFormSubmit" submitText="common.save" hideCancel=true}
</form>
