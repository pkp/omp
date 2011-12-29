{**
 * catalog/form/catalogMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#catalogMetadataEntryForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="catalogMetadataEntryForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="catalogMetadataEntryFormNotification"}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="displayedInTab" value="{$formParams.displayedInTab|escape}" />
	<input type="hidden" name="tab" value="catalog" />

	{fbvFormArea id="audienceInformation" title="monograph.audience" border="true"}
		{fbvFormSection for="audience"}
			{fbvElement label="monograph.audience" type="select" from=$audienceCodes selected=$audience translate=0 id="audience" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeQualifier" type="select" from=$audienceRangeQualifiers selected=$audienceRangeQualifier translate=0 id="audienceRangeQualifier" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeFrom" type="select" from=$audienceRanges selected=$audienceRangeFrom translate=0 id="audienceRangeFrom" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeTo" type="select" from=$audienceRanges selected=$audienceRangeTo translate=0 id="audienceRangeTo" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeExact" type="select" from=$audienceRanges selected=$audienceRangeExact translate=0 id="audienceRangeExact" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="publicationFormats"}
		{fbvFormSection}
			<!--  Formats -->
			{url|assign:formatGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" monographId=$monographId}
			{load_url_in_div id="formatsGridContainer" url="$formatGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="catalogMetadataFormSubmit" submitText="common.save"}
</form>