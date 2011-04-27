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

<form id="review" method="post" action="{url page="reviewer" op="saveStep" path=$submission->getId() step="2" escape=false}">
{include file="common/formErrors.tpl"}

<div id=guidelines>
<h3>{translate key="reviewer.monograph.reviewerGuidelines"}</h3>
<p>{$reviewerGuidelines}</p>
</div>

<div id="nextSteps">
	<p>
		<a href="{url op="submission" path=$submission->getReviewId() step=1}">{translate key="navigation.goBack"}</a>
		<input style="float:right;" type="submit" id="submit" value="{translate key='reviewer.monograph.continueToStepThree'}" class="button" />
	</p>
</div>
<br />
</form>
</div>
{include file="common/footer.tpl"}

