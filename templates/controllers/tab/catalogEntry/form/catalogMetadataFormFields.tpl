{**
 * catalog/form/catalogMetadataFormFields.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}
{capture assign="coverImageMessage"}{translate key="monograph.currentCoverImageReload"}{/capture}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#catalogMetadataEntryForm').pkpHandler(
			'$.pkp.controllers.catalog.form.CatalogMetadataFormHandler',
			{ldelim}
				trackFormChanges: true,
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: {url|json_encode router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" op="uploadCoverImage" escape=false stageId=$stageId submissionId=$submissionId},
					baseUrl: {$baseUrl|json_encode},
					filters: {ldelim}
						mime_types : [
							{ldelim} title : "Image files", extensions : "jpg,jpeg,png,svg" {rdelim}
						]
					{rdelim}
				{rdelim},
				arePermissionsAttached: {if $arePermissionsAttached}true{else}false{/if},
				coverImageMessage: "{$coverImageMessage|escape:"javascript"}",
				workTypeEditedVolume: {$smarty.const.WORK_TYPE_EDITED_VOLUME|escape:"javascript"},
				workTypeAuthoredWork: {$smarty.const.WORK_TYPE_AUTHORED_WORK|escape:"javascript"},
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="catalogMetadataEntryForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" op="saveForm"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="catalogMetadataFormFieldsNotification"}

	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="submissionVersion" value="{$submissionVersion|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="tabPos" value="1" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="catalog" />

	{if !$formParams.hideSubmit}
		{fbvFormSection title="submission.catalogEntry" list="true"}
			{fbvElement type="checkbox" id="confirm" checked=$confirm label="submission.catalogEntry.confirm" value="confirm"}
		{/fbvFormSection}
	{/if}

	{fbvFormSection title="catalog.published"}
		{fbvElement type="text" id="datePublished" value=$datePublished|date_format:$dateFormatShort class="datepicker"}
	{/fbvFormSection}

	{fbvFormSection label="submission.workflowType"}
		{fbvElement type="select" id="workType" from=$workTypeOptions selected=$workType translate=false disabled=$formParams.readOnly size=$fbvStyles.size.SMALL}
	{/fbvFormSection}

	{fbvFormSection id="volumeEditors"}
		{assign var="uuid" value=""|uniqid|escape}
		<div id="volume-editors-{$uuid}">
			<script type="text/javascript">
				pkp.registry.init('volume-editors-{$uuid}', 'SelectListPanel', {$volumeEditorsListData|json_encode});
			</script>
		</div>
	{/fbvFormSection}

	{fbvFormArea id="permissions" title="submission.permissions" class="border"}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="attachPermissions" label="submission.attachPermissions"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="licenseURL" label="submission.licenseURL" value=$licenseURL}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="copyrightHolder" label="submission.copyrightHolder" value=$copyrightHolder multilingual=true size=$fbvStyles.size.MEDIUM inline=true}
			{fbvElement type="text" id="copyrightYear" label="submission.copyrightYear" value=$copyrightYear size=$fbvStyles.size.SMALL inline=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormSection title="monograph.coverImage"}
		<div class="currentCoverImage">
			{capture assign="altTitle"}{translate key="submission.currentCoverImage"}{/capture}
			<img height="{$coverImage.thumbnailHeight}" width="{$coverImage.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$submissionId random=$submissionId|uniqid}" alt="{$altTitle|escape}" />
			<span class="coverImageMessage description"></span>
		</div>
		{include file="controllers/fileUploadContainer.tpl" id="plupload"}
	{/fbvFormSection}

	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

	<div class="pkp_helpers_clear"></div>

	{fbvFormArea id="audienceInformation" title="monograph.audience" class="border"}
		{fbvFormSection for="audience"}
			{fbvElement label="monograph.audience" type="select" from=$audienceCodes selected=$audience translate=false id="audience" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement label="monograph.audience.rangeQualifier" type="select" from=$audienceRangeQualifiers selected=$audienceRangeQualifier translate=false id="audienceRangeQualifier" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement label="monograph.audience.rangeFrom" type="select" from=$audienceRanges selected=$audienceRangeFrom translate=false id="audienceRangeFrom" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement label="monograph.audience.rangeTo" type="select" from=$audienceRanges selected=$audienceRangeTo translate=false id="audienceRangeTo" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement label="monograph.audience.rangeExact" type="select" from=$audienceRanges selected=$audienceRangeExact translate=false id="audienceRangeExact" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="representatives"}
		{fbvFormSection description="grid.catalogEntry.representativesDescription"}
			<!-- Representatives -->
			{assign var="divId" value="representativesGridContainer"|concat:$representationId|escape}
			{capture assign=representativesGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.RepresentativesGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}{/capture}
			{load_url_in_div id=$divId url=$representativesGridUrl}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$formParams.hideSubmit}
		{fbvFormButtons id="catalogMetadataFormSubmit" submitText="common.save"}
	{/if}
</form>
