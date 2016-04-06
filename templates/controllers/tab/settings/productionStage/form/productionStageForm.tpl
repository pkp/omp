{**
 * controllers/tab/settings/productionStage/form/productionStageForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production Stage settings management form.
 *
 *}

{* Help Link *}
{help file="settings.md" section="workflow" class="pkp_help_tab"}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#productionStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="productionStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="productionStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionStageFormNotification"}

	<p class="pkp_help">{translate key="manager.settings.publisherInformation"}</p>
	{fbvFormArea id="publisherInformation"}
		{fbvFormSection id="publisher" label="manager.settings.publisher"}
			{fbvElement type="text" name="publisher" id="publisher" value=$publisher maxlength="255"}
		{/fbvFormSection}
		{fbvFormSection id="location" label="manager.settings.location"}
			{fbvElement type="text" name="location" id="location" value=$location maxlength="255"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="audienceInformation" title="manager.settings.publisherCode"}
		{fbvFormSection for="codeType" description="manager.settings.publisherCodeType.tip"}
			{fbvElement type="select" from=$codeTypes selected=$codeType translate=false id="codeType" defaultValue="" defaultLabel="" label="manager.settings.publisherCodeType"}
		{/fbvFormSection}
		{fbvFormSection for="codeValue"}
			{fbvElement type="text" id="codeValue" value=$codeValue label="manager.settings.publisherCode"}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	{if !$wizardMode}
		{fbvFormButtons id="productionStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
