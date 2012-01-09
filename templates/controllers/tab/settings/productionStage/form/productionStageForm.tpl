{**
 * controllers/tab/settings/productionStage/form/productionStageForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production Stage settings management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#productionStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="productionStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="productionStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionStageFormNotification"}

	{fbvFormArea id="publisherInformation" title="manager.settings.publisher"}
		{fbvFormSection id="publisher"}
			{fbvElement type="text" multilingual=true name="publisher" required="true" id="publisher" value=$publisher maxlength="255"}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:productionLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION}
	{load_url_in_div id="productionLibraryGridDiv" url=$productionLibraryGridUrl}

	<div class="separator"></div>

	<p>{translate key="manager.setup.publicationFormatsDescription"}</p>

	{url|assign:publicationFormatsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.PublicationFormatsListbuilderHandler" op="fetch"}
	{load_url_in_div id="publicationFormatsContainer" url=$publicationFormatsUrl}

	<div class="separator"></div>

	{url|assign:productionTemplateLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION_TEMPLATE}
	{load_url_in_div id="productionTemplateLibraryDiv" url=$productionTemplateLibraryUrl}

	{if !$wizardMode}
		{fbvFormButtons id="productionStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>