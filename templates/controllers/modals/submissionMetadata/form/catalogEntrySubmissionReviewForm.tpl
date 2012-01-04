{**
 * controllers/modals/submissionMetadata/form/catalogEntryFormForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submissionMetadataViewForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="submissionMetadataViewForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submissionMetadataViewFormNotification"}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="displayedInTab" value="{$formParams.displayedInTab|escape}" />
	<input type="hidden" name="tab" value="submission" />
	
	{include file="submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	<!--  Contributors -->
	{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
	{load_url_in_div id="authorsGridContainer" url="$authorGridUrl"}

	{fbvFormSection list="true"}
		{fbvElement type="checkbox" id="confirm" checked=$confirm label="submission.catalogEntry.confirm" value="confirm"}
	{/fbvFormSection}

	{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
</form>
