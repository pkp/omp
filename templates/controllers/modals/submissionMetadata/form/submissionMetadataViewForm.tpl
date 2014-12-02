{**
 * controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
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
	{assign var="notificationId" value="submissionMetadataViewFormNotification-"|uniqid|escape}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$notificationId}

	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	{include file="submission/form/seriesAndCategories.tpl" readOnly=$formParams.readOnly includeSeriesPosition=true}

	<p class="pkp_help">{translate key="common.catalogInformation"}</p>

	{include file="core:submission/submissionMetadataFormTitleFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.anonymous}
		<!--  Contributors -->
		{* generate a unique ID for the form *}
		{assign var="authorsGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.author.AuthorGridHandler" op="fetchGrid" submissionId=$submissionId stageId=$stageId escape=false}
		{load_url_in_div id=$authorsGridContainer url=$authorGridUrl}

		<!--  Chapters -->
		{assign var="chaptersGridContainer" value="chaptersGridContainer-"|uniqid|escape}
		{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}
		{load_url_in_div id=$chaptersGridContainer url=$chaptersGridUrl}
	{/if}

	{include file="core:submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.readOnly}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{/if}
</form>
