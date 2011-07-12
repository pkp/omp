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
	{fbvFormSection label="reviewer.step1.request"}
		<p>{$reviewerRequest|nl2br}</p>
	{/fbvFormSection}
	{fbvFormSection label="submission.overview"}
		<h4>{translate key="monograph.title"}</h4>
		<p>{$submission->getLocalizedTitle()|strip_unsafe_html}</p>

		{if !$blindReview}
			<h4>{translate key="monograph.title"}</h4>
			<p>{$submission->getAuthorString()}</p>
		{/if}

		<h4>{translate key="monograph.description"}</h4>
		<p>{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p>

		<div class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$viewMetadataAction contextId="reviewStep1Form"}
		</div>
	{/fbvFormSection}
	{fbvFormSection label="reviewer.monograph.reviewSchedule"}</h3>
	<table width="100%" class="data" style="margin-left: 10px;">
		<tr><td>
			<h4>{translate key="reviewer.monograph.schedule.request"}</h4><br />
			<p>{if $submission->getDateNotified()}{$submission->getDateNotified()|date_format:$dateFormatShort}{else}&mdash;{/if}</p>
		</td>
		<td>
			<h4>{translate key="reviewer.monograph.schedule.due"}</h4><br />
			<p>{if $submission->getDateDue()}{$submission->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</p>
		</td></tr>
	</table>
	{/fbvFormSection}
	{fbvFormSection label="reviewer.competingInterests" description="reviewer.monograph.enterCompetingInterests"}
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
		{fbvElement type="radio" value="hasCompetingInterests" id="hasCompetingInterests" name="competingInterestOption" checked=$hasCI label="reviewer.monograph.hasCompetingInterests"}
		{fbvElement type="textarea" name="competingInterestsText" id="competingInterestsText" value=$competingInterestsText size=$fbvStyles.size.SMALL}
	{/fbvFormSection}

	{if $reviewAssignment->getDateConfirmed()}
		{fbvFormButtons hideCancel=true submitText="common.saveAndContinue"}
	{else}
		{fbvFormButtons submitText="reviewer.monograph.acceptReview" cancelText="reviewer.monograph.declineReview" cancelAction=$declineReviewAction}
	{/if}
{/fbvFormArea}
</form>

{include file="common/footer.tpl"}


