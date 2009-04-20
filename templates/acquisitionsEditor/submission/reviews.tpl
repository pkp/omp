{foreach from=$reviewProcesses item=reviewProcess}
<div style="border:1px solid gray">

{if $reviewProcess->getStatus() == WORKFLOW_PROCESS_STATUS_CURRENT}

<table class="data" width="100%">
	<tr valign="middle">
		<td width="22%"><h3>{$reviewProcess->getTitle()}</h3></td>
		<td width="14%"><h4>{if $reviewType == $reviewProcess->getId()}{translate key="submission.round" round=$round}{/if}</h4></td>
		<td width="64%" class="nowrap">
			<a href="{url op="selectReviewer" path=$submission->getMonographId()}" class="action">{translate key="editor.monograph.selectReviewer"}</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="{url op="submissionRegrets" path=$submission->getMonographId()}" class="action">{translate|escape key="editor.regrets.link"}</a>
		</td>
	</tr>
</table>

{assign var="start" value="A"|ord}
{foreach from=$reviewAssignments item=reviewAssignment key=reviewKey}
{assign var="reviewId" value=$reviewAssignment->getReviewId()}

{if not $reviewAssignment->getCancelled() and not $reviewAssignment->getDeclined()}
	{assign var="reviewIndex" value=$reviewIndexes[$reviewId]}
	<div class="separator"></div>

	<table class="data" width="100%">
	<tr>
		<td width="20%"><h4>{translate key="user.role.reviewer"} {$reviewIndex+$start|chr}</h4></td>
		<td width="34%"><h4>{$reviewAssignment->getReviewerFullName()|escape}</h4></td>
		<td width="46%">
				{if not $reviewAssignment->getDateNotified()}
					<a href="{url op="clearReview" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId()}" class="action">{translate key="editor.monograph.clearReview"}</a>
				{elseif $reviewAssignment->getDeclined() or not $reviewAssignment->getDateCompleted()}
					<a href="{url op="cancelReview" monographId=$submission->getMonographId() reviewId=$reviewAssignment->getReviewId()}" class="action">{translate key="editor.monograph.cancelReview"}</a>
				{/if}
		</td>
	</tr>
	</table>

	<table width="100%" class="data">
 <!--	<tr valign="top">
		<td class="label">{translate key="submission.reviewForm"}</td>
		<td>
		{if $reviewAssignment->getReviewFormId()}
			{assign var="reviewFormId" value=$reviewAssignment->getReviewFormId()}
			{$reviewFormTitles[$reviewFormId]}
		{else}
			{translate key="manager.reviewForms.noneChosen"}
		{/if}
		{if !$reviewAssignment->getDateCompleted()}
			&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="{url op="selectReviewForm" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId()}"{if $reviewFormResponses[$reviewId]} onclick="return confirm('{translate|escape:"jsparam" key="editor.monograph.confirmChangeReviewForm"}')"{/if}>{translate key="editor.monograph.selectReviewForm"}</a>{if $reviewAssignment->getReviewFormId()}&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="{url op="clearReviewForm" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId()}"{if $reviewFormResponses[$reviewId]} onclick="return confirm('{translate|escape:"jsparam" key="editor.monograph.confirmChangeReviewForm"}')"{/if}>{translate key="editor.monograph.clearReviewForm"}</a>{/if}
		{/if}
		</td>
	</tr>-->
	<tr valign="top">
		<td class="label" width="20%">&nbsp;</td>
		<td width="80%">
			<table width="100%" class="info">
				<tr>
					<td class="heading" width="25%">{translate key="submission.request"}</td>
					<td class="heading" width="25%">{translate key="submission.underway"}</td>
					<td class="heading" width="25%">{translate key="submission.due"}</td>
					<td class="heading" width="25%">{translate key="submission.acknowledge"}</td>
				</tr>
				<tr valign="top">
					<td>
						{url|assign:"reviewUrl" op="notifyReviewer" reviewId=$reviewAssignment->getReviewId() monographId=$submission->getMonographId()}
						{if $reviewAssignment->getDateNotified()}
							{$reviewAssignment->getDateNotified()|date_format:$dateFormatShort}
							{if !$reviewAssignment->getDateCompleted()}
								{icon name="mail" url=$reviewUrl}
							{/if}
						{elseif $reviewAssignment->getReviewFileId()}
							{icon name="mail" url=$reviewUrl}
						{else}
							{icon name="mail" disabled="disabled" url=$reviewUrl}
							{assign var=needsReviewFileNote value=1}
						{/if}
					</td>
					<td>
						{$reviewAssignment->getDateConfirmed()|date_format:$dateFormatShort|default:"&mdash;"}
					</td>
					<td>
						{if $reviewAssignment->getDeclined()}
							{translate key="sectionEditor.regrets"}
						{else}
							<a href="{url op="setDueDate" path=$reviewAssignment->getMonographId()|to_array:$reviewAssignment->getReviewId()}">{if $reviewAssignment->getDateDue()}{$reviewAssignment->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</a>
						{/if}
					</td>
					<td>
						{url|assign:"thankUrl" op="thankReviewer" reviewId=$reviewAssignment->getReviewId() monographId=$submission->getMonographId()}
						{if $reviewAssignment->getDateAcknowledged()}
							{$reviewAssignment->getDateAcknowledged()|date_format:$dateFormatShort}
						{elseif $reviewAssignment->getDateCompleted()}
							{icon name="mail" url=$thankUrl}
						{else}
							{icon name="mail" disabled="disabled" url=$thankUrl}
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>

	{if $reviewAssignment->getDateConfirmed() && !$reviewAssignment->getDeclined()}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.recommendation"}</td>
			<td>
				{if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
					{assign var="recommendation" value=$reviewAssignment->getRecommendation()}
					{translate key=$reviewerRecommendationOptions.$recommendation}
					&nbsp;&nbsp;{$reviewAssignment->getDateCompleted()|date_format:$dateFormatShort}
				{else}
					{translate key="common.none"}&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="{url op="remindReviewer" monographId=$submission->getMonographId() reviewId=$reviewAssignment->getReviewId()}" class="action">{translate key="reviewer.monograph.sendReminder"}</a>
					{if $reviewAssignment->getDateReminded()}
						&nbsp;&nbsp;{$reviewAssignment->getDateReminded()|date_format:$dateFormatShort}
						{if $reviewAssignment->getReminderWasAutomatic()}
							&nbsp;&nbsp;{translate key="reviewer.monograph.automatic"}
						{/if}
					{/if}
				{/if}
			</td>
		</tr>
		{if $currentJournal->getSetting('requireReviewerCompetingInterests')}
			<tr valign="top">
				<td class="label">{translate key="reviewer.competingInterests"}</td>
				<td>{$reviewAssignment->getCompetingInterests()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
			</tr>
		{/if}{* requireReviewerCompetingInterests *}
		{if $reviewFormResponses[$reviewId]}
			<tr valign="top">
				<td class="label">{translate key="submission.reviewFormResponse"}</td>
				<td>
					<a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId()}');" class="icon">{icon name="letter"}</a>
				</td>
			</tr>
		{/if}
		{if !$reviewAssignment->getReviewFormId() || $reviewAssignment->getMostRecentPeerReviewComment()}{* Only display comments link if a comment is entered or this is a non-review form review *}
			<tr valign="top">
				<td class="label">{translate key="submission.review"}</td>
				<td>
					{if $reviewAssignment->getMostRecentPeerReviewComment()}
						{assign var="comment" value=$reviewAssignment->getMostRecentPeerReviewComment()}
						<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId() anchor=$comment->getCommentId()}');" class="icon">{icon name="letter"}</a>&nbsp;&nbsp;{$comment->getDatePosted()|date_format:$dateFormatShort}
					{else}
						<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId()}');" class="icon">{icon name="letter"}</a>&nbsp;&nbsp;{translate key="submission.comments.noComments"}
					{/if}
				</td>
			</tr>
		{/if}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.uploadedFile"}</td>
			<td>
				<table width="100%" class="data">
					{foreach from=$reviewAssignment->getReviewerFileRevisions() item=reviewerFile key=key}
					<tr valign="top">
						<td valign="middle">
							<form name="authorView{$reviewAssignment->getReviewId()}" method="post" action="{url op="makeReviewerFileViewable"}">
								<a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$reviewerFile->getFileId():$reviewerFile->getRevision()}" class="file">{$reviewerFile->getFileName()|escape}</a>&nbsp;&nbsp;{$reviewerFile->getDateModified()|date_format:$dateFormatShort}
								<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}" />
								<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
								<input type="hidden" name="fileId" value="{$reviewerFile->getFileId()}" />
								<input type="hidden" name="revision" value="{$reviewerFile->getRevision()}" />
								{translate key="editor.monograph.showAuthor"} <input type="checkbox" name="viewable" value="1"{if $reviewerFile->getViewable()} checked="checked"{/if} />
								<input type="submit" value="{translate key="common.record"}" class="button" />
							</form>
						</td>
					</tr>
					{foreachelse}
					<tr valign="top">
						<td>{translate key="common.none"}</td>
					</tr>
					{/foreach}
				</table>
			</td>
		</tr>
	{/if}

	{if (($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') || !$reviewAssignment->getDateConfirmed()) && $reviewAssignment->getDateNotified() && !$reviewAssignment->getDeclined()}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.editorToEnter"}</td>
			<td>
				{if !$reviewAssignment->getDateConfirmed()}
					<a href="{url op="confirmReviewForReviewer" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId() accept=1}" class="action">{translate key="reviewer.monograph.canDoReview"}</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{url op="confirmReviewForReviewer" path=$submission->getMonographId()|to_array:$reviewAssignment->getReviewId() accept=0}" class="action">{translate key="reviewer.monograph.cannotDoReview"}</a><br />
				{/if}
				<form method="post" action="{url op="uploadReviewForReviewer"}" enctype="multipart/form-data">
					{translate key="editor.monograph.uploadReviewForReviewer"}
					<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
					<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}"/>
					<input type="file" name="upload" class="uploadField" />
					<input type="submit" name="submit" value="{translate key="common.upload"}" class="button" />
				</form>
				{if $reviewAssignment->getDateConfirmed() && !$reviewAssignment->getDeclined()}
					<a class="action" href="{url op="enterReviewerRecommendation" monographId=$submission->getMonographId() reviewId=$reviewAssignment->getReviewId()}">{translate key="editor.monograph.recommendation"}</a>
				{/if}
			</td>
		</tr>
	{/if}

	{if $reviewAssignment->getDateNotified() && !$reviewAssignment->getDeclined() && $rateReviewerOnQuality}
		<tr valign="top">
			<td class="label">{translate key="editor.monograph.rateReviewer"}</td>
			<td>
			<form method="post" action="{url op="rateReviewer"}">
				<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}" />
				<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
				<select name="quality" size="1" class="selectMenu">
					{html_options_translate options=$reviewerRatingOptions selected=$reviewAssignment->getQuality()}
				</select>&nbsp;&nbsp;
				<input type="submit" value="{translate key="common.record"}" class="button" />
				{if $reviewAssignment->getDateRated()}
					&nbsp;&nbsp;{$reviewAssignment->getDateRated()|date_format:$dateFormatShort}
				{/if}
			</form>
			</td>
		</tr>
	{/if}
	{if $needsReviewFileNote}
		<tr valign="top">
			<td>&nbsp;</td>
			<td>
				{translate key="submission.review.mustUploadFileForReview"}
			</td>
		</tr>
	{/if}
	</table>
{/if}
{/foreach}

<div class="separator"></div>


{if $reviewProcess->getDateEnded() != null && $reviewProcess->getDateSigned() == null}
	{assign var="waitingOnSignoffs" value="1"}
{else}
	{assign var="waitingOnSignoffs" value="0"}
{/if}

<table class="data" width="100%">
	<tr valign="middle">
		<td width="22%"><h3>{$reviewProcess->getTitle()} Signoff</h3></td>
		<td width="14%">{if !$waitingOnSignoffs}<a href="{url op="endWorkflowProcess" path=$submission->getMonographId()}">Sign off</a>{/if}</td>
		<td width="64%" class="nowrap">
			{if $waitingOnSignoffs}There are/is {$reviewProcess->getSignoffQueueCount()} more people/person that must sign off.{/if}
		</td>
	</tr>
</table>

{elseif $reviewProcess->getDateSigned() != null}

<h3>{$reviewProcess->getTitle()}: Done ({$reviewProcess->getDateSigned()})</h3>

{else}

<h3>{$reviewProcess->getTitle()}: Not available yet.</h3>

{/if}

</div>
	<div class="separator"></div>
{/foreach}{*review types*}
