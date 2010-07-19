{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of author monograph submission.
 *
 * $Id$
 *} 
 
{assign var="pageTitle" value="author.submit.step3"}
{include file="author/submit/submitStepHeader.tpl"}

{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}

<div class="separator"></div>

<form name="submit" method="post" action="{url op="saveSubmit" path=$submitStep}">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}


<!--  General Information -->
<div id="bookMetadataContainer" style="width: 97%;">
	<h3>{translate key="author.submit.generalInformation"}</h3>
	{fbvFormArea id="generalInformation"}
		{fbvFormSection title="monograph.title" for="title" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="text" name="title[$formLocale]" id="title" value=$title[$formLocale] maxlength="255" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection title="author.submit.briefSummary" for="abstract" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="textarea" name="abstract[$formLocale]" id="abstract" value=$abstract[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
		{fbvFormSection title="author.submit.metadata" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvKeywordInput id="disciplines" label="search.discipline"} <br />
			{fbvKeywordInput id="keyword" label="common.keywords"} <br />
			{fbvKeywordInput id="agencies" label="submission.supportingAgencies"}
		{/fbvFormSection}
	{/fbvFormArea}
</div>
<!--  Contributors -->
{url|assign:submissionContributorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.submissionContributor.SubmissionContributorGridHandler" op="fetchGrid" monographId=$monographId}
{load_url_in_div id="#submissionContributorGridContainer" url="$submissionContributorGridUrl"}

<!--  Contributors -->
{url|assign:chapterGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" monographId=$monographId}
{load_url_in_div id="#chaptersGridContainer" url="$chapterGridUrl"}

<p><input type="submit" value="{translate key="author.submit.finishSubmission"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>
{include file="common/footer.tpl"}
