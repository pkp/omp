<!-- templates/submission/form/submit/step2.tpl -->

{**
 * step2.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 2 of author monograph submission.
 *
 * $Id$
 *}
{assign var="pageTitle" value="submission.submit.step2"}
{include file="submission/form/submit/submitStepHeader.tpl"}


<div class="separator"></div>

<form method="post" action="{url op="saveStep" path=$submitStep}" enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}

<!-- Submission upload grid -->

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionWizardFilesGridHandler" op="fetchGrid" monographId=$monographId}
{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

{if $pressSettings.supportPhone}
	{assign var="howToKeyName" value="submission.submit.howToSubmit"}
{else}
	{assign var="howToKeyName" value="submission.submit.howToSubmitNoPhone"}
{/if}

<p>{translate key=$howToKeyName supportName=$pressSettings.supportName supportEmail=$pressSettings.supportEmail supportPhone=$pressSettings.supportPhone}</p>

<div class="separator"></div>


<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="submission.submit.cancelSubmission"}')" /></p>

</form>

{include file="common/footer.tpl"}

<!-- / templates/submission/form/submit/step2.tpl -->

