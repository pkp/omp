{**
 * submission.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the reviewer administration page.
 *
 * FIXME: At "Notify The Editor", fix the date.
 *
 * $Id$
 *}
{strip}
{assign var="monographId" value=$submission->getMonographId()}
{assign var="reviewId" value=$reviewAssignment->getReviewId()}
{translate|assign:"pageTitleTranslated" key="submission.page.review" id=$monographId}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
{literal}
<!--
function confirmSubmissionCheck() {
	if (document.recommendation.recommendation.value=='') {
		alert('{/literal}{translate|escape:"javascript" key="reviewer.monograph.mustSelectDecision"}{literal}');
		return false;
	}
	return confirm('{/literal}{translate|escape:"javascript" key="reviewer.monograph.confirmDecision"}{literal}');
}
// -->
{/literal}
</script>

<h3>{translate key="reviewer.monograph.submissionToBeReviewed"}</h3>

<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{translate key="monograph.title"}</td>
	<td width="80%" class="value">{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="submissions.acquisitionsArrangement"}</td>
	<td class="value">{$submission->getAcquisitionsArrangementTitle()|escape}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="monograph.abstract"}</td>
	<td class="value">{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br}</td>
</tr>
{assign var=editAssignments value=$submission->getEditAssignments()}
{foreach from=$editAssignments item=editAssignment}
	{if !$notFirstEditAssignment}
		{assign var=notFirstEditAssignment value=1}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.submissionEditor"}</td>
			<td class="value">
	{/if}
			{assign var=emailString value="`$editAssignment->getEditorFullName()` <`$editAssignment->getEditorEmail()`>"}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl subject=$submission->getLocalizedTitle()|strip_tags monographId=$monographId}
			{$editAssignment->getEditorFullName()|escape} {icon name="mail" url=$url}
			{if !$editAssignment->getCanEdit() || !$editAssignment->getCanReview()}
				{if $editAssignment->getCanEdit()}
					({translate key="submission.editing"})
				{else}
					({translate key="submission.review"})
				{/if}
			{/if}
			<br/>
{/foreach}
{if $notFirstEditAssignment}
		</td>
	</tr>
{/if}
	<tr valign="top">
	       <td class="label">{translate key="submission.metadata"}</td>
	       <td class="value">
		       <a href="{url op="viewMetadata" path=$reviewId|to_array:$monographId}" class="action" target="_new">{translate key="submission.viewMetadata"}</a>
	       </td>
	</tr>
</table>

<div class="separator"></div>

<h3>{translate key="reviewer.monograph.reviewSchedule"}</h3>
<table width="100%" class="data">
<tr valign="top">
	<td class="label" width="20%">{translate key="reviewer.monograph.schedule.request"}</td>
	<td class="value" width="80%">{if $submission->getDateNotified()}{$submission->getDateNotified()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.monograph.schedule.response"}</td>
	<td class="value">{if $submission->getDateConfirmed()}{$submission->getDateConfirmed()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.monograph.schedule.submitted"}</td>
	<td class="value">{if $submission->getDateCompleted()}{$submission->getDateCompleted()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.monograph.schedule.due"}</td>
	<td class="value">{if $submission->getDateDue()}{$submission->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
</table>

<div class="separator"></div>

<h3>{translate key="reviewer.monograph.reviewSteps"}</h3>

{include file="common/formErrors.tpl"}

{assign var="currentStep" value=1}

<table width="100%" class="data">
<tr valign="top">
	{assign var=editAssignments value=$submission->getByIds}
	{* FIXME: Should be able to assign primary editorial contact *}
	{if $editAssignments[0]}{assign var=firstEditAssignment value=$editAssignments[0]}{/if}
	<td width="3%">{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td width="97%"><span class="instruct">{translate key="reviewer.monograph.notifyEditorA"}{if $firstEditAssignment}, {$firstEditAssignment->getEditorFullName()},{/if} {translate key="reviewer.monograph.notifyEditorB"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		{translate key="submission.response"}&nbsp;&nbsp;&nbsp;&nbsp;
		{if not $confirmedStatus}
			{url|assign:"acceptUrl" op="confirmReview" reviewId=$reviewId}
			{url|assign:"declineUrl" op="confirmReview" reviewId=$reviewId declineReview=1}

			{if !$submission->getCancelled()}
				{translate key="reviewer.monograph.canDoReview"} {icon name="mail" url=$acceptUrl}
				&nbsp;&nbsp;&nbsp;&nbsp;
				{translate key="reviewer.monograph.cannotDoReview"} {icon name="mail" url=$declineUrl}
			{else}
				{url|assign:"url" op="confirmReview" reviewId=$reviewId}
				{translate key="reviewer.monograph.canDoReview"} {icon name="mail" disabled="disabled" url=$acceptUrl}
				&nbsp;&nbsp;&nbsp;&nbsp;
				{url|assign:"url" op="confirmReview" reviewId=$reviewId declineReview=1}
				{translate key="reviewer.monograph.cannotDoReview"} {icon name="mail" disabled="disabled" url=$declineUrl}
			{/if}
		{else}
			{if not $declined}{translate key="submission.accepted"}{else}{translate key="submission.rejected"}{/if}
		{/if}
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
{if $press->getLocalizedSetting('reviewGuidelines') != ''}
<tr valign="top">
        <td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.monograph.consultGuidelines"}</span></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
{/if}
<tr valign="top">
	<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.monograph.downloadSubmission"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table width="100%" class="data">
			{if ($confirmedStatus and not $declined) or not $press->getSetting('restrictReviewerFileAccess')}
			<tr valign="top">
				<td width="30%" class="label">
					{translate key="submission.submissionManuscript"}
				</td>
				<td class="value" width="70%">
					{if $reviewFile}
					{if $submission->getDateConfirmed() or not $press->getSetting('restrictReviewerAccessToFile')}
						<a href="{url op="downloadFile" path=$reviewId|to_array:$monographId:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file">{$reviewFile->getFileName()|escape}</a>
					{else}{$reviewFile->getFileName()|escape}{/if}
					&nbsp;&nbsp;{$reviewFile->getDateModified()|date_format:$dateFormatShort}
					{else}
					{translate key="common.none"}
					{/if}
				</td>
			</tr>
			<tr valign="top">
				<td class="label">
					{translate key="monograph.suppFiles"}
				</td>
				<td class="value">
					{assign var=sawSuppFile value=0}
					{foreach from=$suppFiles item=suppFile}
						{if $suppFile->getShowReviewers() }
							{assign var=sawSuppFile value=1}
							<a href="{url op="downloadFile" path=$reviewId|to_array:$monographId:$suppFile->getFileId()}" class="file">{$suppFile->getFileName()|escape}</a><br />
						{/if}
					{/foreach}

					{if !$sawSuppFile}
						{translate key="common.none"}
					{/if}
				</td>
			</tr>
			{else}
			<tr><td class="nodata">{translate key="reviewer.monograph.restrictedFileAccess"}</td></tr>
			{/if}
		</table>
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
{if $currentPress->getSetting('requireReviewerCompetingInterests')}
	<tr valign="top">
		<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
		<td>
			{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}
			<span class="instruct">{translate key="reviewer.monograph.enterCompetingInterests" competingInterestGuidelinesUrl=$competingInterestGuidelinesUrl}</span>
			{if not $confirmedStatus or $declined or $submission->getCancelled() or $submission->getRecommendation()}<br/>
				{$reviewAssignment->getCompetingInterests()|strip_unsafe_html|nl2br}
			{else}
				<form action="{url op="saveCompetingInterests" reviewId=$reviewId}" method="post">
					<textarea {if $cannotChangeCI}disabled="disabled" {/if}name="competingInterests" class="textArea" id="competingInterests" rows="5" cols="40">{$reviewAssignment->getCompetingInterests()|escape}</textarea><br />
					<input {if $cannotChangeCI}disabled="disabled" {/if}class="button defaultButton" type="submit" value="{translate key="common.save"}" />
				</form>
			{/if}
		</td>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
{/if}{* $currentPress->getSetting('requireReviewerCompetingInterests') *}

{if $reviewAssignment->getReviewFormId()}
	<tr valign="top">
		<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
		<td><span class="instruct">{translate key="reviewer.monograph.enterReviewForm"}</span></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			{translate key="submission.reviewForm"} 
			{if $confirmedStatus and not $declined}
				<a href="{url op="editReviewFormResponse" path=$reviewId|to_array:$reviewAssignment->getReviewFormId()}" class="icon">{icon name="comment"}</a>
			{else}
				 {icon name="comment" disabled="disabled"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
{else}{* $reviewAssignment->getReviewFormId() *}
	<tr valign="top">
		<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
		<td><span class="instruct">{translate key="reviewer.monograph.enterReviewA"}</span></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			{translate key="submission.logType.review"} 
			{if $confirmedStatus and not $declined}
				<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$monographId|to_array:$reviewId}');" class="icon">{icon name="comment"}</a>
			{else}
				 {icon name="comment" disabled="disabled"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
{/if}{* $reviewAssignment->getReviewFormId() *}
<tr valign="top">
	<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.monograph.uploadFile"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table class="data" width="100%">
			{assign var=reviewerFiles value=$submission->getReviewerFileRevisions()}
			{assign var=reviewerFiles value=$reviewerFiles.$reviewType.$round}
			{foreach from=$reviewerFiles item=reviewerFile key=key}
				{assign var=uploadedFileExists value="1"}
				<tr valign="top">
				<td class="label" width="30%">
					{if $key eq "0"}
						{translate key="reviewer.monograph.uploadedFile"}
					{/if}
				</td>
				<td class="value" width="70%">
					<a href="{url op="downloadFile" path=$reviewId|to_array:$monographId:$reviewerFile->getFileId():$reviewerFile->getRevision()}" class="file">{$reviewerFile->getFileName()|escape}</a>
					{$reviewerFile->getDateModified()|date_format:$dateFormatShort}
					{if ($submission->getRecommendation() === null || $submission->getRecommendation() === '') && (!$submission->getCancelled())}
						<a class="action" href="{url op="deleteReviewerVersion" path=$reviewId|to_array:$reviewerFile->getFileId():$reviewerFile->getRevision()}">{translate key="common.delete"}</a>
					{/if}
				</td>
				</tr>
			{foreachelse}
				<tr valign="top">
				<td class="label" width="30%">
					{translate key="reviewer.monograph.uploadedFile"}
				</td>
				<td class="nodata">
					{translate key="common.none"}
				</td>
				</tr>
			{/foreach}
		</table>
		{if $submission->getRecommendation() === null || $submission->getRecommendation() === ''}
			<form method="post" action="{url op="uploadReviewerVersion"}" enctype="multipart/form-data">
				<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
				<input type="file" name="upload" {if not $confirmedStatus or $declined or $submission->getCancelled()}disabled="disabled"{/if} class="uploadField" />
				<input type="submit" name="submit" value="{translate key="common.upload"}" {if not $confirmedStatus or $declined or $submission->getCancelled()}disabled="disabled"{/if} class="button" />
			</form>

			{if $currentPress->getSetting('showEnsuringLink')}
			<span class="instruct">
				<a class="action" href="javascript:openHelp('{get_help_id key="editorial.acquisitionsEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.monograph.ensuringBlindReview"}</a>
			</span>
			{/if}
		{/if}
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr valign="top">
	<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.monograph.selectRecommendation"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table class="data" width="100%">
			<tr valign="top">
				<td class="label" width="30%">{translate key="submission.recommendation"}</td>
				<td class="value" width="70%">
				{if $submission->getRecommendation() !== null && $submission->getRecommendation() !== ''}
					{assign var="recommendation" value=$submission->getRecommendation()}
					<strong>{translate key=$reviewerRecommendationOptions.$recommendation}</strong>&nbsp;&nbsp;
					{$submission->getDateCompleted()|date_format:$dateFormatShort}
				{else}
					<form name="recommendation" method="post" action="{url op="recordRecommendation"}">
					<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
					<select name="recommendation" {if not $confirmedStatus or $declined or $submission->getCancelled() or (!$reviewFormResponseExists and !$reviewAssignment->getMostRecentPeerReviewComment() and !$uploadedFileExists)}disabled="disabled"{/if} class="selectMenu">
						{html_options_translate options=$reviewerRecommendationOptions selected=''}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="submit" onclick="return confirmSubmissionCheck()" class="button" value="{translate key="reviewer.monograph.submitReview"}" {if not $confirmedStatus or $declined or $submission->getCancelled() or (!$reviewFormResponseExists and !$reviewAssignment->getMostRecentPeerReviewComment() and !$uploadedFileExists)}disabled="disabled"{/if} />
					</form>					
				{/if}
				</td>		
			</tr>
		</table>
	</td>
</tr>
</table>

{if $press->getLocalizedSetting('reviewGuidelines') != ''}
<div class="separator"></div>
<h3>{translate key="reviewer.monograph.reviewerGuidelines"}</h3>
<p>{$press->getLocalizedSetting('reviewGuidelines')|nl2br}</p>
{/if}

{include file="common/footer.tpl"}

