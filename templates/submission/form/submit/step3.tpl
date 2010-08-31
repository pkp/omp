<!-- templates/submission/form/submit/step3.tpl -->

{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of author monograph submission.
 *}

{assign var="pageTitle" value="submission.submit.step3"}
{include file="submission/form/submit/submitStepHeader.tpl"}

{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}

<div class="separator"></div>

<form name="submit" method="post" action="{url op="saveStep" path=$submitStep}">
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
			{fbvElement type="textarea" name="abstract[$formLocale]" id="abstract" value=$abstract[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
		{fbvFormSection title="submission.submit.metadata"}
			{fbvKeywordInput id="disciplines" label="search.discipline"} <br />
			{fbvKeywordInput id="keyword" label="common.keywords"} <br />
			{fbvKeywordInput id="agencies" label="submission.supportingAgencies"}
		{/fbvFormSection}
	{/fbvFormArea}
</div>
<!--  Contributors -->
{url|assign:submissionContributorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.submissionContributor.SubmissionContributorGridHandler" op="fetchGrid" monographId=$monographId}
{load_url_in_div id="submissionContributorGridContainer" url="$submissionContributorGridUrl"}

<!--  Chapters -->
{if $isEditedVolume}
	{url|assign:chapterGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" monographId=$monographId}
	{load_url_in_div id="chaptersGridContainer" url="$chapterGridUrl"}
{/if}

<p><input type="submit" value="{translate key="submission.submit.finishSubmission"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="submission.submit.cancelSubmission"}')" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>
{include file="common/footer.tpl"}

<!-- / templates/submission/form/submit/step3.tpl -->

