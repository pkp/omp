{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 2 of author monograph submission.
 *
 * $Id$
 *}
{assign var="pageTitle" value="author.submit.step2"}
{include file="author/submit/submitStepHeader.tpl"}


<div class="separator"></div>

<form method="post" action="{url op="saveSubmit" path=$submitStep}" enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}

<!-- Submission upload grid -->

<h3>-----------------------------------<br />
Submission upload grid goes here <br />
----------------------------------<br /></h3>


{if $pressSettings.supportPhone}
	{assign var="howToKeyName" value="author.submit.howToSubmit"}
{else}
	{assign var="howToKeyName" value="author.submit.howToSubmitNoPhone"}
{/if}

<p>{translate key=$howToKeyName supportName=$pressSettings.supportName supportEmail=$pressSettings.supportEmail supportPhone=$pressSettings.supportPhone}</p>

<div class="separator"></div>


<p><input type="submit"{if !$submissionFile} onclick="return confirm('{translate|escape:"jsparam" key="author.submit.noSubmissionConfirm"}')"{/if} value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

</form>

{include file="common/footer.tpl"}
