{**
 * catalog/form/catalogMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#catalogMetadataEntryForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadCoverImage" escape=false stageId=$stageId monographId=$monographId}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="catalogMetadataEntryForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="catalogMetadataEntryFormNotification"}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="tabPos" value="1" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="catalog" />

	{fbvFormArea id="file"}
		{fbvFormSection title="monograph.coverImage"}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}
	{* Container for uploaded file *}
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

	{fbvFormArea id="audienceInformation" title="monograph.audience" border="true"}
		{fbvFormSection for="audience"}
			{fbvElement label="monograph.audience" type="select" from=$audienceCodes selected=$audience translate=false id="audience" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeQualifier" type="select" from=$audienceRangeQualifiers selected=$audienceRangeQualifier translate=false id="audienceRangeQualifier" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeFrom" type="select" from=$audienceRanges selected=$audienceRangeFrom translate=false id="audienceRangeFrom" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeTo" type="select" from=$audienceRanges selected=$audienceRangeTo translate=false id="audienceRangeTo" defaultValue="" defaultLabel=""}
			{fbvElement label="monograph.audience.rangeExact" type="select" from=$audienceRanges selected=$audienceRangeExact translate=false id="audienceRangeExact" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="representatives"}
		{fbvFormSection description="grid.catalogEntry.representativesDescription"}
			<!-- Representatives -->
			{assign var="divId" value="representativesGridContainer"|concat:$publicationFormatId|escape}
			{url|assign:representativesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.RepresentativesGridHandler" op="fetchGrid" monographId=$monographId}
			{load_url_in_div id="$divId" url="$representativesGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="publicationFormats"}
		{fbvFormSection}
			<!--  Formats -->
			{url|assign:formatGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" monographId=$monographId}
			{load_url_in_div id="formatsGridContainer"|uniqid url=$formatGridUrl}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="catalogInclusion"}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="isAvailable" checked=$isAvailable label="submission.catalogEntry.isAvailable"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="catalogMetadataFormSubmit" submitText="common.save"}
</form>
