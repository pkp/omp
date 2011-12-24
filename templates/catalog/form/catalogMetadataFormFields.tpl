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

	{fbvFormArea id="audienceInformation"}
		{fbvFormSection title="monograph.audience" for="audience"}
			{fbvElement type="select" from=$audienceCodes selected=$audience translate=0 id="audience" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection title="monograph.audience.rangeQualifier" for="audienceRangeQualifier"}
			{fbvElement type="select" from=$audienceRangeQualifiers selected=$audienceRangeQualifier translate=0 id="audienceRangeQualifier" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection title="monograph.audience.rangeFrom" for="audienceRangeFrom"}
			{fbvElement type="select" from=$audienceRanges selected=$audienceRangeFrom translate=0 id="audienceRangeFrom" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection title="monograph.audience.rangeTo" for="audienceRangeTo"}
			{fbvElement type="select" from=$audienceRanges selected=$audienceRangeTo translate=0 id="audienceRangeTo" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection title="monograph.audience.rangeExact" for="audienceRangeExact"}
			{fbvElement type="select" from=$audienceRanges selected=$audienceRangeExact translate=0 id="audienceRangeExact" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="publicationFormats"}
		{fbvFormSection title="monograph.publicationFormats" size=$fbvStyles.size.MEDIUM}
			<div id="publicationFormatsContainer">
				{url|assign:publicationFormatsUrl router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.PublicationFormatsListbuilderHandler" op="fetch" monographId=$monographId}
				{load_url_in_div id="publicationFormatsContainer" url=$publicationFormatsUrl}
			</div>
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="catalogMetadataFormSubmit" submitText="common.save"}
</form>