{**
 * templates/reviewer/review/step1.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the review step 1 page
 *
 *}

{strip}
{assign var="pageCrumbTitle" value="submission.request"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#reviewStep1Form').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="reviewStep1Form" method="post" action="{url page="reviewer" op="saveStep" path=$submission->getId() step="1" escape=false}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="reviewStep1"}
	{fbvFormSection title="reviewer.step1.request"}
		<p>{$reviewerRequest|nl2br}</p>
	{/fbvFormSection}
	{fbvFormSection label="submission.overview"}
		{fbvElement type="text" id="title" label="monograph.title" value=$submission->getLocalizedTitle() disabled=true}
		{fbvElement type="textarea" id="summary" label="monograph.description" value=$submission->getLocalizedAbstract() disabled=true}
	{/fbvFormSection}
	<div class="pkp_linkActions">
		{include file="linkAction/linkAction.tpl" action=$viewMetadataAction contextId="reviewStep1Form"}
	</div>
	<br />
	{fbvFormSection title="reviewer.monograph.reviewSchedule"}
		{fbvElement type="text" id="dateNotified" label="reviewer.monograph.reviewRequestDate" value=$submission->getDateNotified()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL}
		{fbvElement type="text" id="responseDue" label="reviewer.monograph.responseDueDate" value=$submission->getDateResponseDue()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL}
		{fbvElement type="text" id="dateDue" label="reviewer.monograph.reviewDueDate" value=$submission->getDateDue()|date_format:$dateFormatShort disabled=true inline=true size=$fbvStyles.size.SMALL}
		<br /><br />
		<div class="pkp_linkActions pkp_helper_align_right">
			{include file="linkAction/linkAction.tpl" action=$aboutDueDatesAction contextId="reviewStep1"}
		</div>
	{/fbvFormSection}
	<br /><br />
	{fbvFormSection label="reviewer.monograph.competingInterests" description="reviewer.monograph.enterCompetingInterests"}
		<div class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$competingInterestsAction contextId="reviewStep1"}
		</div>
	{/fbvFormSection}
	{fbvFormSection list=true}
		{if $competingInterestsText != null}
			{assign var="hasCI" value=true}
			{assign var="noCI" value=false}
		{else}
			{assign var="hasCI" value=false}
			{assign var="noCI" value=true}
		{/if}
		{fbvElement type="radio" value="noCompetingInterests" id="noCompetingInterests" name="competingInterestOption" checked=$noCI label="reviewer.monograph.noCompetingInterests"}
		<br /><br />
		{fbvElement type="radio" value="hasCompetingInterests" id="hasCompetingInterests" name="competingInterestOption" checked=$hasCI label="reviewer.monograph.hasCompetingInterests"}
	{/fbvFormSection}
	{fbvFormSection}
		{fbvElement type="textarea" name="competingInterestsText" id="competingInterestsText" value=$competingInterestsText size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{if $reviewAssignment->getDateConfirmed()}
		{fbvFormButtons hideCancel=true submitText="common.saveAndContinue"}
	{else}
		{fbvFormButtons submitText="reviewer.monograph.acceptReview" cancelText="reviewer.monograph.declineReview" cancelAction=$declineReviewAction}
	{/if}
{/fbvFormArea}
</form>

{include file="common/footer.tpl"}


