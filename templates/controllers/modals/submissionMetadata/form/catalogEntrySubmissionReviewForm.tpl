{**
 * controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}
{* generate a unique ID for the form *}
{assign var="submissionMetadataViewFormId" value="submissionMetadataViewForm-"|uniqid|escape}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$submissionMetadataViewFormId}').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>
<form class="pkp_form" id="{$submissionMetadataViewFormId}" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{csrf}
	{assign var="notificationId" value="submissionMetadataViewFormNotification-"|uniqid|escape}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$notificationId}

	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="submission" />

	{assign var=submissionVersion value=$formParams.submissionVersion}
	<input type="hidden" name="submissionVersion" id="submissionVersion" value="{$submissionVersion|escape}" />

	{include file="core:submission/submissionLocale.tpl" readOnly=$formParams.readOnly}
	{include file="core:submission/submissionMetadataFormTitleFields.tpl" readOnly=$formParams.readOnly}
	{include file="submission/form/seriesAndCategories.tpl" readOnly=$formParams.readOnly includeSeriesPosition=true}

	<!--  Contributors -->

	{if !$formParams.hideSubmit || !$formParams.anonymous}
		{* generate a unique ID for the form *}
		{assign var="authorsGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{capture assign=authorGridUrl}{url router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" submissionId=$submissionId submissionVersion=$submissionVersion stageId=$stageId escape=false}{/capture}
		{load_url_in_div id=$authorsGridContainer url=$authorGridUrl}

		<!--  Chapters -->
		{assign var="chaptersGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{capture assign=chaptersGridUrl}{url router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submissionId submissionVersion=$submissionVersion escape=false}{/capture}
		{load_url_in_div id=$chaptersGridContainer url=$chaptersGridUrl}
	{/if}

	{include file="submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly metadataModal=true}

	{if !$formParams.hideSubmit}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{/if}

</form>
