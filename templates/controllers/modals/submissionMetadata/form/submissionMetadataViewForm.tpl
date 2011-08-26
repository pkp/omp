{**
 * controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's metadata form.
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

	{include file="submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	{if !$formParams.anonymous}
		<!--  Contributors -->
		{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" monographId=$monographId}
		{load_url_in_div id="authorsGridContainer" url="$authorGridUrl"}
	{/if}

	{if !$formParams.readOnly}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{/if}
</form>