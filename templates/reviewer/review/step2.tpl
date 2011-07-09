{**
 * templates/reviewer/review/step2.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the step 2 review page
 *
 *}

{strip}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#review').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="review" method="post" action="{url page="reviewer" op="saveStep" path=$submission->getId() step="2" escape=false}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="reviewStep2"}
	{fbvFormSection label="reviewer.monograph.reviewerGuidelines"}
		<p>{$reviewerGuidelines}</p>
	{/fbvFormSection}

	{fbvFormButtons submitText="reviewer.monograph.continueToStepThree" cancelText="navigation.goBack"}
{/fbvFormArea}
</form>
{include file="common/footer.tpl"}

