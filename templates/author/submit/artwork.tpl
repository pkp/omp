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
{assign var="pageTitle" value="author.submit.step.Artwork"}
{include file="author/submit/submitStepHeader.tpl"}

{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}

<div class="separator"></div>

<form name="submit" method="post" action="{url op="saveSubmit" path=$submitStep}" enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}

{include file="inserts/artwork/ArtworkInsert.tpl"}

<input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" />

</form>

{include file="common/footer.tpl"}
