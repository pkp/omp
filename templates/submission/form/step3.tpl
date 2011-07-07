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
	// Attach the JS form handler.
	$(function() {ldelim}
		$('#submitStep3Form').pkpHandler(
				'$.pkp.pages.submission.SubmissionStep3FormHandler',
				{ldelim}
					isEditedVolume: {if $isEditedVolume}true{else}false{/if},
					chaptersGridContainer: 'chaptersGridContainer',
					authorsGridContainer: 'authorsGridContainer'
				{rdelim});
	{rdelim});
</script>

<div class="separator"></div>

<form class="pkp_form" id="submitStep3Form" method="post" action="{url op="saveStep" path=$submitStep}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	{include file="common/formErrors.tpl"}


	<!--  General Information -->
	<div id="bookMetadataContainer" style="width: 97%;">
		<h3>{translate key="submission.submit.generalInformation"}</h3>
		{fbvFormArea id="generalInformation"}
			{fbvFormSection title="monograph.title" for="title"}
				{fbvElement type="text" name="title[$formLocale]" id="title" value=$title[$formLocale] maxlength="255"}
			{/fbvFormSection}
			{fbvFormSection title="submission.submit.briefSummary" for="abstract"}
				{fbvElement type="textarea" name="abstract[$formLocale]" id="abstract" value=$abstract[$formLocale] size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
			{fbvFormSection title="submission.submit.metadata"}
				{fbvElement type="keyword" id="disciplines" label="search.discipline"} <br />
				{fbvElement type="keyword" id="keyword" label="common.keywords"} <br />
				{fbvElement type="keyword" id="agencies" label="submission.supportingAgencies"}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<!--  Contributors -->
	{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" monographId=$monographId}
	{load_url_in_div id="authorsGridContainer" url="$authorGridUrl"}

	<!--  Chapters -->
	{if $isEditedVolume}
		{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" monographId=$monographId}
		{load_url_in_div id="chaptersGridContainer" url="$chaptersGridUrl"}
	{/if}

	{fbvFormButtons id="step2Buttons" submitText="submission.submit.finishSubmission" confirmSubmit="submission.confirmSubmit"}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
</div>
{include file="common/footer.tpl"}

