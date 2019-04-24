{**
 * controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's metadata form.
 *
 *}
{* generate a unique ID for the form *}
{assign var="submissionMetadataViewFormId" value="submissionMetadataViewForm-"|uniqid|escape}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$submissionMetadataViewFormId}').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="{$submissionMetadataViewFormId}" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{csrf}
	{assign var="notificationId" value="submissionMetadataViewFormNotification-"|uniqid|escape}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$notificationId}

	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	{include file="submission/form/seriesAndCategories.tpl" readOnly=$formParams.readOnly includeSeriesPosition=true}

	{include file="core:submission/submissionMetadataFormTitleFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.anonymous}
		<!--  Contributors -->
		{* generate a unique ID for the form *}
		{assign var="authorsGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{capture assign=authorGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.author.AuthorGridHandler" op="fetchGrid" submissionId=$submissionId submissionVersion=$submissionVersion stageId=$stageId escape=false}{/capture}
		{load_url_in_div id=$authorsGridContainer url=$authorGridUrl}

		<!--  Chapters -->
		{assign var="chaptersGridContainer" value="chaptersGridContainer-"|uniqid|escape}
		{capture assign=chaptersGridUrl}{url router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submissionId submissionVersion=$submissionVersion escape=false}{/capture}
		{load_url_in_div id=$chaptersGridContainer url=$chaptersGridUrl}
	{/if}

	{include file="submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.readOnly}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{/if}
</form>
