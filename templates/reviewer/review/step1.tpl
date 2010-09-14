<!-- templates/reviewer/review/step1.tpl -->

{**
 * step1.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the review step 1 page
 *
 *
 * $Id$
 *}

{strip}
{assign var="pageCrumbTitle" value="submission.request"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<form name="review" method="post" action="{url page="reviewer" op="saveStep" path="1" reviewId=$submission->getReviewId()}">
{include file="common/formErrors.tpl"}

<div id="request">
	<h3>{translate key="reviewer.step1.request"}</h3>
	<br />

	<p>{$reviewerRequest|nl2br}</p>
</div>

<div class="separator"></div>

<div id="submissionOverview">
	<h3>{translate key="submission.overview"}</h3>
	<h4>{translate key="monograph.title"}</h4>
	<p>{$submission->getLocalizedTitle()|strip_unsafe_html}</p>

	{if !$blindReview}
		<h4>{translate key="monograph.title"}</h4>
		<p>{$submission->getAuthorString()}</p>
	{/if}

	<h4>{translate key="monograph.description"}</h4>
	<p>{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p>

	<p>
		{url|assign:"metadataUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.ReviewerSubmissionMetadataHandler" op="fetch" monographId=$submission->getId()}
		{modal url="$metadataUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.step1.viewAllDetails' button="#viewMetadata"}
		<a id="viewMetadata" href="{$metadataUrl}">{translate key="reviewer.step1.viewAllDetails"}</a>
	</p>
</div>
<br />
<div class="separator"></div>

<div id="reviewSchedule">
	<h3>{translate key="reviewer.monograph.reviewSchedule"}</h3>
	<br />
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
</div>
<br />
<div class="separator"></div>

<div id="competingInterestsContainer" style="margin-right:20px;">
{*if $currentPress->getSetting('requireReviewerCompetingInterests')*}
{if true}
	<h3>{translate key="reviewer.competingInterests"}</h3>

	<p>
		{translate key="reviewer.monograph.enterCompetingInterests"}<br /><br />

		{url|assign:"competingInterestGuidelinesUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.competingInterests.CompetingInterestsHandler" op="fetch" monographId=$submission->getId() pressId=$submission->getPressId() escape=false}
		{modal url="$competingInterestGuidelinesUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.competingInterests' button="#viewCompetingInterests"}
		<a id="viewCompetingInterests" href="{$competingInterestGuidelinesUrl}">{translate key="reviewer.monograph.viewCompetingInterests"}</a><br />

		{fbvFormArea id="competingInterestForm"}
			{fbvFormSection}
				{if $competingInterestsText != null}
					{assign var="hasCI" value=true}
					{assign var="noCI" value=false}
				{else}
					{assign var="hasCI" value=false}
					{assign var="noCI" value=true}
				{/if}
				{fbvElement type="radio" value="noCompetingInterests" id="noCompetingInterests" name="competingInterestOption" checked=$noCI label="reviewer.monograph.noCompetingInterests"}
				{fbvElement type="radio" value="hasCompetingInterests" id="hasCompetingInterests" name="competingInterestOption" checked=$hasCI label="reviewer.monograph.hasCompetingInterests"}
				{fbvElement type="textarea" name="competingInterestsText" id="competingInterestsText" value=$competingInterestsText size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.2OF3}
			{/fbvFormSection}
		{/fbvFormArea}
	</p>
{/if}
</div>
<br />

{if !$reviewAssignment->getDateConfirmed()}
	<div id="nextSteps">
		<p>
			{url|assign:"declineRequestUrl" op='showDeclineReview' reviewId=$submission->getReviewId()}
			{modal url="$declineRequestUrl" actOnType="nothing" actOnId="nothing" dialogText='reviewer.monograph.declineReview' button="#declineRequest"}

			<a id="declineRequest" href="{$declineRequestUrl}">{translate key="reviewer.monograph.declineReview"}</a>
			<input style="float:right;" type="submit" id="submit" value="{translate key='reviewer.monograph.acceptReview'}" class="button" />
		</p>
	</div>
{/if}
<br />
</form>
</div>
{include file="common/footer.tpl"}


<!-- / templates/reviewer/review/step1.tpl -->

