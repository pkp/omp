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

<h3>{translate key="author.submit.generalInformation"}</h3>
{fbvFormArea id="generalInformation" layout=$fbvStyles.layout.ONE_COLUMN}
{fbvFormSection title="monograph.title" for="title"}
	{fbvElement type="text" name="title[$formLocale]" id="title" value=$title[$formLocale] maxlength="255" size=$fbvStyles.size.LARGE}
{/fbvFormSection}
{fbvFormSection title="monograph.abstract" for="abstract"}
	{fbvElement type="textarea" name="abstract[$formLocale]" id="abstract" value=$abstract[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<!--  Contributors -->

{url|assign:submissionContributorGridUrl router=$smarty.const.ROUTE_COMPONENT co
mponent="grid.submissionContributor.SubmissionContributorGridHandler" op="fetchG
rid" monographId=27}
{load_div id="submissionContributorGridContainer" loadMessageId="submission.subm
issionContributors.form.loadMessage" url="$submissionContributorGridUrl"}

<div class="separator"></div>

<!--  Indexing Information -->

<h3>-----------------------------------<br />
Indexing information listbuilder goes here <br />
----------------------------------<br /></h3>

<div class="separator"></div>

<p><input type="submit" value="{translate key="author.submit.finishSubmission"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>
{include file="common/footer.tpl"}
