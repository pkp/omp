{**
 * controllers/tab/settings/homepage/form/homepageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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

<form class="pkp_form pkp_controllers_form" id="homepageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="homepage"}">
	{include file="common/formErrors.tpl"}

	<h3>{translate key="manager.setup.announcements"}</h3>

	<p>{translate key="manager.setup.announcementsDescription"}</p>

	{fbvFormArea id="toggleAnnouncements"}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="enableAnnouncements" name="enableAnnouncements" label="manager.setup.enableAnnouncements" value="1" checked=$enableAnnouncements}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="checkbox" id="enableAnnouncementsHomepage" name="enableAnnouncementsHomepage" label="manager.setup.enableAnnouncementsHomepage1" value="1" checked=$enableAnnouncementsHomepage}
			{fbvElement type="select" id="numAnnouncementsHomepage" name="numAnnouncementsHomepage" from=$numAnnouncementsHomepageOptions selected=$numAnnouncementsHomepage defaultValue="1" translate=false disabled=$disableAnnouncementsHomepage}
			<p>{translate key="manager.setup.enableAnnouncementsHomepage2"}</p>
		{/fbvFormSection}
	{/fbvFormArea}

	<p>{translate key="manager.setup.announcementsIntroductionDescription"}</p>

	{fbvFormArea id="announcementsIntroductionContainer"}
		{fbvFormSection title="manager.setup.announcementsIntroduction"}
			{fbvElement type="textarea" multilingual="true" name="announcementsIntroduction" id="announcementsIntroduction" value=$announcementsIntroduction size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	<h3>{translate key="manager.setup.information"}</h3>
	<p>{translate key="manager.setup.information.description"}</p>
	{fbvFormArea id="information"}
		{fbvFormSection title="manager.setup.information.forReaders"}
			{fbvElement type="textarea" multilingual=true name="readerInformation" id="readerInformation" value=$readerInformation size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.information.forAuthors"}
			{fbvElement type="textarea" multilingual=true name="authorInformation" id="authorInformation" value=$authorInformation size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.information.forLibrarians"}
			{fbvElement type="textarea" multilingual=true name="librarianInformation" id="librarianInformation" value=$librarianInformation size=$fbvStyles.size.MEDIUM  rich=true}
		{/fbvFormSection}
	{/fbvFormArea}


	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
