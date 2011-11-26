{**
 * templates/submission/form/step3.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of author monograph submission.
 *}

{assign var="pageTitle" value="submission.submit"}
{include file="submission/form/submitStepHeader.tpl"}


<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submitStep3Form').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="submitStep3Form" method="post" action="{url op="saveStep" path=$submitStep}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submitStep3FormNotification"}

	{include file="submission/submissionMetadataFormFields.tpl"}

	{fbvFormArea id="contributors"}
		{fbvFormSection}
			<!--  Contributors -->
			{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" monographId=$monographId}
			{load_url_in_div id="authorsGridContainer" class="update_source_author" url="$authorGridUrl"}

			<!--  Chapters -->
			{if $isEditedVolume}
				{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" monographId=$monographId}
				{load_url_in_div id="chaptersGridContainer" class="update_target_author" url="$chaptersGridUrl"}
			{/if}
		{/fbvFormSection}

		{fbvFormButtons id="step2Buttons" submitText="submission.submit.finishSubmission" confirmSubmit="submission.confirmSubmit"}
	{/fbvFormArea}
</form>
{include file="common/footer.tpl"}

