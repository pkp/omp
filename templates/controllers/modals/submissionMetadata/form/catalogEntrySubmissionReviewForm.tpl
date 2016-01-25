{**
 * controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}
{* generate a unique ID for the form *}
{assign var="submissionMetadataViewFormId" value="submissionMetadataViewForm-"|uniqid|escape}
{if $formParams.expeditedSubmission}
	{assign var="formHandlerClass" value="'$.pkp.controllers.modals.expeditedSubmission.form.ExpeditedSubmissionMetadataFormHandler'"}
{else}
	{assign var="formHandlerClass" value="'$.pkp.controllers.form.AjaxFormHandler'"}
{/if}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$submissionMetadataViewFormId}').pkpHandler(
			{$formHandlerClass},
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>
<form class="pkp_form" id="{$submissionMetadataViewFormId}" method="post"
	{if $formParams.expeditedSubmission}
		action="{url router=$smarty.const.ROUTE_PAGE op="expedite"}"
	{else}
		action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}"
	{/if}
>
	{assign var="notificationId" value="submissionMetadataViewFormNotification-"|uniqid|escape}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$notificationId}

	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="submission" />

	{if $formParams.expeditedSubmission}
		{* pull in the approved proof form fields so the Editor has a chance to set a price and the access status *}
		{fbvFormArea id="approvedProofInfo"}
		{include file="controllers/grid/files/proof/form/approvedProofFormFields.tpl"}
		{/fbvFormArea}
	{/if}

	{include file="submission/form/seriesAndCategories.tpl" readOnly=$formParams.readOnly includeSeriesPosition=true}

	<p class="pkp_help">{translate key="common.catalogInformation"}</p>

	{include file="core:submission/submissionMetadataFormTitleFields.tpl" readOnly=$formParams.readOnly}

	<!--  Contributors -->

	{if !$formParams.hideSubmit || !$formParams.anonymous}
		{* generate a unique ID for the form *}
		{assign var="authorsGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" submissionId=$submissionId stageId=$stageId escape=false}
		{load_url_in_div id=$authorsGridContainer url=$authorGridUrl}

		<!--  Chapters -->
		{assign var="chaptersGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}
		{load_url_in_div id=$chaptersGridContainer url=$chaptersGridUrl}
	{/if}

	{include file="core:submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.hideSubmit}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{else}
		{fbvElement type="button" class="cancelFormButton" id="cancelFormButton" label="common.close"}
	{/if}

</form>
