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
		$('#homepageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler', {ldelim}
			baseUrl: '{$baseUrl|escape:"javascript"}'
		{rdelim});
	{rdelim});
</script>

<form id="homepageForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="homepage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="homepageFormNotification"}

	{fbvFormArea id="information" title="manager.setup.information" class="border"}
		{fbvFormSection description="manager.setup.information.description"}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.information.forReaders"}
			{fbvElement type="textarea" multilingual=true id="readerInformation" value=$readerInformation rich=true}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.information.forAuthors"}
			{fbvElement type="textarea" multilingual=true id="authorInformation" value=$authorInformation rich=true}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.information.forLibrarians"}
			{fbvElement type="textarea" multilingual=true id="librarianInformation" value=$librarianInformation rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="homepageFormSubmit" submitText="common.save" hideCancel=true}
</form>
